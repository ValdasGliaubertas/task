<?php

declare(strict_types=1);

use App\Service\Validators\StringValidatorInterface;
use App\Service\Validators\ValidatePhoneNr;
use PHPUnit\Framework\TestCase;

final class ValidatePhoneNrTest extends TestCase
{

    private StringValidatorInterface $validator;

    public function setUp(): void
    {
        $this->validator = new ValidatePhoneNr();
    }

    public function phoneNrProvider(): array
    {
        return [
            ['+1234567890', true],
            ['+1 234 567 8900', false],
            ['+44 20 7946 0958', false],
            ['+91-9876543210', false],
            ['12345', false],
            ['phone123', false],
            ['+12(345)67890', false],
        ];
    }

    /**
     * Test that an invalid phone number fails validation.
     *
     * @dataProvider phoneNrProvider
     */
    public function testInvalidPhoneFails(string $input, bool $expected): void
    {
        $result = $this->validator->validate($input);
        $this->assertSame($expected, $result);

        if ($result !== $expected) {
            $errors = $this->validator->getErrors();
            $this->assertContains('Invalid phone number format.', $errors);
        }
    }

    public function SupportsProvider(): array
    {
        return [
            ['phone_', false],
            ['phone', true],
            ['phone_nr', false],
            ['PHONE', false],
            ['Phone', false]
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