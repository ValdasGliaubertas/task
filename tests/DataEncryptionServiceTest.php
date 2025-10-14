<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Service\DataEncryptionService;
use PHPUnit\Framework\TestCase;
use Random\RandomException;
use RuntimeException;
use SodiumException;

final class DataEncryptionServiceTest extends TestCase
{
    private string $tempKeyPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempKeyPath = sys_get_temp_dir() . '/encryption_key_' . uniqid();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempKeyPath)) {
            unlink($this->tempKeyPath);
        }
        parent::tearDown();
    }

    private function createValidKeyFile(): void
    {
        $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        file_put_contents($this->tempKeyPath, base64_encode($key));
    }

    public function testThrowsExceptionIfKeyFileMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Encryption key file not found.');

        new DataEncryptionService($this->tempKeyPath);
    }

    public function testThrowsExceptionForInvalidKeyFormat(): void
    {
        // Write invalid key data
        file_put_contents($this->tempKeyPath, base64_encode('short_key'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid encryption key format');

        new DataEncryptionService($this->tempKeyPath);
    }

    /**
     * @throws RandomException
     */
    public function testEncryptAndDecryptReturnsOriginalData(): void
    {
        $this->createValidKeyFile();

        $service = new DataEncryptionService($this->tempKeyPath);
        $plaintext = 'Sensitive test data 123!';

        $encrypted = $service->encrypt($plaintext);
        $this->assertNotEmpty($encrypted, 'Encrypted string should not be empty.');
        $this->assertNotSame($plaintext, $encrypted, 'Encrypted string should differ from plaintext.');

        $decrypted = $service->decrypt($encrypted);
        $this->assertSame($plaintext, $decrypted, 'Decrypted data should match original plaintext.');
    }

    /**
     * @throws RandomException
     */
    public function testDecryptThrowsExceptionForTamperedData(): void
    {
        $this->createValidKeyFile();

        $service = new DataEncryptionService($this->tempKeyPath);
        $plaintext = 'Hello World';

        $encrypted = $service->encrypt($plaintext);

        // Tamper with ciphertext
        $tampered = substr($encrypted, 0, -2) . 'zz';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to decrypt data');

        $service->decrypt($tampered);
    }
}
