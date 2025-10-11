<?php

declare(strict_types=1);

namespace App\Service;

interface FormValidatorServiceInterface
{
    public function validate(array $input, array $files): array;

}