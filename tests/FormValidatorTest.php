<?php

declare(strict_types=1);

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
        $tmpFile = tempnam(sys_get_temp_dir(), 'tst');
        file_put_contents($tmpFile, "\xFF\xD8\xFF" . 'fake image content');
        // simulate jpeg
        rename($tmpFile, $tmpFile . '.jpg');
        $tmpFile .= '.jpg';

        $input = [
            'full_name' => 'John Doe',
            'email' => 'example@example.com',
            'phone' => '+37062426954',
            'loan_amount' => '5000'
        ];

        $this->validator->validateInputs($input);
        $errors = $this->validator->getErrors();
        $this->assertEmpty($errors);
        unlink($tmpFile);
    }

    /**
     * Test that valid input passes all validations.
     */
    public function testValidFilePasses(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'tst');
        file_put_contents($tmpFile, "\xFF\xD8\xFF" . 'fake image content');
        // simulate jpeg
        rename($tmpFile, $tmpFile . '.jpg');
        $tmpFile .= '.jpg';

        $files = [
            'file' => [
                'name' => 'passport.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ];

        $this->validator->validateFiles($files);
        $errors = $this->validator->getErrors();
        $this->assertEmpty($errors);
        unlink($tmpFile);
    }

    /**
     * Test that valid input passes all validations.
     */
    public function testInvalidInputPasses(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'tst');
        file_put_contents($tmpFile, 'fake image content');
        // simulate jpeg
        rename($tmpFile, $tmpFile . '.jpg');
        $tmpFile .= '.jpg';

        $input = [
            'full_name' => 'Jo',
            'email' => 'example@example@.com',
            'phone' => '+370',
            'loan_amount' => '0'
        ];

        $this->validator->validateInputs($input);
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertCount(4, $errors);
        unlink($tmpFile);
    }

    /**
     * Test that valid input passes all validations.
     */
    public function testInvalidInputFilePasses(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'tst');
        file_put_contents($tmpFile, 'fake image content');
        // simulate jpeg
        rename($tmpFile, $tmpFile . '.jpg');
        $tmpFile .= '.jpg';

        $files = [
            'file' => [
                'name' => 'passport.jpg',
                'type' => 'image/png',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => 5024
            ]
        ];

        $this->validator->validateFiles($files);
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
        unlink($tmpFile);
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
}