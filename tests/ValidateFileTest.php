<?php

declare(strict_types=1);

namespace Tests\Service\Validators;

use App\Service\Validators\FileValidatorInterface;
use App\Service\Validators\ValidateJPGFile;
use PHPUnit\Framework\TestCase;

final class ValidateFileTest extends TestCase
{

    private FileValidatorInterface $validator;

    public function setUp(): void
    {
        $this->validator = new ValidateJPGFile();
    }


    /**
     * Test that a missing file fails validation.
     */
    public function testMissingFileFails(): void
    {
        $this->validator->validate([]);
        $errors = $this->validator->getErrors();
        $this->assertContains('File is required.', $errors);
    }

    /**
     * Test that a non-JPEG file fails validation.
     */
    public function testNonJpegFileFails(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'tst');
        file_put_contents($tmpFile, 'not an image');

        $files = [
            'name' => 'test.png',
            'type' => 'image/png',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => 1000
        ];

        $this->validator->validate($files);
        $errors = $this->validator->getErrors();
        $this->assertContains('Only JPEG files are allowed.', $errors);

        unlink($tmpFile);
    }

    /**
     * Test that a file exceeding the size limit fails validation.
     */
    public function testFileTooLargeFails(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'jpg');
        file_put_contents($tmpFile, "\xFF\xD8\xFF" . str_repeat('A', 3 * 1024 * 1024)); // 3MB

        $files = [
            'name' => 'bigfile.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => 3 * 1024 * 1024
        ];

        $this->validator->validate($files);
        $errors = $this->validator->getErrors();
        $this->assertContains('File exceeds maximum size of 2MB.', $errors);

        unlink($tmpFile);
    }

    /**
     * Test that a file upload error code fails validation.
     */
    public function testUploadErrorCodeFails(): void
    {
        $files = [
            'tmp_name' => '/tmp/file',
            'error' => UPLOAD_ERR_NO_FILE,
            'size' => 0
        ];

        $this->validator->validate($files);
        $errors = $this->validator->getErrors();
        $this->assertStringContainsString('File upload failed with error code', reset($errors));
    }


    public function SupportsProvider(): array
    {
        return [
            ['file.jpg', true],
            ['file.jpeg', true],
            ['file', false],
            ['file.png', false],
        ];
    }


    /**
     * @dataProvider SupportsProvider
     */
    function testSupports(string $input, bool $expected): void
    {
        $this->assertSame($this->validator->supports($input), $expected);
    }
}