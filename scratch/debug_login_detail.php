<?php

$host = '113.192.30.33';
$port = 8039;
$user = 'dev-op';
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

echo "[STEP 1] Sending /login (v7 style: plain text)...\n";
$response1 = sendCommand($socket, ['/login', '=name=' . $user, '=password=' . $pass]);
echo "Response: " . json_encode($response1) . "\n\n";

if (isset($response1[0]) && $response1[0] === '!done') {
    echo "=== BERHASIL LOGIN (v7 style) ===\n";

    echo "\n[STEP 2] Getting identity...\n";
    $identity = sendCommand($socket, ['/system/identity/print']);
    echo "Identity Response: " . json_encode($identity) . "\n";

} elseif (isset($response1[0]) && $response1[0] === '!trap') {
    echo "=== LOGIN DITOLAK ===\n";
    foreach ($response1 as $line) {
        if (strpos($line, '=message=') === 0) {
            echo "Error Message: " . substr($line, 9) . "\n";
        }
    }

    echo "\n[STEP 2] Trying v6 challenge-response login...\n";
    $socket2 = @fsockopen($host, $port, $errno, $errstr, 5);
    stream_set_timeout($socket2, 5);
    $challenge_resp = sendCommand($socket2, ['/login']);
    echo "Challenge Response: " . json_encode($challenge_resp) . "\n";

    if (isset($challenge_resp[1]) && strpos($challenge_resp[1], '=ret=') === 0) {
        $challenge = substr($challenge_resp[1], 5);
        $md5 = md5(chr(0) . $pass . pack('H*', $challenge));
        $login2 = sendCommand($socket2, ['/login', '=name=' . $user, '=response=00' . $md5]);
        echo "Login v6 Response: " . json_encode($login2) . "\n";
    }
    fclose($socket2);

} elseif (empty($response1)) {
    echo "=== TIDAK ADA RESPON (timeout/silent) ===\n";
    echo "Kemungkinan: Firewall drop, atau port ini bukan API.\n";
} else {
    echo "=== RESPON TIDAK DIKENAL ===\n";
}

fclose($socket);
