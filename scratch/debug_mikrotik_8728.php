<?php

require_once __DIR__ . '/../app/Services/MikrotikService.php';
use App\Services\MikrotikService;

class DebugMikrotik extends MikrotikService {
    public function debugLogin() {
        $response = $this->send(['/login', '=name=' . 'dev-op', '=password=' . 'root_admin.,*']);
        echo "Response 1: " . json_encode($response) . "\n";
        
        if (isset($response[0]) && $response[0] === '!re') {
            $challenge = '';
            foreach ($response as $line) {
                if (strpos($line, '=ret=') === 0) $challenge = substr($line, 5);
            }
            $md5 = md5(chr(0) . 'root_admin.,*' . pack('H*', $challenge));
            $response2 = $this->send(['/login', '=name=' . 'dev-op', '=response=00' . $md5]);
            echo "Response 2: " . json_encode($response2) . "\n";
        }
    }
}

$host = '113.192.30.33';
$port = 8728; // Standard API

echo "Debugging Login to $host:$port...\n";
$m = new DebugMikrotik($host, 'dev-op', 'root_admin.,*', $port);
$socket = @fsockopen($host, $port, $errno, $errstr, 5);
if ($socket) {
    echo "Socket connected.\n";
    $reflection = new ReflectionClass('App\Services\MikrotikService');
    $prop = $reflection->getProperty('socket');
    $prop->setAccessible(true);
    $prop->setValue($m, $socket);
    
    $m->debugLogin();
} else {
    echo "Socket failed: $errstr\n";
}
