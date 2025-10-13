<?php

declare(strict_types=1);

namespace App\Service\Sanitizers;

use App\maps\InputMap;

final class SanitizeLoanAmount implements SanitizerInterface
{

    public function sanitize(string $input): string
    {
        // Remove everything except digits and dots
        $value = preg_replace('/[^0-9.]/', '', $input);

        // Keep only the first dot
        $parts = explode('.', $value, 2);
        $value = $parts[0] . (isset($parts[1]) ? '.' . preg_replace('/\./', '', $parts[1]) : '');

        // trim leading zeros, e.g., "0001430.43" -> "1430.43"
        $value = ltrim($value, '0');
        if ($value === '' || $value[0] === '.') {
            $value = '0' . $value;
        }
        return $value;
    }

    public function supportedKeys(): array
    {
        return [InputMap::LOAN_AMOUNT];
    }
}