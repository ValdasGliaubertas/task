<?php

declare(strict_types=1);

namespace App\Service\Sanitizers;

use App\maps\InputMap;

final class SanitizeString implements SanitizerInterface
{

    public function supportedKeys(): array
    {
        // Array of input names can be matched here if required
        return [InputMap::FULL_NAME];
    }

    public function sanitize(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

}