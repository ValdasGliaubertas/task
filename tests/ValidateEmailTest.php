<?php

declare(strict_types=1);

use App\Service\DnsCheckerInterface;
use App\Service\Validators\StringValidatorInterface;
use App\Service\Validators\ValidateEmail;
use PHPUnit\Framework\TestCase;

final class ValidateEmailTest extends TestCase
{

    private StringValidatorInterface $validator;

    public static function domainProvider(): array
    {
        return [
            'domain exists' => [true, true],
            'domain invalid' => [false, false],
        ];
    }

    public function setUp(): void
    {
        $mock = $this->createMock(DnsCheckerInterface::class);
        $mock->method('domainHasMxRecord')->willReturn(true);
        $this->validator = new ValidateEmail($mock);
    }

    public function emailProvider(): array
    {
        return [
            ['v @gmail.com', false],
            ['v++@gmail.com', true],
            ['v..@gmail.com', false],
            ['?5%^@gmail.com', true],
            ['@gmail.com', false],
            ['v@gmail', false],
            ['000@gmail.lt', true],
            ['+1@gmail.com', true],
            ['vg@gmail.com', true],
            ['test1@gmai@l.com', false],
            ['test8763@hotmail.ray', true],
        ];
    }

    /**
     * Test that a full name that is too long fails validation.
     *
     * @dataProvider emailProvider
     */
    public function testEmails(string $input, bool $expected): void
    {
        $this->validator->validate($input);
        $errors = $this->validator->getErrors();
        if ($expected) {
            $this->assertEmpty($errors);
        } else {
            $this->assertNotEmpty($errors);
        }
    }

    /**
     * @dataProvider domainProvider
     */
    public function testDomainValidation(bool $hasMxRecord, bool $expectedResult): void
    {
        $mock = $this->createMock(DnsCheckerInterface::class);
        $mock->method('domainHasMxRecord')->willReturn($hasMxRecord);
        $validator = new ValidateEmail($mock);
        $result = $validator->validate('john@example.com');

        $this->assertSame($expectedResult, $result);
    }

    public function SupportsProvider(): array
    {
        return [
            ['email', true],
            ['EMAIL', false],
            ['Email', false],
            ['eMaIl', false],
            ['username', false],
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
        $this->assertSame(in_array($input, $this->validator->supportedKeys()), $expected);
    }
}