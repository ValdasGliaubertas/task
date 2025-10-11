<?php

declare(strict_types=1);

namespace App\Service;

interface ConfigServiceInterface
{
    public function get(string $key, mixed $default = null): mixed;
}