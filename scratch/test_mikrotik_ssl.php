<?php

$host = '113.192.30.33';
$port = 8114;
$timeout = 5;

echo "Mencoba menghubungkan ke SSL $host:$port...\n";

$context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
$connection = @stream_socket_client("ssl://$host:$port", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);

if ($connection) {
    echo "BERHASIL: Port $port di $host terbuka dengan SSL.\n";
    fclose($connection);
} else {
    echo "GAGAL: Tidak dapat terhubung ke SSL $host:$port.\n";
    echo "Error: $errstr ($errno)\n";
}
