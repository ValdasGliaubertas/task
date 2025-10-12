<?php

declare(strict_types=1);

namespace App\Service\Sanitizers;

final class SanitizeEmail implements SanitizerInterface
{

    public function supports(string $key): bool
    {
        return strtolower($key) === 'email';
    }

    public function sanitize(string $input): string
    {
        return filter_var($input, FILTER_SANITIZE_EMAIL);
    }

}