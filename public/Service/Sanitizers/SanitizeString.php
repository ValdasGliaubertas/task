<?php

declare(strict_types=1);

namespace App\Service\Sanitizers;

final class SanitizeString implements SanitizerInterface
{

    public function supportedKeys(): array
    {
        // Array of input names can be matched here if required
        return ['full_name'];
    }

    public function sanitize(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

}