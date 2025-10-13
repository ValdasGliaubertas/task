<?php

declare(strict_types=1);

namespace App\Service\Sanitizers;

final class SanitizePhoneNr implements SanitizerInterface
{

    public function supportedKeys(): array
    {
        return ['phone'];
    }

    public function sanitize(string $input): string
    {
        return preg_replace('/[^0-9+\-]/', '', $input);
    }

}