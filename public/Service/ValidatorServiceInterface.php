<?php

declare(strict_types=1);

namespace App\Service;

interface ValidatorServiceInterface
{
    public function validateInputs(array $input): void;

    public function validateFiles(array $files): void;

    public function getErrors(): array;

}