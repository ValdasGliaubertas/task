<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Service\FileEncryptionStorageService;
use App\Service\EncryptorServiceInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class FileEncryptionStorageServiceTest extends TestCase
{
    private string $tempDir;
    /** @var EncryptorServiceInterface&MockObject */
    private EncryptorServiceInterface $encryptor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/uploads_' . uniqid();
        mkdir($this->tempDir);
        $this->encryptor = $this->createMock(EncryptorServiceInterface::class);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            array_map('unlink', glob($this->tempDir . '/*'));
            rmdir($this->tempDir);
        }
        parent::tearDown();
    }

    private function createTempFile(string $content = 'filecontent'): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'upl_');
        file_put_contents($tmpFile, $content);
        return $tmpFile;
    }

    public function testThrowsExceptionIfUploadDirMissing(): void
    {
        $missingDir = $this->tempDir . '/missing';
        $service = new FileEncryptionStorageService($this->encryptor, $missingDir);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Upload directory does not exist');

        $service->store(['name' => 'test.jpg', 'tmp_name' => $this->createTempFile()]);
    }

    public function testStoresEncryptedFileSuccessfully(): void
    {
        $originalContent = 'plain file data';
        $encryptedContent = 'ENCRYPTED_CONTENT';

        $this->encryptor
            ->expects($this->once())
            ->method('encrypt')
            ->with($originalContent)
            ->willReturn($encryptedContent);

        $tmpFile = $this->createTempFile($originalContent);
        $service = new FileEncryptionStorageService($this->encryptor, $this->tempDir);

        $fileArray = [
            'name' => 'document.jpg',
            'tmp_name' => $tmpFile,
        ];

        $storedFileName = $service->store($fileArray);

        // Assertions
        $this->assertMatchesRegularExpression('/^document_[a-f0-9]{10}\.jpg$/', $storedFileName);
        $storedFilePath = $this->tempDir . DIRECTORY_SEPARATOR . $storedFileName;

        $this->assertFileExists($storedFilePath);
        $this->assertSame($encryptedContent, file_get_contents($storedFilePath));
        $this->assertFileDoesNotExist($tmpFile, 'Temporary file should be deleted after storing.');
    }

    public function testThrowsExceptionOnEncryptionFailure(): void
    {
        $this->encryptor
            ->method('encrypt')
            ->willThrowException(new Exception('encryption failed'));

        $tmpFile = $this->createTempFile('data');
        $service = new FileEncryptionStorageService($this->encryptor, $this->tempDir);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File encryption failed. encryption failed');

        $service->store(['name' => 'testfile.jpg', 'tmp_name' => $tmpFile]);
    }
}
