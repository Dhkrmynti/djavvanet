<?php

// Clone simple API logic with SSL support for testing
class MikrotikSSLTest
{
    private $socket = null;
    private $host;
    private $port;
    private $user;
    private $pass;

    public function __construct($host, $user, $pass, $port = 8114)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->port = $port;
    }

    public function connect()
    {
        $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $this->socket = @stream_socket_client("ssl://{$this->host}:{$this->port}", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
        if (!$this->socket) return false;
        return $this->login();
    }

    private function login()
    {
        $response = $this->send(['/login', '=name=' . $this->user, '=password=' . $this->pass]);
        if (isset($response[0]) && $response[0] === '!done') return true;
        
        if (isset($response[0]) && $response[0] === '!re') {
            $challenge = '';
            foreach ($response as $line) {
                if (strpos($line, '=ret=') === 0) $challenge = substr($line, 5);
            }
            $md5 = md5(chr(0) . $this->pass . pack('H*', $challenge));
            $response2 = $this->send(['/login', '=name=' . $this->user, '=response=00' . $md5]);
            return isset($response2[0]) && $response2[0] === '!done';
        }
        return false;
    }

    public function send($commands)
    {
        foreach ($commands as $cmd) {
            $this->writeWord($cmd);
        }
        $this->writeWord('');
        return $this->readResponse();
    }

    private function writeWord($word)
    {
        $len = strlen($word);
        if ($len < 0x80) $prefix = chr($len);
        elseif ($len < 0x4000) { $len |= 0x8000; $prefix = chr(($len >> 8) & 0xFF) . chr($len & 0xFF); }
        else { /* Simplified for test */ $prefix = chr($len); }
        fwrite($this->socket, $prefix . $word);
    }

    private function readResponse()
    {
        $result = [];
        while (true) {
            $word = $this->readWord();
            if ($word === null) break;
            if ($word === '') {
                if (!empty($result) && in_array(end($result), ['!done', '!trap', '!fatal'])) break;
                continue;
            }
            $result[] = $word;
            if (in_array($word, ['!done', '!trap', '!fatal'])) break;
        }
        return $result;
    }

    private function readWord()
    {
        $data = fread($this->socket, 1);
        if ($data === false || $data === '') return null;
        $len = ord($data);
        if ($len == 0) return '';
        if ($len & 0x80) {
             $b = fread($this->socket, 1);
             $len = (($len & ~0x80) << 8) | ord($b);
        }
        $res = '';
        while (strlen($res) < $len) {
            $res .= fread($this->socket, $len - strlen($res));
        }
        return $res;
    }
}

$host = '113.192.30.33';
$port = 8114;
$user = 'dev-op';
$pass = 'root_admin.,*';

echo "Mencoba login SSL ke MikroTik $host:$port...\n";
$tester = new MikrotikSSLTest($host, $user, $pass, $port);
if ($tester->connect()) {
    echo "BERHASIL LOGIN via SSL!\n";
} else {
    echo "GAGAL LOGIN via SSL.\n";
}
