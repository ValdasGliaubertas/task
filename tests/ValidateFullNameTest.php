<?php

declare(strict_types=1);

use App\maps\InputMap;
use App\Service\Validators\StringValidatorInterface;
use App\Service\Validators\ValidateFullName;
use PHPUnit\Framework\TestCase;

final class ValidateFullNameTest extends TestCase
{

    private StringValidatorInterface $validator;

    public function setUp(): void
    {
        $this->validator = new ValidateFullName();
    }


    /**
     * Test that a too short full name fails validation.
     */
    public function testFullNameTooShortAndFails(): void
    {
        $this->assertFalse($this->validator->validate('Jo'));
        $errors = $this->validator->getErrors();
        $this->assertContains('Name must be at least 3 characters.', $errors);
    }

    public function testFullNameNotProvidedAndFails(): void
    {
        $this->assertFalse($this->validator->validate('Jonas'));
        $errors = $this->validator->getErrors();
        $this->assertContains('Full name must contain at least first name and last name.', $errors);
    }

    public function testFullNamePass(): void
    {
        $this->assertTrue($this->validator->validate('Jonas G'));
        $errors = $this->validator->getErrors();
        $this->assertEmpty($errors);
    }

    public function SupportsProvider(): array
    {
        return [
            ['full+name', false],
            [InputMap::FULL_NAME, true],
            ['FullName', false],
            ['Full_Name', false],
        ];
    }


    /**
     * @dataProvider SupportsProvider
     */
    function testSupports(string $input, bool $expected): void
    {
        $this->assertSame(in_array($input, $this->validator->supportedKeys()), $expected);
    }
}