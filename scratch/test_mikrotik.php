<?php

$host = '113.192.30.33';
$port = 8114;
$timeout = 5;

echo "Mencoba menghubungkan ke $host:$port...\n";

$connection = @fsockopen($host, $port, $errno, $errstr, $timeout);

if (is_resource($connection)) {
    echo "BERHASIL: Port $port di $host terbuka.\n";
    fclose($connection);
} else {
    echo "GAGAL: Tidak dapat terhubung ke $host:$port.\n";
    echo "Error: $errstr ($errno)\n";
}
