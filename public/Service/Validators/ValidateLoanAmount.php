<?php

declare(strict_types=1);

namespace App\Service\Validators;

final class ValidateLoanAmount implements StringValidatorInterface
{

    private array $errors = [];

    public function validate(string $input): bool
    {
        if (!is_numeric($input) || (float)$input <= 0) {
            $this->errors[] = "Loan amount must be a positive number.";
            return false;
        }
        return true;
    }

    public function supports(string $key): bool
    {
        return strtolower($key) === "loan_amount";
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}