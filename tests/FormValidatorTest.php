<?php

declare(strict_types=1);

use App\maps\InputMap;
use App\Service\DnsCheckerInterface;
use App\Service\FormValidatorService;
use App\Service\Validators\ValidateEmail;
use App\Service\Validators\ValidateFullName;
use App\Service\Validators\ValidateJPGFile;
use App\Service\Validators\validateLoanAmount;
use App\Service\Validators\ValidatePhoneNr;
use PHPUnit\Framework\TestCase;

final class FormValidatorTest extends TestCase
{
    private FormValidatorService $validator;

    /**
     * Test that valid input passes all validations.
     */
    public function testValidInputPasses(): void
    {
        $input = [
            InputMap::FULL_NAME => 'John Doe',
            InputMap::EMAIL => 'example@example.com',
            InputMap::PHONE => '+37062426954',
            InputMap::LOAN_AMOUNT => '5000'
        ];

        $this->validator->validateInputs($input);
        $errors = $this->validator->getErrors();
        $this->assertEmpty($errors);
    }

    /**
     * Test that valid input passes all validations.
     */
    public function testValidFilePasses(): void
    {
        $files = $this->_simulateUploadedFile();
        $this->validator->validateFiles($files, [inputMap::FILE_NAME]);
        $errors = $this->validator->getErrors();
        $this->assertEmpty($errors);
        unlink($files['file']['tmp_name']);
    }

    /**
     * Test that valid input passes all validations.
     */
    public function testInvalidInputPasses(): void
    {
        $input = [
            InputMap::FULL_NAME => 'Jo',
            InputMap::EMAIL => 'example@example@.com',
            InputMap::PHONE => '+370',
            InputMap::LOAN_AMOUNT => '0'
        ];

        $this->validator->validateInputs($input);
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertCount(4, $errors);
    }

    /**
     * Test that file validation fails due to size.
     */
    public function testInvalidInputFileSize(): void
    {
        $files = $this->_simulateUploadedFile();
        $files['file']['size'] = 3 * 1024 * 1024; // 3MB to trigger size error

        $this->validator->validateFiles($files, [inputMap::FILE_NAME]);
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
        unlink($files['file']['tmp_name']);
    }

    /**
     * Test that file validation fails due to wrong file type passed.
     */
    public function testInvalidInputFileWrongMime(): void
    {
        $files = $this->_simulateUploadedFile(FALSE); // Pass invalid headers
        $files['file']['type'] = 'image/png'; // wrong mime type

        $this->validator->validateFiles($files, [inputMap::FILE_NAME]);
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
        unlink($files['file']['tmp_name']);
    }

    /**
     * Test that file validation fails due to wrong file name suffix passed.
     */
    public function testInvalidInputFileWrongNameSuffix(): void
    {
        $files = $this->_simulateUploadedFile(TRUE); // Pass invalid headers
        $files['file']['name'] = 'passport.png'; // wrong suffix

        $this->validator->validateFiles($files, [inputMap::FILE_NAME]);
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
        unlink($files['file']['tmp_name']);
    }

    /**
     * Test that file validation fails due to wrong file name passed.
     */
    public function testInvalidInputFileWrongInputName(): void
    {
        $files = $this->_simulateUploadedFile();
        $files['new_file'] = $files['file'];
        unset($files['file']);

        $this->validator->validateFiles($files, [inputMap::FILE_NAME]);
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
        unlink($files['new_file']['tmp_name']);
    }

    /**
     * Test that neither file neither key is passed.
     */
    public function testInputFileWhenKeyAndFileMissing(): void
    {
        $this->validator->validateFiles([], []);
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
    }

    /**
     * Test that file is passed but key missing (expected file name).
     */
    public function testInputFileValidationWhenKeyIsMissing(): void
    {
        $files = $this->_simulateUploadedFile();
        $this->validator->validateFiles($files, []);
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
        unlink($files['file']['tmp_name']);
    }

    /**
     * Test that file is passed but key missing (expected file name).
     */
    public function testInputFileValidationWhenFileIsMissing(): void
    {
        $this->validator->validateFiles([], [InputMap::FILE_NAME]);
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
    }

    public function testInputFileValidationWhenValidatorIsMissing(): void
    {
        $this->validator = new FormValidatorService(
            [],
            []
        );
        $files = $this->_simulateUploadedFile();
        $this->validator->validateFiles($files, [InputMap::FILE_NAME]);
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
    }

    /**
     * Test that valid input passes, but no validator is found.
     */
    public function testValidInputPassesButNoValidatorsFound(): void
    {
        $mock = $this->createMock(DnsCheckerInterface::class);
        $mock->method('domainHasMxRecord')->willReturn(true);
        $this->validator = new FormValidatorService(
            [
                new ValidateEmail($mock),
                new ValidateFullName(),
                new ValidatePhoneNr(),
                new validateLoanAmount()
            ],
            []
        );

        $input = [
            InputMap::FULL_NAME => 'John Doe',
            InputMap::EMAIL => 'example@example.com',
            InputMap::PHONE => '+37062426954',
            InputMap::LOAN_AMOUNT => '5000'
        ];

        $this->validator->validateInputs($input);
        $errors = $this->validator->getErrors();
        $this->assertEmpty($errors);
    }

    protected function setUp(): void
    {
        $mock = $this->createMock(DnsCheckerInterface::class);
        $mock->method('domainHasMxRecord')->willReturn(true);
        $this->validator = new FormValidatorService(
            [
                new ValidateEmail($mock),
                new ValidateFullName(),
                new ValidatePhoneNr(),
                new validateLoanAmount()
            ],
            [
                new ValidateJPGFile()
            ]
        );
    }

    private function _simulateUploadedFile(bool $valid = true): array
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'tst');
        $header = "";
        if ($valid) {
            $header = "\xFF\xD8\xFF"; // JPEG header
        }
        file_put_contents($tmpFile, $header . 'fake image content');
        // simulate jpeg
        rename($tmpFile, $tmpFile . '.jpg');
        $tmpFile .= '.jpg';

        $files = [
            'file' => [
                'name' => 'passport.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => 5024
            ]
        ];
        return $files;
    }
}