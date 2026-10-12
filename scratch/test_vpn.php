<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\MikrotikService;

$host = 'id-34.tunnel.web.id';
$port = 7083;
$user = 'ap';
$pass = 'root_admin.,*';

echo "Testing VPN Remote connection to {$host}:{$port}...\n";

$mikrotik = new MikrotikService($host, $user, $pass, $port, 10);
if ($mikrotik->connect()) {
    echo "BERHASIL KONEK!\n";
    echo "Identity: " . $mikrotik->getSystemIdentity() . "\n";
    $mikrotik->disconnect();
} else {
    echo "GAGAL KONEK. Pastikan client sudah mengaktifkan VPN-nya di router.\n";
}
