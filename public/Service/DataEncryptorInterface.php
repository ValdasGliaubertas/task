<?php

declare(strict_types=1);

namespace App\Service;

interface DataEncryptorInterface
{
    public function encrypt(string $data): string;

    public function decrypt(string $data): string;
}