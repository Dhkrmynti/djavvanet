<?php

function check($host, $port) {
    echo "Checking $host:$port... ";
    $fp = @fsockopen($host, $port, $errno, $errstr, 3);
    if ($fp) {
        echo "OPEN\n";
        fclose($fp);
    } else {
        echo "CLOSED ($errstr)\n";
    }
}

$host = '113.192.30.33';
check($host, 8114);
check($host, 8728);
check($host, 8729);
check($host, 8291);
check($host, 80);
check($host, 443);
