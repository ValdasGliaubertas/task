<?php

// This file generates a new encryption key for the file storage.
// File is auto deleted after key generation.

if (!is_dir('/var/www/secure_storage')) {
    mkdir('/var/www/secure_storage', 0700, true);
}

if (file_exists('/var/www/secure_storage/encryption.key')) {
    echo "Key already exists. Exiting.\n";
    exit(1);
}

// Some readonly persistent storage for the encryption key should be used in production.
// Or like docker secrets...
$key = sodium_crypto_secretbox_keygen();
file_put_contents('/var/www/secure_storage/encryption.key', base64_encode($key));
chmod('/var/www/secure_storage/encryption.key', 0400);
echo "Encryption key generated\n";