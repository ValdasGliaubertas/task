<?php

declare(strict_types=1);

namespace Model;

use Random\RandomException;
use SodiumException;

class DataEncryptor
{

    private string $key;

    public function __construct(string $key) {
        // if empty generate key

        if (strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new \InvalidArgumentException('Key must be 32 bytes long.');
        }
        $this->key = $key;
    }

    /**
     * @throws SodiumException|RandomException
     */
    public function encrypt(string $data): string
    {
        $message = "Sensitive data to protect";

        // Generate a random 32-byte secret key (store this securely!)
        $key = sodium_crypto_secretbox_keygen();

        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        // Encrypt (authenticated)
        $ciphertext = sodium_crypto_secretbox($message, $nonce, $key);

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

        $decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

        if ($decrypted === false) {
            die("Decryption failed: data may be corrupted or wrong key used.");
        }
    }

}