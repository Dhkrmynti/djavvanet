<?php

$host = '113.192.30.33';
$port = 38893;
$user = 'ap';
$pass = 'root_admin.,*';

echo "=== DEBUG LOGIN MIKROTIK ===\n";
echo "Target: $host:$port\n\n";

$socket = @fsockopen($host, $port, $errno, $errstr, 5);
if (!$socket) {
    die("GAGAL CONNECT: $errstr ($errno)\n");
}
echo "[OK] Socket connected\n";
stream_set_timeout($socket, 5);

function writeWord($socket, $word) {
    $len = strlen($word);
    if ($len < 0x80) {
        $prefix = chr($len);
    } elseif ($len < 0x4000) {
        $len |= 0x8000;
        $prefix = chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
    } else {
        $prefix = chr($len);
    }
    fwrite($socket, $prefix . $word);
}

function readWord($socket) {
    $data = @fread($socket, 1);
    if ($data === false || $data === '') return null;
    $len = ord($data);
    if ($len === 0) return '';
    if ($len & 0x80) {
        if (($len & 0xC0) === 0x80) {
            $b = fread($socket, 1);
            if ($b === false) return null;
            $len = (($len & ~0x80) << 8) | ord($b);
        } elseif (($len & 0xE0) === 0xC0) {
            $b = fread($socket, 2);
            if ($b === false) return null;
            $len = (($len & ~0xC0) << 16) | (ord($b[0]) << 8) | ord($b[1]);
        }
    }
    if ($len <= 0) return '';
    $res = '';
    while (strlen($res) < $len) {
        $chunk = fread($socket, $len - strlen($res));
        if ($chunk === false) return null;
        $res .= $chunk;
    }
    return $res;
}

function sendCommand($socket, $commands) {
    foreach ($commands as $cmd) {
        writeWord($socket, $cmd);
    }
    writeWord($socket, '');

    $result = [];
    while (true) {
        $word = readWord($socket);
        if ($word === null) break;
        if ($word === '') {
            if (!empty($result) && in_array(end($result), ['!done', '!trap', '!fatal'])) break;
            continue;
        }
        $result[] = $word;
        if (in_array($word, ['!done', '!trap', '!fatal'])) {
            $next = readWord($socket);
            while ($next !== '' && $next !== null) {
                $result[] = $next;
                $next = readWord($socket);
            }
            break;
        }
    }
    return $result;
}

echo "[STEP 1] Sending /login...\n";
$response1 = sendCommand($socket, ['/login', '=name=' . $user, '=password=' . $pass]);
echo "Response: " . json_encode($response1) . "\n\n";

if (isset($response1[0]) && $response1[0] === '!done') {
    echo "=== BERHASIL LOGIN! ===\n";
    $identity = sendCommand($socket, ['/system/identity/print']);
    echo "Identity: " . json_encode($identity) . "\n";
} elseif (isset($response1[0]) && $response1[0] === '!trap') {
    echo "=== LOGIN DITOLAK ===\n";
    foreach ($response1 as $line) {
        if (strpos($line, '=message=') === 0) echo "Error: " . substr($line, 9) . "\n";
    }
} elseif (empty($response1)) {
    echo "=== TIDAK ADA RESPON ===\n";
} else {
    echo "=== RESPON LAIN ===\n";
}

fclose($socket);
