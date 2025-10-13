<?php

declare(strict_types=1);

namespace App\Service;

interface SanitizerServiceInterface
{
    public function sanitizeInputs(array $input, array $keys): array;

    public function getErrors(): array;

}