<?php

declare(strict_types=1);

namespace App\Service;

use Random\RandomException;
use RuntimeException;
use SodiumException;

class DataEncryptionService implements EncryptorServiceInterface
{

    private string $key;

    public function __construct(string $key_path = '/var/www/secure_storage/encryption.key')
    {
        // if empty generate key
        if (!file_exists($key_path)) {
            throw new RuntimeException("Encryption key file not found.");
        }

        $encoded = trim(file_get_contents($key_path));
        $key = base64_decode($encoded, true);

        if ($key === false || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new RuntimeException("Invalid encryption key format");
        }
        $this->key = $key;
    }

    /**
     * @throws SodiumException|RandomException
     */
    public function encrypt(string $data): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        // Encrypt (authenticated)
        $ciphertext = sodium_crypto_secretbox($data, $nonce, $this->key);

        // Combine nonce + ciphertext for storage
        return base64_encode($nonce . $ciphertext);
    }

    /**
     * @throws SodiumException
     */
    public function decrypt(string $encoded): string
    {
        $decoded = base64_decode($encoded);
        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);

        if ($decrypted === false) {
            throw new RuntimeException("Unable to decrypt data");
        }

        return $decrypted;
    }

}