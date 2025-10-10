<?php

declare(strict_types=1);

namespace App\Service;

interface EnvConfigInterface
{
    public function get(string $key, mixed $default = null): mixed;
}