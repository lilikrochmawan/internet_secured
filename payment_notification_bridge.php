<?php
// Midtrans Notification Bridge to Laravel Webhook

$logFile = __DIR__ . '/storage/logs/midtrans_bridge.log';

function logMessage($msg) {
    global $logFile;
    $date = date('Y-m-d H:i:s');
    // Ensure storage/logs directory exists
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    file_put_contents($logFile, "[$date] $msg\n", FILE_APPEND);
}

logMessage("Received webhook request from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

// Forward request to the local URL under public/
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$forwardUrl = "$scheme://$host/internet/public/payment/notification";

logMessage("Forwarding request to: $forwardUrl");

$inputData = file_get_contents('php://input');
logMessage("Payload: " . $inputData);

$ch = curl_init($forwardUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $inputData);

// Set headers
$headers = [];
if (function_exists('getallheaders')) {
    foreach (getallheaders() as $name => $value) {
        if (strtolower($name) !== 'host') {
            $headers[] = "$name: $value";
        }
    }
} else {
    // Fallback if getallheaders() is not available
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $hdr_name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
            if (strtolower($hdr_name) !== 'host') {
                $headers[] = "$hdr_name: $value";
            }
        }
    }
}

// Ensure Content-Type is forwarded
if (isset($_SERVER['CONTENT_TYPE'])) {
    $headers[] = "Content-Type: " . $_SERVER['CONTENT_TYPE'];
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    logMessage("cURL Error: " . $error_msg);
    http_response_code(500);
    echo "Bridge Error: " . $error_msg;
    curl_close($ch);
    exit;
}

$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$res_headers = substr($response, 0, $header_size);
$res_body = substr($response, $header_size);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

logMessage("Forwarded response code: $http_code");
logMessage("Forwarded response body: $res_body");

http_response_code($http_code);

// Forward response headers
foreach (explode("\r\n", $res_headers) as $hdr) {
    if (!empty($hdr) && strpos($hdr, ':') !== false) {
        if (stripos($hdr, 'set-cookie') === false && stripos($hdr, 'transfer-encoding') === false) {
            header($hdr);
        }
    }
}

echo $res_body;
curl_close($ch);
