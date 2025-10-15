<?php

declare(strict_types=1);

namespace Tests\Service\Validators;

use App\Maps\InputMap;
use App\Service\Validators\ValidateLoanAmount;
use PHPUnit\Framework\TestCase;

final class ValidateLoanAmountTest extends TestCase
{
    private ValidateLoanAmount $validator;

    protected function setUp(): void
    {
        $this->validator = new ValidateLoanAmount();
    }

    public function testValidPositiveLoanAmount(): void
    {
        $result = $this->validator->validate('100.50');

        $this->assertTrue($result, 'Expected valid numeric input to pass.');
        $this->assertSame([], $this->validator->getErrors(), 'No errors should be returned for valid input.');
    }

    public function testZeroOrNegativeLoanAmountFails(): void
    {
        $cases = ['0', '-10', '-0.01'];

        foreach ($cases as $case) {
            $validator = new ValidateLoanAmount(); // fresh per test
            $result = $validator->validate($case);

            $this->assertFalse($result, "Expected $case to fail validation.");
            $errors = $validator->getErrors();
            $this->assertContains('Loan amount must be a positive number.', $errors);
        }
    }

    public function testNonNumericLoanAmountFails(): void
    {
        $cases = ['abc', 'ten', '', ' ', '12abc'];

        foreach ($cases as $case) {
            $validator = new ValidateLoanAmount();
            $result = $validator->validate($case);

            $this->assertFalse($result, "Expected non-numeric input '$case' to fail.");
            $errors = $validator->getErrors();
            $this->assertContains('Loan amount must be a positive number.', $errors);
        }
    }

    public function testSupportedKeys(): void
    {
        $keys = $this->validator->supportedKeys();
        $this->assertContains(InputMap::LOAN_AMOUNT, $keys, 'Validator should support InputMap::LOAN_AMOUNT');
    }

    public function testErrorsPersistAfterInvalidInput(): void
    {
        $this->validator->validate('-100');
        $errors = $this->validator->getErrors();

        $this->assertCount(1, $errors);
        $this->assertSame('Loan amount must be a positive number.', $errors[0]);
    }
}
