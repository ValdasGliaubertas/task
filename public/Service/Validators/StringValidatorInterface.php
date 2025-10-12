<?php

namespace App\Service\Validators;

interface StringValidatorInterface
{
    public function validate(string $input): bool;
}