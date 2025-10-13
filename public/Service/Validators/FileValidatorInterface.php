<?php

namespace App\Service\Validators;

interface FileValidatorInterface
{
    public function validate(array $input): bool;

    public function supports(string $key): bool;

    public function getErrors(): array;
}