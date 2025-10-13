<?php

declare(strict_types=1);

use App\Service\Sanitizers\SanitizerInterface;
use App\Service\Sanitizers\SanitizeString;
use PHPUnit\Framework\TestCase;

final class SanitizeStringTest extends TestCase
{

    private SanitizerInterface $sanitizer;

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

    public function setUp(): void
    {
        $this->sanitizer = new SanitizeString();
    }

    /**
     * @dataProvider sanitizeStringProvider
     */
    public function testSanitizeString(string $input, string $expected): void
    {
        $this->assertEquals($expected, $this->sanitizer->sanitize($input));
    }

    public function SupportsProvider(): array
    {
        return [
            [InputMap::FULL_NAME, true],
            ['FULL_NAME', false],
            ['LoanAmount', false],
            ['Loan_Amount', false],
            ['email', false],
            ['phone', false],
            ['address', false],
            ['Full_Name', false],
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