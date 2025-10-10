<?php

namespace App\Service;

interface FormValidatorInterface
{
    public function validate(array $input, array $files): array;

}