<?php

namespace App\Services;

class MikrotikService
{
    private $socket = null;
    private string $host;
    private int $port;
    private string $user;
    private string $pass;
    private int $timeout;

    public function __construct(string $host, string $user, string $pass, int $port = 8728, int $timeout = 5)
    {
        $this->host    = $host;
        $this->user    = $user;
        $this->pass    = $pass;
        $this->port    = $port;
        $this->timeout = $timeout;
    }

    public function connect(): bool
    {
        try {
            $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
            if (!$this->socket) {
                return false;
            }
            stream_set_timeout($this->socket, $this->timeout);
            return $this->login();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function login(): bool
    {
        $response = $this->send(['/login', '=name=' . $this->user, '=password=' . $this->pass]);
        if (isset($response[0]) && $response[0] === '!done') {
            return true;
        }
        
        if (isset($response[0]) && $response[0] === '!re') {
            $challenge = '';
            foreach ($response as $line) {
                if (str_starts_with($line, '=ret=')) {
                    $challenge = substr($line, 5);
                }
            }
            $md5 = md5(chr(0) . $this->pass . pack('H*', $challenge));
            $response2 = $this->send(['/login', '=name=' . $this->user, '=response=00' . $md5]);
            return isset($response2[0]) && $response2[0] === '!done';
        }
        return false;
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    public function send(array $commands): array
    {
        if (!$this->socket) return [];

        foreach ($commands as $cmd) {
            $this->writeWord($cmd);
        }
        $this->writeWord(''); 

        return $this->readResponse();
    }

    private function writeWord(string $word): void
    {
        $len    = strlen($word);
        $prefix = '';
        if ($len < 0x80) {
            $prefix = chr($len);
        } elseif ($len < 0x4000) {
            $len |= 0x8000;
            $prefix = chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
        } elseif ($len < 0x200000) {
            $len |= 0xC00000;
            $prefix = chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
        } else {
            $len |= 0xE0000000;
            $prefix = chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
        }
        fwrite($this->socket, $prefix . $word);
    }

    private function readWord(): ?string
    {
        if (!$this->socket || feof($this->socket)) return null;
        $data = fread($this->socket, 1);
        if ($data === false || $data === '') return null;
        $firstByte = ord($data);
        
        if ($firstByte === 0) return '';
        $len = $firstByte;
        if ($firstByte & 0x80) {
            if (($firstByte & 0xC0) === 0x80) {
                $b = fread($this->socket, 1);
                if ($b === false || $b === '') return null;
                $len = (($firstByte & ~0x80) << 8) | ord($b);
            } elseif (($firstByte & 0xE0) === 0xC0) {
                $b2 = fread($this->socket, 2);
                if ($b2 === false || strlen($b2) < 2) return null;
                $len = (($firstByte & ~0xC0) << 16) | (ord($b2[0]) << 8) | ord($b2[1]);
            } elseif (($firstByte & 0xF0) === 0xE0) {
                $b3 = fread($this->socket, 3);
                if ($b3 === false || strlen($b3) < 3) return null;
                $len = (($firstByte & ~0xE0) << 24) | (ord($b3[0]) << 16) | (ord($b3[1]) << 8) | ord($b3[2]);
            }
        }
        if ($len <= 0) return '';
        
        $res = '';
        $remaining = $len;
        while ($remaining > 0 && !feof($this->socket)) {
            $chunk = fread($this->socket, $remaining);
            if ($chunk === false) return null;
            $res .= $chunk;
            $remaining -= strlen($chunk);
        }
        return $res;
    }

    private function readResponse(): array
    {
        $result = [];
        while (true) {
            $word = $this->readWord();
            
            if ($word === null) {
                break;
            }

            if ($word === '') {
                if (!empty($result) && in_array(end($result), ['!done', '!trap', '!fatal'])) {
                    break;
                }
                continue;
            }
            $result[] = $word;
            if (in_array($word, ['!done', '!trap', '!fatal'])) {
                $next = $this->readWord();
                while ($next !== '' && $next !== null) {
                    $result[] = $next;
                    $next = $this->readWord();
                }
                break;
            }
        }
        return $result;
    }

    public static function parseResponse(array $response): array
    {
        $items  = [];
        $current = [];
        foreach ($response as $word) {
            if ($word === '!re') {
                if (!empty($current)) $items[] = $current;
                $current = [];
            } elseif (str_starts_with($word, '=')) {
                $parts = explode('=', ltrim($word, '='), 2);
                if (count($parts) === 2) {
                    $current[$parts[0]] = $parts[1];
                }
            }
        }
        if (!empty($current)) $items[] = $current;
        return $items;
    }

    public function getSystemResource(): ?array
    {
        $res = $this->send(['/system/resource/print']);
        $parsed = self::parseResponse($res);
        return $parsed[0] ?? null;
    }

    public function getSystemIdentity(): string
    {
        $res    = $this->send(['/system/identity/print']);
        $parsed = self::parseResponse($res);
        return $parsed[0]['name'] ?? 'Unknown';
    }

    public function getPPPoESecrets(): array
    {
        $res = $this->send(['/ppp/secret/print']);
        return self::parseResponse($res);
    }

    public function getActivePPPoE(): array
    {
        $res = $this->send(['/ppp/active/print']);
        return self::parseResponse($res);
    }

    public function getPPPoEProfiles(): array
    {
        $res = $this->send(['/ppp/profile/print']);
        return self::parseResponse($res);
    }

    public function addPPPoESecret(string $name, string $password, string $profile = 'default', string $service = 'pppoe'): bool
    {
        $res = $this->send([
            '/ppp/secret/add',
            '=name='     . $name,
            '=password=' . $password,
            '=service='  . $service,
            '=profile='  . $profile,
        ]);
        return in_array('!done', $res);
    }

    public function removePPPoESecret(string $name): bool
    {
        $secrets = $this->getPPPoESecrets();
        $id = null;
        foreach ($secrets as $s) {
            if (($s['name'] ?? '') === $name) { $id = $s['.id'] ?? null; break; }
        }
        if (!$id) return false;
        $res = $this->send(['/ppp/secret/remove', '=.id=' . $id]);
        return in_array('!done', $res);
    }

    public function disablePPPoESecret(string $name): bool
    {
        $secrets = $this->getPPPoESecrets();
        $id = null;
        foreach ($secrets as $s) {
            if (($s['name'] ?? '') === $name) { $id = $s['.id'] ?? null; break; }
        }
        if (!$id) return false;
        $res = $this->send(['/ppp/secret/disable', '=.id=' . $id]);
        return in_array('!done', $res);
    }

    public function enablePPPoESecret(string $name): bool
    {
        $secrets = $this->getPPPoESecrets();
        $id = null;
        foreach ($secrets as $s) {
            if (($s['name'] ?? '') === $name) { $id = $s['.id'] ?? null; break; }
        }
        if (!$id) return false;
        $res = $this->send(['/ppp/secret/enable', '=.id=' . $id]);
        return in_array('!done', $res);
    }

    public function removeActivePPPoE(string $name): bool
    {
        $active = $this->getActivePPPoE();
        $id = null;
        foreach ($active as $a) {
            if (($a['name'] ?? '') === $name) { $id = $a['.id'] ?? null; break; }
        }
        if (!$id) return false;
        $res = $this->send(['/ppp/active/remove', '=.id=' . $id]);
        return in_array('!done', $res);
    }

    public function getPPPoESecretByName(string $name): ?array
    {
        $secrets = $this->getPPPoESecrets();
        foreach ($secrets as $s) {
            if (($s['name'] ?? '') === $name) return $s;
        }
        return null;
    }

    public function setPPPoESecretProfile(string $name, string $profile): bool
    {
        $secret = $this->getPPPoESecretByName($name);
        if (!$secret || !isset($secret['.id'])) return false;

        $res = $this->send([
            '/ppp/secret/set',
            '=.id='    . $secret['.id'],
            '=profile=' . $profile,
        ]);
        return in_array('!done', $res);
    }

    public function updatePPPoESecret(string $oldName, array $params): bool
    {
        $secret = $this->getPPPoESecretByName($oldName);
        if (!$secret || !isset($secret['.id'])) return false;

        $commands = ['/ppp/secret/set', '=.id=' . $secret['.id']];
        foreach ($params as $key => $value) {
            $commands[] = "=$key=$value";
        }

        $res = $this->send($commands);
        return in_array('!done', $res);
    }
}
