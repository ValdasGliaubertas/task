<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Sanitizers\SanitizerInterface;

final class FormSanitizerService implements SanitizerServiceInterface
{

    private array $errors = [];

    /** @var array<string, SanitizerInterface> */
    private array $sanitizer_map = [];

    public function __construct(
        /** @var SanitizerInterface[] */
        private readonly iterable $sanitizers
    ) {
        $this->buildSanitizerMap();
    }

    private function buildSanitizerMap(): void
    {
        foreach ($this->sanitizers as $sanitizer) {
            foreach ($sanitizer->supportedKeys() as $key) {
                $this->sanitizer_map[$key] = $sanitizer;
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function sanitizeInputs(array $input, array $keys): array
    {
        $output = [];

        if (empty($keys)) {
            $this->errors[] = "No keys provided for sanitization.";
            return $output;
        }

        foreach ($keys as $key) {
            if (!isset($input[$key])) {
                $this->errors[] = "Missing input for key: $key";
                continue;
            }

            $sanitizer = $this->sanitizer_map[$key] ?? null;
            if ($sanitizer === null) {
                $this->errors[] = "No sanitizer found for key: $key";
                continue;
            }

            $output[$key] = $sanitizer->sanitize($input[$key]);
        }

        return $output;
    }

}