<?php
/**
 * TR-069 Auto-Configuration Server (ACS) Lightweight Endpoint
 * Handles SOAP messages from CPE (ONT/Router) devices.
 */

// Disable error display to avoid corrupting XML output
error_reporting(0);
ini_set('display_errors', 0);

// Include database connection
require_once dirname(__FILE__) . '/../administrator/include/koneksi.php';

session_start();

// Get the raw POST content
$xmlInput = file_get_contents('php://input');

header("Content-Type: text/xml; charset=utf-8");

// Parse transaction ID or create a random one
$messageId = '12345';

if (empty($xmlInput)) {
    // If the POST payload is empty, the CPE is ready to receive commands from the ACS
    handleEmptyPost($koneksi, $messageId);
    exit;
}

// Load and parse XML
$dom = new DOMDocument();
if (!$dom->loadXML($xmlInput)) {
    http_response_code(400);
    echo "Malformed XML";
    exit;
}

// Log raw CWMP messages for debugging
if (!file_exists(dirname(__FILE__) . '/../scratch')) {
    mkdir(dirname(__FILE__) . '/../scratch', 0777, true);
}
file_put_contents(dirname(__FILE__) . '/../scratch/acs_debug.log', "[" . date('Y-m-d H:i:s') . "] Raw Input:\n" . $xmlInput . "\n\n", FILE_APPEND);

$xpath = new DOMXPath($dom);
$xpath->registerNamespace('soap-env', 'http://schemas.xmlsoap.org/soap/envelope/');
$xpath->registerNamespace('cwmp', 'urn:dslforum-org:cwmp-1-0');

// Retrieve Message ID from SOAP Header
$idNodes = $xpath->query('//cwmp:ID');
if ($idNodes->length > 0) {
    $messageId = $idNodes->item(0)->nodeValue;
}

// Detect the CWMP RPC Method
$bodyNode = $xpath->query('/soap-env:Envelope/soap-env:Body/*');
if ($bodyNode->length === 0) {
    http_response_code(400);
    echo "SOAP Body is empty";
    exit;
}

$methodNode = $bodyNode->item(0);
$methodName = $methodNode->localName; // e.g., Inform, RebootResponse

switch ($methodName) {
    case 'Inform':
        handleInform($koneksi, $xpath, $messageId);
        break;

    case 'RebootResponse':
    case 'SetParameterValuesResponse':
    case 'GetParameterValuesResponse':
        handleResponse($koneksi, $methodName, $messageId, $xpath);
        break;

    case 'Fault':
        handleFaultResponse($koneksi);
        break;

    default:
        // For unknown RPCs or default responses
        sendEmptyResponse();
        break;
}

/**
 * Helper to parse and format optical power from various formats to dBm
 */
function formatOpticalPower($name, $value, $isRx) {
    if (!is_numeric($value)) {
        return $value;
    }
    $valFloat = floatval($value);
    
    // If the value is negative or a small positive number (up to 15 dBm),
    // it is already in dBm and doesn't need scaling/conversion.
    if ($valFloat < 0 || ($valFloat > 0 && $valFloat <= 15)) {
        return number_format($valFloat, 2) . ' dBm';
    }
    
    // Check if it's EPON / ZTE 0.1 uW format (case-insensitive to support China Unicom X_CU_WANEPONInterfaceConfig etc.)
    $nameLower = strtolower($name);
    if (strpos($nameLower, 'eponinterfaceconfig') !== false || strpos($nameLower, 'gponinterfaceconfig') !== false) {
        if ($valFloat <= 0) {
            return $isRx ? '-40.00 dBm (No Signal)' : '-inf dBm';
        }
        $dBm = 10 * log10($valFloat / 10000);
        return number_format($dBm, 2) . ' dBm';
    }
    
    // Otherwise, use generic heuristic parser
    if ($valFloat < 0) {
        if ($valFloat <= -500) {
            if (abs($valFloat) >= 10000) {
                return number_format($valFloat / 1000, 2) . ' dBm';
            } else {
                return number_format($valFloat / 100, 2) . ' dBm';
            }
        } else {
            return number_format($valFloat, 2) . ' dBm';
        }
    } else {
        if ($valFloat >= 500) {
            if (strpos($name, 'OpticalSignalLevel') !== false) {
                return number_format(10 * log10($valFloat / 10000), 2) . ' dBm';
            }
            if (abs($valFloat) >= 10000) {
                return number_format($valFloat / 1000, 2) . ' dBm';
            } else {
                return number_format($valFloat / 100, 2) . ' dBm';
            }
        } else {
            return number_format($valFloat, 2) . ' dBm';
        }
    }
}

/**
 * Handle Inform request from CPE
 */
