<?php

declare(strict_types=1);

namespace App\tests;

use App\Service\FormSanitizerService;
use PHPUnit\Framework\TestCase;

class SanitizerTest extends TestCase
{
    private FormSanitizerService $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new FormSanitizerService();
    }

    public function tearDown(): void
    {
        unset($this->sanitizer);
    }


    public static function sanitizeStringProvider(): array
    {
        return [
            // 1. Basic XSS tag
            [
                '  <script>alert(\'XSS\');</script> Hello World!  ',
                '&lt;script&gt;alert(&#039;XSS&#039;);&lt;/script&gt; Hello World!'
            ],

            // 2. HTML tags
            [
                '<b>Bold</b> and <i>Italic</i>',
                '&lt;b&gt;Bold&lt;/b&gt; and &lt;i&gt;Italic&lt;/i&gt;'
            ],

            // 3. Double quotes
            ['"Quoted text"', '&quot;Quoted text&quot;'],

            // 4. Single quotes
            ["'Single quoted text'", '&#039;Single quoted text&#039;'],

            // 5. Mixed HTML and text
            [
                '<a href="http://example.com">Click</a> here!',
                '&lt;a href=&quot;http://example.com&quot;&gt;Click&lt;/a&gt; here!'
            ],

            // 6. Encoded characters (should remain safe)
            ['Hello & welcome', 'Hello &amp; welcome'],

            // 7. Emoji and UTF-8
            ['😊 <b>happy</b>', '😊 &lt;b&gt;happy&lt;/b&gt;'],

            // 8. JavaScript URI (should escape)
            [
                '<img src="javascript:alert(1)">',
                '&lt;img src=&quot;javascript:alert(1)&quot;&gt;'
            ],

            // 9. Whitespace trimming
            ['   Safe text with spaces   ', 'Safe text with spaces'],

            // 10. Harmless plain text
            ['Hello world!', 'Hello world!'],
        ];
    }

    /**
     * @dataProvider sanitizeStringProvider
     *
     * @covers \App\Service\FormSanitizerService::sanitizeString
     */
    public function testSanitizeString(string $input, string $expected): void
    {
        $this->assertEquals($expected, $this->sanitizer->sanitizeString($input));
    }

    public static function sanitizeEmailProvider(): array
    {
        return [
            // 1. Normal email — should remain unchanged
            ['john.doe@example.com', 'john.doe@example.com'],

            // 2. Uppercase letters — should stay valid (filter doesn't lowercase)
            ['John.Doe@Example.COM', 'John.Doe@Example.COM'],

            // 3. Extra spaces — trimmed and cleaned
            ['  alice.smith@example.com  ', 'alice.smith@example.com'],

            // 4. Email with illegal characters — removed
            ['bob*(at)example.com', 'bob*atexample.com'],

            // 5. Email with angle brackets (from form copy-paste) — stripped
            ['<jane@example.com>', 'jane@example.com'],

            // 6. Quoted name and email — only email remains
            ['"Jane Doe" <jane@example.com>', 'JaneDoejane@example.com'],

            // 7. Special chars that are allowed — should stay
            ['john_doe-123@example-domain.co.uk', 'john_doe-123@example-domain.co.uk'],

            // 8. Control characters — stripped
            ["mike\n@example.com", 'mike@example.com'],

            // 9. Invalid characters like comma — removed
            ['test,email@example.com', 'testemail@example.com'],

            // 10. Email with comment (RFC but invalid for this filter) — comment removed
            ['user(comment)@example.com', 'usercomment@example.com'],
        ];
    }

    /**
     * @dataProvider sanitizeEmailProvider
     *
     * @covers \App\Service\FormSanitizerService::sanitizeEmail
     */
    public function testSanitizeEmail(string $input, string $expected): void
    {
        $this->assertEquals($expected, $this->sanitizer->sanitizeEmail($input));
    }


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

    /**
     * @dataProvider sanitizeMobilePhoneNrProvider
     *
     * @covers \App\Service\FormSanitizerService::sanitizeMobilePhoneNr
     */
    public function testSanitizeMobilePhoneNr(string $input, string $expected)
    {
        $this->assertSame($expected, $this->sanitizer->sanitizeMobilePhoneNr($input));
    }

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

    /**
     * @dataProvider sanitizeLoanAmountProvider
     *
     * @covers \App\Service\FormSanitizerService::sanitizeLoanAmount
     */
    public function testSanitizeLoanAmount(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->sanitizer->sanitizeLoanAmount($input));
    }

    public static function sanitizeErrorsProvider(): array
    {
        return [
            [['nonexistent_key' => 'value'], ['nonexistent_key']],
            [['email' => null], ['another_key']],
            [[], ['email']],
            [['some key'], []],
            [[], []]
        ];
    }

    /**
     * @dataProvider sanitizeErrorsProvider
     *
     * @covers \App\Service\FormSanitizerService::sanitize
     * @covers \App\Service\FormSanitizerService::getErrors
     */
    public function testErrorsForSanitize(array $input = [], $allowed_keys = []): void
    {
        $this->sanitizer->sanitize($input, $allowed_keys);
        $this->assertNotEmpty($this->sanitizer->getErrors());
    }

    public static function sanitizeSuccessProvider(): array
    {
        return [
            [['email' => 'email@test.com'], ['email']],
            [['phone' => '+37648383726'], ['phone']],
            [['full_name' => 'Name Surname'], ['full_name']],
            [['loan_amount' => '1234'], ['loan_amount']],
        ];
    }

    /**
     * @dataProvider sanitizeSuccessProvider
     *
     * @covers \App\Service\FormSanitizerService::sanitize
     * @covers \App\Service\FormSanitizerService::getErrors
     */
    public function testSuccessForSanitize(array $input = [], $allowed_keys = []): void
    {
        $this->sanitizer->sanitize($input, $allowed_keys);
        $this->assertEmpty($this->sanitizer->getErrors());
    }
}