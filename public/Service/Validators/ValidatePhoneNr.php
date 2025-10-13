<?php

declare(strict_types=1);

namespace App\Service\Validators;

use App\maps\InputMap;

final class ValidatePhoneNr implements StringValidatorInterface
{

    private array $errors = [];

    public function validate(string $input): bool
    {
        // Simple phone validation (basic format)
        // Real life scenario would require a big lib of each country's phone formats
        // based on country code and number format rules either a 3rd party service.
        if (!preg_match('/^\+?\d{8,15}$/', $input)) {
            $this->errors[] = "Invalid phone number format.";
            return false;
        }
        return true;
    }

    public function supportedKeys(): array
    {
        return [InputMap::PHONE];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}