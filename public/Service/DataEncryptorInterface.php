<?php

namespace App\Service;

interface DataEncryptorInterface
{
    public function encrypt(string $data): string;

    public function decrypt(string $data): string;
}