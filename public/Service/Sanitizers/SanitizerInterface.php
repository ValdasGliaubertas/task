<?php

namespace App\Service\Sanitizers;

interface SanitizerInterface
{
    public function sanitize(string $input): string;

    public function supportedKeys(): array;

}