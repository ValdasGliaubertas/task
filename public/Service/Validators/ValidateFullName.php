<?php

declare(strict_types=1);

namespace App\Service\Validators;

final class ValidateFullName implements StringValidatorInterface
{

    private array $errors = [];

    public function validate(string $input): bool
    {
        if (strlen($input) < 3) {
            $this->errors[] = "Name must be at least 3 characters.";
            return false;
        }

        if (!str_contains($input, ' ')) {
            $this->errors[] = "Full name must contain at least first name and last name.";
            return false;
        }

        return true;
    }

    public function supports(string $key): bool
    {
        return strtolower($key) === 'full_name';
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}