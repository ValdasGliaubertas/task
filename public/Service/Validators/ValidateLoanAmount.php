<?php

declare(strict_types=1);

namespace App\Service\Validators;

use App\Maps\InputMap;

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

    public function supportedKeys(): array
    {
        return [InputMap::LOAN_AMOUNT];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}