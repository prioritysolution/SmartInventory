<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Hash;

trait VerifiesStoredPassword
{
    protected function hashPlainPassword(?string $plain): ?string
    {
        if ($plain === null || trim($plain) === '') {
            return null;
        }

        return Hash::make($plain);
    }

    protected function passwordFromColumn(mixed $stored): string
    {
        if (is_resource($stored)) {
            $stored = stream_get_contents($stored) ?: '';
        }

        return is_string($stored) ? $stored : (string) $stored;
    }

    protected function isBcryptPassword(mixed $stored): bool
    {
        return preg_match('/^\$2[ayb]\$\d{2}\$/', $this->passwordFromColumn($stored)) === 1;
    }

    protected function loginPasswordIsValid(mixed $stored, string $plain): bool
    {
        $hash = $this->passwordFromColumn($stored);
        if ($hash === '') {
            return false;
        }

        if ($this->isBcryptPassword($hash)) {
            return Hash::check($plain, $hash);
        }

        if ($this->legacyAesPasswordMatches($plain, $hash)) {
            return true;
        }

        // Legacy plain-text passwords still present in some agent rows
        return hash_equals($hash, $plain);
    }

    protected function legacyAesPasswordMatches(string $plain, string $stored): bool
    {
        $encrypted = $this->legacyAesEncrypt($plain);
        if ($encrypted === false) {
            return false;
        }

        return hash_equals($stored, $encrypted);
    }

    protected function legacyAesEncrypt(string $plain): string|false
    {
        $keyStr = hash('sha512', '123@');
        $aesKey = $this->mysqlAesCreateKey($keyStr, 16);

        return openssl_encrypt($plain, 'AES-128-ECB', $aesKey, OPENSSL_RAW_DATA);
    }

    protected function mysqlAesCreateKey(string $keyStr, int $keyLength = 16): string
    {
        $rkey = str_repeat("\0", $keyLength);
        $keyLen = strlen($keyStr);
        for ($i = 0; $i < $keyLen; $i++) {
            $rkey[$i % $keyLength] = chr(ord($rkey[$i % $keyLength]) ^ ord($keyStr[$i]));
        }

        return $rkey;
    }
}
