<?php

$host = '113.192.30.33';
$port = 8291;
$fp = @fsockopen($host, $port, $errno, $errstr, 3);
if ($fp) {
    echo "WinBox Connected.\n";
    // Send a WinBox-like discovery or hello
    fwrite($fp, "\x01\x00\x00\x00");
    stream_set_timeout($fp, 2);
    $res = fread($fp, 64);
    echo "Response: " . bin2hex($res) . "\n";
    fclose($fp);
} else {
    echo "WinBox Port Closed: $errstr\n";
}
