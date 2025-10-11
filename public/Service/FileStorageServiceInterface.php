<?php

declare(strict_types=1);

namespace App\Service;

interface FileStorageServiceInterface
{
    public function store(array $file): string;
}