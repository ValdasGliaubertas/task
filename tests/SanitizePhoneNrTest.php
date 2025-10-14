<?php

declare(strict_types=1);

use App\maps\InputMap;
use App\Service\Sanitizers\SanitizePhoneNr;
use App\Service\Sanitizers\SanitizerInterface;
use PHPUnit\Framework\TestCase;

final class SanitizePhoneNrTest extends TestCase
{

    private SanitizerInterface $sanitizer;

    public static function sanitizeMobilePhoneNrProvider(): array
    {
        return [
            // 1. Basic number with spaces
            ['  +1 234 567 8900  ', '+12345678900'],

            // 2. Number with dashes, should be the same
            ['+1-234-567-8900', '+1-234-567-8900'],

            // 3. Number with parentheses
            ['(123) 456-7890', '123456-7890'],

            // 4. Number with dots
            ['123.456.7890', '1234567890'],

            // 5. Number with country code and spaces
            ['+44 20 7946 0958', '+442079460958'],

            // 6. Number with leading zeros
            ['0044 20 7946 0958', '00442079460958'],

            // 7. Number with mixed formatting
            ['+1 (234) 567-8900 ext. 123', '+1234567-8900123'],

            // 8. Number with letters (should be removed)
            ['1-800-FLOWERS', '1-800-'],

            // 9. Number with special characters
            ['+1@234#567$8900!', '+12345678900'],

            // 10. Plain number without formatting
            ['1234567890', '1234567890'],
        ];
    }

    public function setUp(): void
    {
        $this->sanitizer = new SanitizePhoneNr();
    }

    /**
     * @dataProvider sanitizeMobilePhoneNrProvider
     */
    public function testSanitizeMobilePhoneNr(string $input, string $expected)
    {
        $this->assertSame($expected, $this->sanitizer->sanitize($input));
    }

    public function SupportsProvider(): array
    {
        return [
            [InputMap::PHONE, true],
            ['Phone', false],
            ['Phone1', false],
            ['PHONE', false],
            ['email', false],
            ['full_name', false],
            ['loan_amount', false],
            ['random_key', false],
        ];
    }


    /**
     * @dataProvider SupportsProvider
     */
    function testSupports(string $input, bool $expected): void
    {
        $this->assertSame(in_array($input, $this->sanitizer->supportedKeys()), $expected);
    }
}