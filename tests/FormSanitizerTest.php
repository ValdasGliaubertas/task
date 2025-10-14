<?php

declare(strict_types=1);

use App\maps\InputMap;
use App\Service\FormSanitizerService;
use App\Service\Sanitizers\SanitizeEmail;
use App\Service\Sanitizers\SanitizeLoanAmount;
use App\Service\Sanitizers\SanitizePhoneNr;
use App\Service\Sanitizers\SanitizeString;
use PHPUnit\Framework\TestCase;

final class FormSanitizerTest extends TestCase
{
    private FormSanitizerService $sanitizer;

    public static function sanitizeErrorsProvider(): array
    {
        return [
            [['nonexistent_key' => 'value'], ['nonexistent_key']],
            [[InputMap::EMAIL => null], ['another_key']],
            [[], ['emai']],
            [['some key'], ['some key']],
            [[], ['test']]
        ];
    }

    public static function sanitizeSuccessProvider(): array
    {
        return [
            [[InputMap::EMAIL => 'email@test.com'], [InputMap::EMAIL]],
            [[InputMap::PHONE => '+37648383726'], [InputMap::PHONE]],
            [[InputMap::FULL_NAME => 'Name Surname'], [InputMap::FULL_NAME]],
            [[InputMap::LOAN_AMOUNT => '1234'], [InputMap::LOAN_AMOUNT]],
        ];
    }

    public function tearDown(): void
    {
        unset($this->sanitizer);
    }

    /**
     * @dataProvider sanitizeErrorsProvider
     */
    public function testErrorsForSanitize(array $input = [], $allowed_keys = []): void
    {
        $this->sanitizer->sanitizeInputs($input, $allowed_keys);
        $this->assertNotEmpty($this->sanitizer->getErrors());
    }

    /**
     * @dataProvider sanitizeSuccessProvider
     */
    public function testSuccessForSanitize(array $input = [], $allowed_keys = []): void
    {
        $this->sanitizer->sanitizeInputs($input, $allowed_keys);
        $this->assertEmpty($this->sanitizer->getErrors());
    }

    protected function setUp(): void
    {
        $this->sanitizer = new FormSanitizerService([
            new SanitizeLoanAmount(),
            new SanitizeEmail(),
            new SanitizePhoneNr(),
            new SanitizeString(),
        ]);
    }
}