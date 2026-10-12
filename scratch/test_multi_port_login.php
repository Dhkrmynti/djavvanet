<?php

require_once __DIR__ . '/../app/Services/MikrotikService.php';
use App\Services\MikrotikService;

$host = '113.192.30.33';
$user = 'dev-op';
$pass = 'root_admin.,*';

$ports = [8114, 8728, 8729, 8291];

foreach ($ports as $port) {
    echo "--- Mengetes Port $port ---\n";
    $mikrotik = new MikrotikService($host, $user, $pass, $port);
    if ($mikrotik->connect()) {
        echo "BERHASIL LOGIN di port $port!\n";
        echo "Identity: " . $mikrotik->getSystemIdentity() . "\n";
        $mikrotik->disconnect();
        break;
    } else {
        echo "Gagal login di port $port.\n";
    }
}
