<?php

declare(strict_types=1);

namespace App\Service;

interface SanitizerServiceInterface
{
    public function sanitize(array $input, array $keys): array;

}