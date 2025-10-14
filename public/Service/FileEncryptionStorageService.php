<?php

declare(strict_types=1);

namespace App\Service;

use Exception;

final class FileEncryptionStorageService implements FileStorageServiceInterface
{
    private string $upload_dir;

    public function __construct(
        private readonly EncryptorServiceInterface $encryptor,
        string $upload_dir = __DIR__ . '/../../uploads'
    ) {
        $this->upload_dir = $upload_dir;
    }

    /**
     * Encrypts and stores the uploaded file.
     * @param array $file The uploaded file information from $_FILES
     *
     * @throws Exception
     *
     * @return string The new filename of the stored file
     */
    public function store(array $file): string
    {
        // Encrypted file storage
        if (!is_dir($this->upload_dir)) {
            throw new Exception("Upload directory does not exist");
        }

        // Generate unique filename
        try {
            $new_file_name = sprintf(
                '%s_%s.jpg',
                preg_replace('/[^a-zA-Z0-9]/', '_', pathinfo($file['name'], PATHINFO_FILENAME)),
                bin2hex(random_bytes(5))
            );
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $destination = $this->upload_dir . DIRECTORY_SEPARATOR . $new_file_name;

        // Get file contents and encrypt
        $file_contents = file_get_contents($file['tmp_name']);

        try {
            $encrypted = $this->encryptor->encrypt($file_contents);
            // Store file to the destination:
            $file_stored = file_put_contents($destination, $encrypted);
            unlink($file['tmp_name']);
        } catch (Exception $e) {
            throw new Exception('File encryption failed. ' . $e->getMessage());
        }

        if (!$file_stored) {
            throw new Exception('Failed to write encrypted data to file.');
        }

        return $new_file_name;
    }
}