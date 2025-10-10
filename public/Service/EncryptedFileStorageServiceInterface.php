<?php

declare(strict_types=1);

namespace App\Service;

interface EncryptedFileStorageServiceInterface
{
    public function store(array $file): string;
}