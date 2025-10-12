<?php

namespace App\Service\Validators;

interface FileValidatorInterface
{
    public function validate(array $input): bool;
}