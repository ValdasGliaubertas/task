<?php

namespace App\Service;

interface EncryptedFileStorageServiceInterface
{
    public function store(array $file): string;
}