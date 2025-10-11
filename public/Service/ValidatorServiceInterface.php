<?php

declare(strict_types=1);

namespace App\Service;

interface ValidatorServiceInterface
{
    public function validate(array $input, array $files): void;

    public function getErrors(): array;

}