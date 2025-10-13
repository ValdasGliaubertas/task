<?php

declare(strict_types=1);

use App\Service\Sanitizers\SanitizeLoanAmount;
use App\Service\Sanitizers\SanitizerInterface;
use PHPUnit\Framework\TestCase;

final class SanitizeLoanAmountTest extends TestCase
{

    private SanitizerInterface $sanitizer;

    public static function sanitizeLoanAmountProvider(): array
    {
        return [
            // 1. Basic number with spaces
            ['  1000  ', '1000'],

            // 2. Number with commas
            ['1,000', '1000'],

            // 3. Number with currency symbol
            ['$1000', '1000'],

            // 4. Number with decimal point
            ['1000.50', '1000.50'],

            // 5. Number with currency symbol and commas
            ['€1,000.75', '1000.75'],

            // 6. Number with leading zeros
            ['0001234', '1234'],

            // 7. Number with spaces and currency symbol
            ['  £ 2,500 ', '2500'],

            // 8. Number with special characters (should be removed)
            ['1@000#$', '1000'],

            // 9. Negative number (should not keep the minus sign)
            ['-1500', '1500'],

            // 10. Plain number without formatting
            ['7500', '7500'],

            // 11. decimal with only a fractional part
            ['.75', '0.75'],
        ];
    }

    public function setUp(): void
    {
        $this->sanitizer = new SanitizeLoanAmount();
    }

    /**
     * @dataProvider sanitizeLoanAmountProvider
     */
    public function testSanitizeLoanAmount(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->sanitizer->sanitize($input));
    }

    public function SupportsProvider(): array
    {
        return [
            ['loan+amount', false],
            [InputMap::LOAN_AMOUNT, true],
            ['LoanAmount', false],
            ['Loan_Amount', false],
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