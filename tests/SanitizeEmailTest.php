<?php

declare(strict_types=1);

use App\Service\Sanitizers\SanitizeEmail;
use App\Service\Sanitizers\SanitizerInterface;
use PHPUnit\Framework\TestCase;

final class SanitizeEmailTest extends TestCase
{

    private SanitizerInterface $sanitizer;

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

    public function setUp(): void
    {
        $this->sanitizer = new SanitizeEmail();
    }

    /**
     * @dataProvider sanitizeEmailProvider
     */
    public function testSanitizeEmail(string $input, string $expected): void
    {
        $this->assertEquals($expected, $this->sanitizer->sanitize($input));
    }

    public function SupportsProvider(): array
    {
        return [
            ['email', true],
            ['EMAIL', false],
            ['Email', false],
            ['eMaIl', false],
            ['user_name', false],
            ['phone', false],
            ['address', false],
            ['name', false]
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