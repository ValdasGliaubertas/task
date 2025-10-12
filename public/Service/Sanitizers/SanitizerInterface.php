<?php

namespace App\Service\Sanitizers;

interface SanitizerInterface
{
    public function sanitize(string $input): string;

    public function supports(string $key): bool;

}