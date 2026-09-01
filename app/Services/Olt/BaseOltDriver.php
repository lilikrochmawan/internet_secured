<?php

namespace App\Services\Olt;

use App\Models\Olt;

abstract class BaseOltDriver implements OltDriverInterface
{
    protected $olt;
    protected $connection = null;
    protected $shell = null;

    public function __construct(Olt $olt)
    {
        $this->olt = $olt;
    }

    protected function isMock(): bool
    {
        $host = trim($this->olt->ip_address);
        return $host === '127.0.0.1' || $host === 'localhost' || strpos($host, '192.168.99.') === 0;
    }

    public function connect(): bool
    {
        if ($this->isMock()) {
            $this->connection = 'mock';
            return true;
        }

        $host = $this->olt->ip_address;
        $port = $this->olt->port;
        $username = $this->olt->username;
        $password = $this->olt->password;

        if ($this->olt->protokol === 'ssh') {
            try {
                $ssh = new \phpseclib3\Net\SSH2($host, $port, 5);
                if (!$ssh->login($username, $password)) {
                    throw new \Exception("Kredensial SSH OLT salah.");
                }
                $ssh->enablePTY();
                $ssh->startSubsystem('shell');
                // Read initial greeting/prompt
                $ssh->read('/[>#]/', \phpseclib3\Net\SSH2::READ_REGEX);
                
                $this->connection = $ssh;
                return true;
            } catch (\Exception $e) {
                throw new \Exception("Gagal melakukan koneksi SSH ke OLT: " . $e->getMessage());
            }
        } else {
            // Native PHP Telnet socket connection
            try {
                $this->connection = @fsockopen($host, $port, $errno, $errstr, 5);
                if (!$this->connection) {
                    throw new \Exception("Gagal membuka socket Telnet ke {$host}:{$port} (Error {$errno}: {$errstr})");
                }
                
                stream_set_timeout($this->connection, 3);
                
                // Read greeting until login/username prompt
                $output = $this->readUntil('/(login|username|user):/i');
                if (!$output) {
                    fclose($this->connection);
                    $this->connection = null;
                    throw new \Exception("OLT tidak merespon prompt login Telnet dalam batas waktu.");
                }
                
                // Send username
                fwrite($this->connection, $username . PHP_EOL);
                
                // Read until password prompt
                $output = $this->readUntil('/password:/i');
                if (!$output) {
                    fclose($this->connection);
                    $this->connection = null;
                    throw new \Exception("OLT tidak memunculkan prompt password Telnet.");
                }
                
                // Send password
                fwrite($this->connection, $password . PHP_EOL);
                
                // Read response until CLI prompt character > or #
                $output = $this->readUntil('/[>#]/');
                if (strpos(strtolower($output), 'incorrect') !== false || strpos(strtolower($output), 'fail') !== false) {
                    fclose($this->connection);
                    $this->connection = null;
                    throw new \Exception("Kredensial login Telnet OLT salah.");
                }
                
                return true;
            } catch (\Exception $e) {
                if ($this->connection && is_resource($this->connection)) {
                    fclose($this->connection);
                }
                $this->connection = null;
                throw new \Exception($e->getMessage());
            }
        }
    }

    protected function readUntil(string $pattern): string
    {
        if (!$this->connection || !is_resource($this->connection)) return '';
        
        $buffer = '';
        $start = time();
        while (!feof($this->connection)) {
            if ((time() - $start) > 4) { // 4s timeout
                break;
            }
            $char = fgetc($this->connection);
            if ($char === false) {
                usleep(10000);
                continue;
            }
            $buffer .= $char;
            if (preg_match($pattern, $buffer)) {
                break;
            }
        }
        return $buffer;
    }

    protected function executeCommand(string $command): string
    {
        if ($this->connection === 'mock') {
            return $this->getMockResponse($command);
        }

        if ($this->olt->protokol === 'telnet') {
            if (!$this->connection || !is_resource($this->connection)) return '';
            
            // Clear any lingering output
            stream_set_blocking($this->connection, false);
            while (fgets($this->connection)) { /* discard */ }
            stream_set_blocking($this->connection, true);

            fwrite($this->connection, $command . PHP_EOL);
            
            $output = '';
            $start = time();
            while (!feof($this->connection)) {
                if ((time() - $start) > 4) { // 4s command timeout
                    break;
                }
                $line = fgets($this->connection);
                if ($line === false) {
                    usleep(20000);
                    continue;
                }
                $output .= $line;
                if (preg_match('/[>#]\s*$/', trim($line))) {
                    break;
                }
            }
            return $output;
        }

        // SSH (phpseclib3) execution
        if (!$this->connection) return '';
        try {
            $this->connection->write($command . "\r");
            return $this->connection->read('/[>#]/', \phpseclib3\Net\SSH2::READ_REGEX);
        } catch (\Exception $e) {
            return '';
        }
    }

    public function disconnect(): void
    {
        if ($this->olt->protokol === 'telnet') {
            if ($this->connection && is_resource($this->connection)) {
                // Exit CLI session politely
                @fwrite($this->connection, "exit" . PHP_EOL);
                @fclose($this->connection);
            }
        } else {
            if ($this->connection) {
                try {
                    $this->connection->disconnect();
                } catch (\Exception $e) {}
            }
        }
        $this->connection = null;
        $this->shell = null;
    }

    /**
     * Provide realistic mock CLI responses for testing/dev environments.
     */
    abstract protected function getMockResponse(string $command): string;
}