function handleInform($koneksi, $xpath, $messageId) {
    // Extract Serial Number
    $serialNodes = $xpath->query('//cwmp:Inform/DeviceId/SerialNumber');
    if ($serialNodes->length === 0) {
        sendFault($messageId, '9003', 'Invalid arguments: SerialNumber is missing');
        return;
    }
    $serialNumber = trim($serialNodes->item(0)->nodeValue);
    
    // Save serial number to session to track during empty POSTs
    $_SESSION['serial_number'] = $serialNumber;
    
    // Reset auto-queried flag for the new CWMP session
    unset($_SESSION['auto_queried']);

    // Detect CWMP Model (TR-098 vs TR-181)
    $cwmpModel = 'tr098'; // Default fallback
    $allParamNodes = $xpath->query('//cwmp:Inform/ParameterList/ParameterValueStruct/Name');
    foreach ($allParamNodes as $node) {
        $name = trim($node->nodeValue);
        if (strpos($name, 'Device.') === 0) {
            $cwmpModel = 'tr181';
            break;
        }
    }
    $_SESSION['cwmp_model'] = $cwmpModel;

    // Extract other device parameters
    $oui = trim($xpath->query('//cwmp:Inform/DeviceId/OUI')->item(0)->nodeValue ?? '');
    $productClass = trim($xpath->query('//cwmp:Inform/DeviceId/ProductClass')->item(0)->nodeValue ?? '');
    $manufacturer = trim($xpath->query('//cwmp:Inform/DeviceId/Manufacturer')->item(0)->nodeValue ?? '');

    // Initialize version variables
    $softwareVersion = '';
    $hardwareVersion = '';
    $connectionRequestUrl = '';
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $rxPower = null;
    $txPower = null;
    $pppoeUsername = null;
    $pppoeStatus = null;
    $wifiSsid24 = null;
    $wifiSsid5 = null;
    $wifiChannel24 = null;
    $wifiChannel5 = null;
    $itmsUsername = null;

    // Loop through parameters list
    $paramNodes = $xpath->query('//cwmp:Inform/ParameterList/ParameterValueStruct');
    foreach ($paramNodes as $node) {
        $name = trim($node->getElementsByTagName('Name')->item(0)->nodeValue ?? '');
        $value = trim($node->getElementsByTagName('Value')->item(0)->nodeValue ?? '');

        if (strpos($name, 'SoftwareVersion') !== false) {
            $softwareVersion = $value;
        } elseif (strpos($name, 'HardwareVersion') !== false) {
            $hardwareVersion = $value;
        } elseif (strpos($name, 'ConnectionRequestURL') !== false) {
            $connectionRequestUrl = $value;
        }
        
        // Extract Optical Power parameters (RxPower and TxPower)
        $isTx = (strpos($name, 'TxPower') !== false || strpos($name, 'TxOpticalPower') !== false || strpos($name, 'OpticalInfo.TxPower') !== false || strpos($name, 'OpticalDiagnostics.TxPower') !== false || strpos($name, 'TXPower') !== false);
        $isRx = (strpos($name, 'RxPower') !== false || strpos($name, 'RxOpticalPower') !== false || strpos($name, 'OpticalSignalLevel') !== false || strpos($name, 'OpticalInfo.RxPower') !== false || strpos($name, 'OpticalDiagnostics.RxPower') !== false || strpos($name, 'RXPower') !== false || strpos($name, 'OpticalPower') !== false);
        
        if ($isTx) {
            $txPower = formatOpticalPower($name, $value, false);
        } elseif ($isRx) {
            $rxPower = formatOpticalPower($name, $value, true);
        }
        
        // Extract PPPoE Username
        if ((strpos($name, 'WANPPPConnection') !== false || strpos($name, 'PPP.Interface') !== false) && strpos($name, 'Username') !== false) {
            if (empty($pppoeUsername) || strtolower($pppoeUsername) === 'default') {
                if (!empty($value)) {
                    $pppoeUsername = $value;
                }
            }
        } elseif (strpos($name, 'X_CT-COM_UserInfo.UserName') !== false || strpos($name, 'X_CT-COM_UserInfo.UserId') !== false) {
            $itmsUsername = $value;
        }
        
        // Extract PPPoE Connection Status
        if ((strpos($name, 'WANPPPConnection') !== false || strpos($name, 'PPP.Interface') !== false) && strpos($name, 'ConnectionStatus') !== false) {
            if (empty($pppoeStatus) || $pppoeStatus !== 'Connected') {
                if (!empty($value)) {
                    $pppoeStatus = $value;
                }
            }
        }
        
        // Extract WiFi SSIDs (2.4GHz and 5GHz)
        if (preg_match('/(WLANConfiguration|WiFi\.SSID)\.(\d+)\.SSID$/i', $name, $matches)) {
            $index = intval($matches[2]);
            if ($index === 1) {
                $wifiSsid24 = $value;
            } else {
                $mfgLower = strtolower($manufacturer);
                $isCData = ($mfgLower === 'cdt' || $mfgLower === 'cdata' || $mfgLower === 'c-data');
                if ($isCData) {
                    if ($index === 6) {
                        $wifiSsid5 = $value;
                        $_SESSION['wifi_ssid_5_index'] = $index;
                    } elseif ($index === 5 && empty($wifiSsid5)) {
                        $wifiSsid5 = $value;
                        $_SESSION['wifi_ssid_5_index'] = $index;
                    } elseif ($index === 2 && empty($wifiSsid5)) {
                        $wifiSsid5 = $value;
                        $_SESSION['wifi_ssid_5_index'] = $index;
                    }
                } else {
                    if ($index === 6) {
                        $wifiSsid5 = $value;
                        $_SESSION['wifi_ssid_5_index'] = $index;
                    } elseif (in_array($index, [5, 9]) && empty($wifiSsid5)) {
                        $wifiSsid5 = $value;
                        $_SESSION['wifi_ssid_5_index'] = $index;
                    }
                }
            }
        }

        // Extract WiFi Channels (2.4GHz and 5GHz)
        if (preg_match('/(WLANConfiguration|WiFi\.Radio|WiFi\.SSID)\.(\d+)\.Channel$/i', $name, $matches)) {
            $index = intval($matches[2]);
            if ($index === 1) {
                $wifiChannel24 = $value;
            } else {
                $mfgLower = strtolower($manufacturer);
                $isCData = ($mfgLower === 'cdt' || $mfgLower === 'cdata' || $mfgLower === 'c-data');
                if ($isCData) {
                    if ($index === 6) {
                        $wifiChannel5 = $value;
                    } elseif ($index === 5 && empty($wifiChannel5)) {
                        $wifiChannel5 = $value;
                    } elseif ($index === 2 && empty($wifiChannel5)) {
                        $wifiChannel5 = $value;
                    }
                } else {
                    if (in_array($index, [5, 6, 9])) {
                        $wifiChannel5 = $value;
                    }
                }
            }
        }
    }

    if (empty($pppoeUsername) && !empty($itmsUsername)) {
        $pppoeUsername = $itmsUsername;
        if (empty($pppoeStatus)) {
            $pppoeStatus = 'Connected';
        }
    }

    // Insert or Update the CPE details
    $escapedSerial = $koneksi->real_escape_string($serialNumber);
    $escapedOUI = $koneksi->real_escape_string($oui);
    $escapedClass = $koneksi->real_escape_string($productClass);
    $escapedMfg = $koneksi->real_escape_string($manufacturer);
    $escapedSw = $koneksi->real_escape_string($softwareVersion);
    $escapedHw = $koneksi->real_escape_string($hardwareVersion);
    $escapedUrl = $koneksi->real_escape_string($connectionRequestUrl);
    $escapedIp = $koneksi->real_escape_string($ipAddress);

    $check = $koneksi->query("SELECT id_cpe FROM tb_cpe WHERE serial_number = '$escapedSerial'");
    if ($check && $check->num_rows > 0) {
        $updateFields = [
            "oui = '$escapedOUI'", 
            "product_class = '$escapedClass'", 
            "manufacturer = '$escapedMfg'", 
            "ip_address = '$escapedIp'", 
            "connection_request_url = '$escapedUrl'", 
            "software_version = '$escapedSw'", 
            "hardware_version = '$escapedHw'", 
            "cwmp_model = '$cwmpModel'", 
            "last_inform = NOW()"
        ];
        
        if ($rxPower !== null) $updateFields[] = "rx_power = '" . $koneksi->real_escape_string($rxPower) . "'";
        if ($txPower !== null) $updateFields[] = "tx_power = '" . $koneksi->real_escape_string($txPower) . "'";
        if ($pppoeUsername !== null) $updateFields[] = "pppoe_username = '" . $koneksi->real_escape_string($pppoeUsername) . "'";
        if ($pppoeStatus !== null) $updateFields[] = "pppoe_status = '" . $koneksi->real_escape_string($pppoeStatus) . "'";
        if ($wifiSsid24 !== null) $updateFields[] = "wifi_ssid_24 = '" . $koneksi->real_escape_string($wifiSsid24) . "'";
        if ($wifiSsid5 !== null) {
            $updateFields[] = "wifi_ssid_5 = '" . $koneksi->real_escape_string($wifiSsid5) . "'";
            if (isset($_SESSION['wifi_ssid_5_index'])) {
                $updateFields[] = "wifi_ssid_5_index = " . (int)$_SESSION['wifi_ssid_5_index'];
            }
        }
        if ($wifiChannel24 !== null) $updateFields[] = "wifi_channel_24 = '" . $koneksi->real_escape_string($wifiChannel24) . "'";
        if ($wifiChannel5 !== null) $updateFields[] = "wifi_channel_5 = '" . $koneksi->real_escape_string($wifiChannel5) . "'";

        $koneksi->query("UPDATE tb_cpe SET " . implode(", ", $updateFields) . " WHERE serial_number = '$escapedSerial'");
    } else {
        $insertCols = ['serial_number', 'oui', 'product_class', 'manufacturer', 'ip_address', 'connection_request_url', 'software_version', 'hardware_version', 'cwmp_model', 'last_inform'];
        $insertVals = ["'$escapedSerial'", "'$escapedOUI'", "'$escapedClass'", "'$escapedMfg'", "'$escapedIp'", "'$escapedUrl'", "'$escapedSw'", "'$escapedHw'", "'$cwmpModel'", "NOW()"];
        
        if ($rxPower !== null) {
            $insertCols[] = 'rx_power';
            $insertVals[] = "'" . $koneksi->real_escape_string($rxPower) . "'";
        }
        if ($txPower !== null) {
            $insertCols[] = 'tx_power';
            $insertVals[] = "'" . $koneksi->real_escape_string($txPower) . "'";
        }
        if ($pppoeUsername !== null) {
            $insertCols[] = 'pppoe_username';
            $insertVals[] = "'" . $koneksi->real_escape_string($pppoeUsername) . "'";
        }
        if ($pppoeStatus !== null) {
            $insertCols[] = 'pppoe_status';
            $insertVals[] = "'" . $koneksi->real_escape_string($pppoeStatus) . "'";
        }
        if ($wifiSsid24 !== null) {
            $insertCols[] = 'wifi_ssid_24';
            $insertVals[] = "'" . $koneksi->real_escape_string($wifiSsid24) . "'";
        }
        if ($wifiSsid5 !== null) {
            $insertCols[] = 'wifi_ssid_5';
            $insertVals[] = "'" . $koneksi->real_escape_string($wifiSsid5) . "'";
            if (isset($_SESSION['wifi_ssid_5_index'])) {
                $insertCols[] = 'wifi_ssid_5_index';
                $insertVals[] = (int)$_SESSION['wifi_ssid_5_index'];
            }
        }
        if ($wifiChannel24 !== null) {
            $insertCols[] = 'wifi_channel_24';
            $insertVals[] = "'" . $koneksi->real_escape_string($wifiChannel24) . "'";
        }
        if ($wifiChannel5 !== null) {
            $insertCols[] = 'wifi_channel_5';
            $insertVals[] = "'" . $koneksi->real_escape_string($wifiChannel5) . "'";
        }
        
        $koneksi->query("INSERT INTO tb_cpe (" . implode(", ", $insertCols) . ") VALUES (" . implode(", ", $insertVals) . ")");
    }

    // Clean up older completed commands in queue to keep database under 100 per device
    $koneksi->query("DELETE FROM tb_acs_queue 
        WHERE serial_number = '$escapedSerial' 
          AND status IN ('success', 'failed')
          AND id_command NOT IN (
              SELECT id_command FROM (
                  SELECT id_command FROM tb_acs_queue 
                  WHERE serial_number = '$escapedSerial' 
                  ORDER BY id_command DESC 
                  LIMIT 100
              ) tmp
          )");

    // Return InformResponse SOAP envelope
    echo '<?xml version="1.0" encoding="UTF-8"?>
<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cwmp="urn:dslforum-org:cwmp-1-0">
    <soap-env:Header>
        <cwmp:ID soap-env:mustUnderstand="1">' . htmlspecialchars($messageId) . '</cwmp:ID>
    </soap-env:Header>
    <soap-env:Body>
        <cwmp:InformResponse>
            <MaxEnvelopes>1</MaxEnvelopes>
        </cwmp:InformResponse>
    </soap-env:Body>
</soap-env:Envelope>';
}

/**
 * Handle CPE indicating it's ready for commands (Empty SOAP Post)
 */
function handleEmptyPost($koneksi, $messageId) {
    if (empty($_SESSION['serial_number'])) {
        sendEmptyResponse();
        return;
    }

    $serialNumber = $_SESSION['serial_number'];
    $escapedSerial = $koneksi->real_escape_string($serialNumber);

    // Look for pending commands in queue
    $query = $koneksi->query("SELECT * FROM tb_acs_queue 
        WHERE serial_number = '$escapedSerial' AND status = 'pending' 
        ORDER BY id_command ASC LIMIT 1");

    // If no commands in queue, check if we need to auto-query parameters (once per session)
    if ((!$query || $query->num_rows === 0) && empty($_SESSION['auto_queried'])) {
        $_SESSION['auto_queried'] = true;
        
        $cwmpModel = $_SESSION['cwmp_model'] ?? 'tr098';
        
        // Fetch manufacturer
        $mfg = '';
        $mfgQuery = $koneksi->query("SELECT manufacturer FROM tb_cpe WHERE serial_number = '$escapedSerial'");
        if ($mfgQuery && $mfgQuery->num_rows > 0) {
            $mfgRow = $mfgQuery->fetch_assoc();
            $mfg = strtolower(trim($mfgRow['manufacturer']));
        }
        $isCData = ($mfg === 'cdt' || $mfg === 'cdata' || $mfg === 'c-data');

        $pathsToQuery = [];
        
        if ($cwmpModel === 'tr181') {
            $pathsToQuery = [
                'Device.PPP.Interface.1.Username',
                'Device.PPP.Interface.1.ConnectionStatus',
                'Device.PPP.Interface.2.Username',
                'Device.PPP.Interface.2.ConnectionStatus',
                'Device.PPP.Interface.3.Username',
                'Device.PPP.Interface.3.ConnectionStatus',
                'Device.WiFi.SSID.1.SSID',
                'Device.X_CT-COM_UserInfo.UserName',
                'Device.X_CT-COM_UserInfo.UserId'
            ];
            if ($isCData) {
                $pathsToQuery[] = 'Device.WiFi.SSID.2.SSID';
                $pathsToQuery[] = 'Device.WiFi.SSID.5.SSID';
                $pathsToQuery[] = 'Device.WiFi.SSID.6.SSID';
            } else {
                $pathsToQuery[] = 'Device.WiFi.SSID.5.SSID';
                $pathsToQuery[] = 'Device.WiFi.SSID.6.SSID';
            }
            $pathsToQuery[] = 'Device.WiFi.Radio.1.Channel';
            $pathsToQuery[] = 'Device.WiFi.Radio.2.Channel';
            $pathsToQuery = array_merge($pathsToQuery, [
                'Device.Optical.Interface.1.Stats.RxOpticalPower',
                'Device.Optical.Interface.1.Stats.TxOpticalPower',
                'Device.GPON.ONU.OpticalDiagnostics.RxPower',
                'Device.GPON.ONU.OpticalDiagnostics.TxPower',
                'Device.Hosts.'
            ]);
        } else {
            $pathsToQuery = [
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username',
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ConnectionStatus',
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Username',
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.ConnectionStatus',
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.Username',
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.ConnectionStatus',
                'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'InternetGatewayDevice.X_CT-COM_UserInfo.UserName',
                'InternetGatewayDevice.X_CT-COM_UserInfo.UserId'
            ];
            if ($isCData) {
                $pathsToQuery[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.2.SSID';
                $pathsToQuery[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID';
                $pathsToQuery[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.6.SSID';
            } else {
                $pathsToQuery[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID';
                $pathsToQuery[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.6.SSID';
            }
            $pathsToQuery[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Channel';
            if ($isCData) {
                $pathsToQuery[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.2.Channel';
                $pathsToQuery[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.Channel';
                $pathsToQuery[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.6.Channel';
            } else {
                $pathsToQuery[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.Channel';
                $pathsToQuery[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.6.Channel';
            }
            $pathsToQuery = array_merge($pathsToQuery, [
                'InternetGatewayDevice.WANDevice.1.X_CT-COM_GponInterfaceConfig.RXPower',
                'InternetGatewayDevice.WANDevice.1.X_CT-COM_GponInterfaceConfig.TxPower',
                'InternetGatewayDevice.WANDevice.1.X_CT-COM_GponInterfaceConfig.RxPower',
                'InternetGatewayDevice.WANDevice.1.X_CT-COM_GponInterfaceConfig.TXPower',
                'InternetGatewayDevice.WANDevice.1.X_CT-COM_EponInterfaceConfig.RXPower',
                'InternetGatewayDevice.WANDevice.1.X_CT-COM_EponInterfaceConfig.TXPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE_GponInterfaceConfig.RXPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE_GponInterfaceConfig.TXPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE_EponInterfaceConfig.RXPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE_EponInterfaceConfig.TXPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_GponInterfaceConfig.RXPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_GponInterfaceConfig.TXPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_EponInterfaceConfig.RXPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_EponInterfaceConfig.TXPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_OpticalInfo.RxPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_OpticalInfo.TxPower',
                'InternetGatewayDevice.WANDevice.1.X_FH_PON.Optical.RxPower',
                'InternetGatewayDevice.WANDevice.1.X_FH_PON.Optical.TxPower',
                'InternetGatewayDevice.WANDevice.1.X_FH_PON.OpticalDiagnostics.RxPower',
                'InternetGatewayDevice.WANDevice.1.X_FH_PON.OpticalDiagnostics.TxPower',
                'InternetGatewayDevice.WANDevice.1.X_FI_PON.Optical.RxPower',
                'InternetGatewayDevice.WANDevice.1.X_FI_PON.Optical.TxPower',
                'InternetGatewayDevice.WANDevice.1.X_FH_OpticalInfo.RxPower',
                'InternetGatewayDevice.WANDevice.1.X_FH_OpticalInfo.TxPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE_Dslh.OpticalDiagnostics.RxPower',
                'InternetGatewayDevice.WANDevice.1.X_ZTE_Dslh.OpticalDiagnostics.TxPower',
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANONUConnection.OpticalSignalLevel',
                'InternetGatewayDevice.X_GPON.Diagnostics.OpticalInformation.RxOpticalPower',
                'InternetGatewayDevice.X_HW_OpticalDiagnostics.RxPower',
                'InternetGatewayDevice.X_ZTE_Dslh.OpticalDiagnostics.RxPower',
                'InternetGatewayDevice.WANDevice.1.X_CU_WANEPONInterfaceConfig.OpticalTransceiver.RXPower',
                'InternetGatewayDevice.WANDevice.1.X_CU_WANEPONInterfaceConfig.OpticalTransceiver.TXPower',
                'InternetGatewayDevice.WANDevice.1.X_CU_WANGPONInterfaceConfig.OpticalTransceiver.RXPower',
                'InternetGatewayDevice.WANDevice.1.X_CU_WANGPONInterfaceConfig.OpticalTransceiver.TXPower',
                'InternetGatewayDevice.Hosts.',
                'InternetGatewayDevice.LANDevice.1.Hosts.'
            ]);
        }
        
        // Insert them as individual commands so if one fails, others don't get canceled
        foreach ($pathsToQuery as $path) {
            $koneksi->query("INSERT INTO tb_acs_queue (serial_number, command_type, command_data, status, created_at) 
                VALUES ('$escapedSerial', 'GetParameterValues', '" . $koneksi->real_escape_string(json_encode([$path])) . "', 'pending', NOW())");
        }
        
        // Re-query the queue
        $query = $koneksi->query("SELECT * FROM tb_acs_queue 
            WHERE serial_number = '$escapedSerial' AND status = 'pending' 
            ORDER BY id_command ASC LIMIT 1");
    }

    if ($query && $query->num_rows > 0) {
        $command = $query->fetch_assoc();
        $idCommand = $command['id_command'];
        $cmdType = $command['command_type'];
        $cmdData = json_decode($command['command_data'] ?? '{}', true);

        // Store the active command in session to link with response
        $_SESSION['active_command_id'] = $idCommand;

        // Update command status to 'sent'
        $koneksi->query("UPDATE tb_acs_queue SET status = 'sent', updated_at = NOW() WHERE id_command = $idCommand");

        // Format SOAP commands based on type
        if ($cmdType === 'Reboot') {
            echo '<?xml version="1.0" encoding="UTF-8"?>
<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cwmp="urn:dslforum-org:cwmp-1-0">
    <soap-env:Header>
        <cwmp:ID soap-env:mustUnderstand="1">cmd_' . $idCommand . '</cwmp:ID>
    </soap-env:Header>
    <soap-env:Body>
        <cwmp:Reboot>
            <CommandKey>reboot_cmd</CommandKey>
        </cwmp:Reboot>
    </soap-env:Body>
</soap-env:Envelope>';
        } elseif ($cmdType === 'SetParameterValues') {
            $parameterListXml = '';
            foreach ($cmdData as $paramName => $paramValue) {
                $parameterListXml .= '
            <ParameterValueStruct>
                <Name>' . htmlspecialchars($paramName) . '</Name>
                <Value xsi:type="xsd:string" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">' . htmlspecialchars($paramValue) . '</Value>
            </ParameterValueStruct>';
            }

            echo '<?xml version="1.0" encoding="UTF-8"?>
<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cwmp="urn:dslforum-org:cwmp-1-0">
    <soap-env:Header>
        <cwmp:ID soap-env:mustUnderstand="1">cmd_' . $idCommand . '</cwmp:ID>
    </soap-env:Header>
    <soap-env:Body>
        <cwmp:SetParameterValues>
            <ParameterList>' . $parameterListXml . '
            </ParameterList>
            <ParameterKey>set_param_key</ParameterKey>
        </cwmp:SetParameterValues>
    </soap-env:Body>
</soap-env:Envelope>';
        } elseif ($cmdType === 'GetParameterValues') {
            $parameterNamesXml = '';
            foreach ($cmdData as $paramPath) {
                $parameterNamesXml .= '<string>' . htmlspecialchars($paramPath) . '</string>';
            }

            echo '<?xml version="1.0" encoding="UTF-8"?>
<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cwmp="urn:dslforum-org:cwmp-1-0">
    <soap-env:Header>
        <cwmp:ID soap-env:mustUnderstand="1">cmd_' . $idCommand . '</cwmp:ID>
    </soap-env:Header>
    <soap-env:Body>
        <cwmp:GetParameterValues>
            <ParameterNames>
                ' . $parameterNamesXml . '
            </ParameterNames>
        </cwmp:GetParameterValues>
    </soap-env:Body>
</soap-env:Envelope>';
        } elseif ($cmdType === 'GetParameterNames') {
            $path = $cmdData['parameter_path'] ?? 'InternetGatewayDevice.';
            $nextLevel = isset($cmdData['next_level']) ? ($cmdData['next_level'] ? 'true' : 'false') : 'false';
            echo '<?xml version="1.0" encoding="UTF-8"?>
<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cwmp="urn:dslforum-org:cwmp-1-0">
    <soap-env:Header>
        <cwmp:ID soap-env:mustUnderstand="1">cmd_' . $idCommand . '</cwmp:ID>
    </soap-env:Header>
    <soap-env:Body>
        <cwmp:GetParameterNames>
            <ParameterPath>' . htmlspecialchars($path) . '</ParameterPath>
            <NextLevel>' . $nextLevel . '</NextLevel>
        </cwmp:GetParameterNames>
    </soap-env:Body>
</soap-env:Envelope>';
        } else {
            // Unhandled command type
            sendEmptyResponse();
        }
    } else {
        // No pending commands, finish session
        sendEmptyResponse();
    }
}

/**
 * Handle responses to commands sent by the ACS
 */
function handleResponse($koneksi, $methodName, $messageId, $xpath) {
    if (!empty($_SESSION['active_command_id'])) {
        $idCommand = (int) $_SESSION['active_command_id'];
        unset($_SESSION['active_command_id']);

        // Update command status to 'success'
        $koneksi->query("UPDATE tb_acs_queue SET status = 'success', updated_at = NOW() WHERE id_command = $idCommand");
    }

    // Parse parameters returned by GetParameterValuesResponse
    if ($methodName === 'GetParameterValuesResponse' && !empty($_SESSION['serial_number'])) {
        $serialNumber = $_SESSION['serial_number'];
        $escapedSerial = $koneksi->real_escape_string($serialNumber);

        // Fetch manufacturer
        $mfg = '';
        $mfgQuery = $koneksi->query("SELECT manufacturer FROM tb_cpe WHERE serial_number = '$escapedSerial'");
        if ($mfgQuery && $mfgQuery->num_rows > 0) {
            $mfgRow = $mfgQuery->fetch_assoc();
            $mfg = strtolower(trim($mfgRow['manufacturer']));
        }
        $isCData = ($mfg === 'cdt' || $mfg === 'cdata' || $mfg === 'c-data');
        
        $rxPower = null;
        $txPower = null;
        $pppoeUsername = null;
        $pppoeStatus = null;
        $wifiSsid24 = null;
        $wifiSsid5 = null;
        $wifiChannel24 = null;
        $wifiChannel5 = null;
        $itmsUsername = null;
        
        $pppoeConnections = [];
        $hostsList = [];
        
        $paramNodes = $xpath->query('//cwmp:GetParameterValuesResponse/ParameterList/ParameterValueStruct');
        if ($paramNodes->length === 0) {
            $paramNodes = $xpath->query('//ParameterValueStruct');
        }
        
        foreach ($paramNodes as $node) {
            $name = trim($node->getElementsByTagName('Name')->item(0)->nodeValue ?? '');
            $value = trim($node->getElementsByTagName('Value')->item(0)->nodeValue ?? '');
            
            // Log parsed parameter for debugging
            file_put_contents(dirname(__FILE__) . '/../scratch/acs_debug.log', "Parsed Parameter: " . $name . " = " . $value . "\n", FILE_APPEND);
            
            // Extract ITMS/RMS Username
            if (strpos($name, 'X_CT-COM_UserInfo.UserName') !== false || strpos($name, 'X_CT-COM_UserInfo.UserId') !== false) {
                $itmsUsername = $value;
            }
            
            // Extract Optical Power parameters (RxPower and TxPower)
            $isTx = (strpos($name, 'TxPower') !== false || strpos($name, 'TxOpticalPower') !== false || strpos($name, 'OpticalInfo.TxPower') !== false || strpos($name, 'OpticalDiagnostics.TxPower') !== false || strpos($name, 'TXPower') !== false);
            $isRx = (strpos($name, 'RxPower') !== false || strpos($name, 'RxOpticalPower') !== false || strpos($name, 'OpticalSignalLevel') !== false || strpos($name, 'OpticalInfo.RxPower') !== false || strpos($name, 'OpticalDiagnostics.RxPower') !== false || strpos($name, 'RXPower') !== false || strpos($name, 'OpticalPower') !== false);
            
            if ($isTx) {
                $txPower = formatOpticalPower($name, $value, false);
            } elseif ($isRx) {
                $rxPower = formatOpticalPower($name, $value, true);
            }
            
            // Extract PPPoE details per connection index
            if (preg_match('/(WANConnectionDevice\.\d+\.WANPPPConnection\.\d+|PPP\.Interface\.\d+)/i', $name, $matches)) {
                $connKey = $matches[1];
                if (!isset($pppoeConnections[$connKey])) {
                    $pppoeConnections[$connKey] = ['username' => null, 'status' => null];
                }
                if (strpos($name, 'Username') !== false) {
                    $pppoeConnections[$connKey]['username'] = $value;
                } elseif (strpos($name, 'ConnectionStatus') !== false) {
                    $pppoeConnections[$connKey]['status'] = $value;
                }
            }
            
            // Extract WiFi SSIDs
            if (preg_match('/(WLANConfiguration|WiFi\.SSID)\.(\d+)\.SSID$/i', $name, $matches)) {
                $index = intval($matches[2]);
                if ($index === 1) {
                    $wifiSsid24 = $value;
                } else {
                    if ($isCData) {
                        // CData dual-band uses index 6 for 5GHz, older models may use index 5 or 2.
                        // We prioritize index 6, then index 5, then index 2.
                        if ($index === 6) {
                            $wifiSsid5 = $value;
                            $_SESSION['wifi_ssid_5_index'] = $index;
                        } elseif ($index === 5 && (empty($wifiSsid5) || strpos($wifiSsid5, 'HGW') === 0)) {
                            $wifiSsid5 = $value;
                            $_SESSION['wifi_ssid_5_index'] = $index;
                        } elseif ($index === 2 && (empty($wifiSsid5) || strpos($wifiSsid5, 'HGW') === 0)) {
                            $wifiSsid5 = $value;
                            $_SESSION['wifi_ssid_5_index'] = $index;
                        }
                    } else {
                        // For non-CData, prefer index 6, then 5, then 9
                        if ($index === 6) {
                            $wifiSsid5 = $value;
                            $_SESSION['wifi_ssid_5_index'] = $index;
                        } elseif ($index === 5 && (empty($wifiSsid5) || strpos($wifiSsid5, 'HGW') === 0)) {
                            $wifiSsid5 = $value;
                            $_SESSION['wifi_ssid_5_index'] = $index;
                        } elseif ($index === 9 && (empty($wifiSsid5) || strpos($wifiSsid5, 'HGW') === 0)) {
                            $wifiSsid5 = $value;
                            $_SESSION['wifi_ssid_5_index'] = $index;
                        }
                    }
                }
            }

            // Extract WiFi Channels
            if (preg_match('/(WLANConfiguration|WiFi\.Radio|WiFi\.SSID)\.(\d+)\.Channel$/i', $name, $matches)) {
                $index = intval($matches[2]);
                if ($index === 1) {
                    $wifiChannel24 = $value;
                } else {
                    if ($isCData) {
                        $active5gIndex = isset($_SESSION['wifi_ssid_5_index']) ? (int)$_SESSION['wifi_ssid_5_index'] : 6;
                        if ($index === $active5gIndex || ($index === 6 && empty($wifiChannel5))) {
                            $wifiChannel5 = $value;
                        } elseif ($index === 5 && empty($wifiChannel5)) {
                            $wifiChannel5 = $value;
                        } elseif ($index === 2 && empty($wifiChannel5)) {
                            $wifiChannel5 = $value;
                        }
                    } else {
                        $active5gIndex = isset($_SESSION['wifi_ssid_5_index']) ? (int)$_SESSION['wifi_ssid_5_index'] : 5;
                        if ($index === $active5gIndex || ($index === 5 && empty($wifiChannel5))) {
                            $wifiChannel5 = $value;
                        } elseif ($index === 6 && empty($wifiChannel5)) {
                            $wifiChannel5 = $value;
                        } elseif ($index === 9 && empty($wifiChannel5)) {
                            $wifiChannel5 = $value;
                        }
                    }
                }
            }

            // Extract Hosts details
            if (preg_match('/(Device|InternetGatewayDevice)(?:\.LANDevice\.\d+)?\.Hosts\.Host\.(\d+)\.(HostName|IPAddress|MACAddress|PhysAddress|Active|InterfaceType)/i', $name, $matches)) {
                $hostIdx = intval($matches[2]);
                $field = strtolower($matches[3]);
                if (!isset($hostsList[$hostIdx])) {
                    $hostsList[$hostIdx] = [];
                }
                if ($field === 'physaddress') {
                    $hostsList[$hostIdx]['macaddress'] = $value;
                } else {
                    $hostsList[$hostIdx][$field] = $value;
                }
            }
        }
        
        // Find the active PPPoE connection with a username (prioritizing non-default usernames)
        $fallbackUsername = null;
        $fallbackStatus = null;
        foreach ($pppoeConnections as $conn) {
            if (!empty($conn['username'])) {
                if (strtolower($conn['username']) !== 'default') {
                    $pppoeUsername = $conn['username'];
                    $pppoeStatus = $conn['status'];
                    break;
                } else {
                    $fallbackUsername = $conn['username'];
                    $fallbackStatus = $conn['status'];
                }
            }
        }
        if (empty($pppoeUsername) && !empty($fallbackUsername)) {
            $pppoeUsername = $fallbackUsername;
            $pppoeStatus = $fallbackStatus;
        }
        
        // Fallback to ITMS/RMS Username if PPPoE Username is empty
        if (empty($pppoeUsername) && !empty($itmsUsername)) {
            $pppoeUsername = $itmsUsername;
            if (empty($pppoeStatus)) {
                $pppoeStatus = 'Connected';
            }
        }

        // Fallback if no username was retrieved, but a connection is online
        if ($pppoeUsername === null) {
            foreach ($pppoeConnections as $conn) {
                if ($conn['status'] === 'Connected') {
                    $pppoeStatus = 'Connected';
                    break;
                }
            }
        }
        
        $updateFields = [];
        if ($rxPower !== null) $updateFields[] = "rx_power = '" . $koneksi->real_escape_string($rxPower) . "'";
        if ($txPower !== null) $updateFields[] = "tx_power = '" . $koneksi->real_escape_string($txPower) . "'";
        if ($pppoeUsername !== null) $updateFields[] = "pppoe_username = '" . $koneksi->real_escape_string($pppoeUsername) . "'";
        if ($pppoeStatus !== null) $updateFields[] = "pppoe_status = '" . $koneksi->real_escape_string($pppoeStatus) . "'";
        if ($wifiSsid24 !== null) $updateFields[] = "wifi_ssid_24 = '" . $koneksi->real_escape_string($wifiSsid24) . "'";
        if ($wifiSsid5 !== null) {
            $updateFields[] = "wifi_ssid_5 = '" . $koneksi->real_escape_string($wifiSsid5) . "'";
            if (isset($_SESSION['wifi_ssid_5_index'])) {
                $updateFields[] = "wifi_ssid_5_index = " . (int)$_SESSION['wifi_ssid_5_index'];
            }
        }
        if ($wifiChannel24 !== null) $updateFields[] = "wifi_channel_24 = '" . $koneksi->real_escape_string($wifiChannel24) . "'";
        if ($wifiChannel5 !== null) $updateFields[] = "wifi_channel_5 = '" . $koneksi->real_escape_string($wifiChannel5) . "'";
        
        if (!empty($hostsList)) {
            $activeHosts = [];
            foreach ($hostsList as $idx => $host) {
                if (!empty($host['macaddress']) || !empty($host['ipaddress'])) {
                    $activeHosts[] = [
                        'hostname' => $host['hostname'] ?? '-',
                        'ip_address' => $host['ipaddress'] ?? '-',
                        'mac_address' => $host['macaddress'] ?? '-',
                        'interface_type' => $host['interfacetype'] ?? '-',
                    ];
                }
            }
            $updateFields[] = "connected_devices = '" . $koneksi->real_escape_string(json_encode($activeHosts)) . "'";
        }

        if (!empty($updateFields)) {
            $koneksi->query("UPDATE tb_cpe SET " . implode(", ", $updateFields) . " WHERE serial_number = '$escapedSerial'");
        }
    }

    // Parse parameters returned by GetParameterNamesResponse
    if ($methodName === 'GetParameterNamesResponse') {
        $parameterNodes = $xpath->query('//cwmp:GetParameterNamesResponse/ParameterList/ParameterInfoStruct');
        if ($parameterNodes->length === 0) {
            $parameterNodes = $xpath->query('//ParameterInfoStruct');
        }
        
        $logContent = "[" . date('Y-m-d H:i:s') . "] --- GetParameterNamesResponse ---\n";
        foreach ($parameterNodes as $node) {
            $name = trim($node->getElementsByTagName('Name')->item(0)->nodeValue ?? '');
            $writable = trim($node->getElementsByTagName('Writable')->item(0)->nodeValue ?? '');
            $logContent .= $name . " (Writable: " . $writable . ")\n";
        }
        file_put_contents(dirname(__FILE__) . '/../scratch/parameter_names.log', $logContent, FILE_APPEND);
    }

    // After receiving a response, check if there are other commands in the queue
    handleEmptyPost($koneksi, $messageId);
}

/**
 * Handle Fault response when a command fails
 */
function handleFaultResponse($koneksi) {
    if (!empty($_SESSION['active_command_id'])) {
        $idCommand = (int) $_SESSION['active_command_id'];
        unset($_SESSION['active_command_id']);
        
        // Fetch failed command to inspect command_data
        $resCmd = $koneksi->query("SELECT * FROM tb_acs_queue WHERE id_command = $idCommand");
        if ($resCmd && $resCmd->num_rows > 0) {
            $cmd = $resCmd->fetch_assoc();
            $cmdData = json_decode($cmd['command_data'] ?? '[]', true);
            $serialNumber = $cmd['serial_number'];
            $escapedSerial = $koneksi->real_escape_string($serialNumber);
            
            // If GetParameterValues failed for 5GHz SSIDs, clear wifi_ssid_5 in tb_cpe
            if ($cmd['command_type'] === 'GetParameterValues' && is_array($cmdData)) {
                foreach ($cmdData as $path) {
                    if (strpos($path, 'WLANConfiguration.5.SSID') !== false || 
                        strpos($path, 'WLANConfiguration.2.SSID') !== false || 
                        strpos($path, 'WLANConfiguration.9.SSID') !== false || 
                        strpos($path, 'WiFi.SSID.5.SSID') !== false || 
                        strpos($path, 'WiFi.SSID.2.SSID') !== false || 
                        strpos($path, 'WiFi.SSID.9.SSID') !== false) {
                        
                        $koneksi->query("UPDATE tb_cpe SET wifi_ssid_5 = NULL WHERE serial_number = '$escapedSerial'");
                        break;
                    }
                }
            }
        }
        
        // Update command status to 'failed'
        $koneksi->query("UPDATE tb_acs_queue SET status = 'failed', updated_at = NOW() WHERE id_command = $idCommand");
    }
    
    // Check if there are other commands in the queue
    handleEmptyPost($koneksi, '12345');
}

/**
 * Send an empty response to close CWMP session (HTTP 204 No Content)
 */
function sendEmptyResponse() {
    header("HTTP/1.1 204 No Content");
    header("Content-Length: 0");
    exit;
}

/**
 * Send a SOAP fault error code
 */
function sendFault($messageId, $faultCode, $faultString) {
    echo '<?xml version="1.0" encoding="UTF-8"?>
<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
    <soap-env:Header>
        <cwmp:ID soap-env:mustUnderstand="1">' . htmlspecialchars($messageId) . '</cwmp:ID>
    </soap-env:Header>
    <soap-env:Body>
        <soap-env:Fault>
            <faultcode>Client</faultcode>
            <faultstring>CWMP fault</faultstring>
            <detail>
                <cwmp:Fault>
                    <FaultCode>' . htmlspecialchars($faultCode) . '</FaultCode>
                    <FaultString>' . htmlspecialchars($faultString) . '</FaultString>
                </cwmp:Fault>
            </detail>
        </soap-env:Fault>
    </soap-env:Body>
</soap-env:Envelope>';
    exit;
}
?>
