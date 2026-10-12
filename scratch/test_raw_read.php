<?php

$host = '113.192.30.33';
$port = 8114;
$fp = @fsockopen($host, $port, $errno, $errstr, 5);
if ($fp) {
    echo "Connected. Waiting for data...\n";
    stream_set_timeout($fp, 2);
    $data = fread($fp, 1024);
    echo "Received: " . bin2hex($data) . "\n";
    echo "Plain: " . $data . "\n";
    fclose($fp);
} else {
    echo "Failed to connect.\n";
}
