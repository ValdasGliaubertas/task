<?php

declare(strict_types=1);

use App\Service\Validators\StringValidatorInterface;
use App\Service\Validators\ValidateLoanAmount;
use PHPUnit\Framework\TestCase;

final class ValidateLoanAmountTest extends TestCase
{

    private StringValidatorInterface $validator;

    public function setUp(): void
    {
        $this->validator = new ValidateLoanAmount();
    }

    /**
     * Test that a negative loan amount fails validation.
     */
    public function testNegativeLoanAmountFails(): void
    {
        $this->assertFalse($this->validator->validate('-100'));
        $errors = $this->validator->getErrors();
        $this->assertContains('Loan amount must be a positive number.', $errors);
    }

    /**
     * Test that a positive loan amount passes validation.
     * @todo: add more tests via provider for different valid and invalid inputs
     */
    public function testPositiveLoanAmountPass(): void
    {
        $this->assertTrue($this->validator->validate('100'));
        $errors = $this->validator->getErrors();
        $this->assertEmpty($errors);
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
        $this->assertSame(in_array($input, $this->validator->supportedKeys()), $expected);
    }
}