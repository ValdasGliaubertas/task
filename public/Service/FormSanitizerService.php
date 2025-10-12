<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Sanitizers\SanitizerInterface;

final class FormSanitizerService implements SanitizerServiceInterface
{

    private array $errors = [];

    public function __construct(
        /** @var SanitizerInterface[] */
        private readonly array $sanitizers
    ) {
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function sanitize(array $input, array $keys): array
    {
        $output = [];
        if (empty($input)
            || empty($keys)
            || !in_array(key($input), $keys, true)) {
            $this->errors[] = 'Invalid input or keys';
            return $output;
        }

        foreach ($keys as $key) {
            $processed = false;
            foreach ($this->sanitizers as $sanitizer) {
                if (!$sanitizer->supports($key)) {
                    continue;
                }
                $output[$key] = $sanitizer->sanitize($input[$key]);
                $processed = true;
            }
            if (!$processed) {
                $this->errors[] = "No sanitizer found for key: $key";
            }
        }

        return $output;
    }

}