<?php

declare(strict_types=1);

namespace App\Service\Validators;

use App\Maps\InputMap;

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

    public function supportedKeys(): array
    {
        return [InputMap::FULL_NAME];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}