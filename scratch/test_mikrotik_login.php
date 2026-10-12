<?php

require_once __DIR__ . '/../app/Services/MikrotikService.php';

use App\Services\MikrotikService;

$host = '113.192.30.33';
$port = 8114;
$user = 'dev-op';
$pass = 'root_admin.,*';

echo "Mencoba login ke MikroTik $host:$port dengan user $user...\n";

$mikrotik = new MikrotikService($host, $user, $pass, $port);

if ($mikrotik->connect()) {
    echo "BERHASIL LOGIN!\n";
    $identity = $mikrotik->getSystemIdentity();
    echo "Identitas MikroTik: " . $identity . "\n";
    $mikrotik->disconnect();
} else {
    echo "GAGAL LOGIN: Username atau password salah, atau API tidak diizinkan.\n";
}
