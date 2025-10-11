<?php

declare(strict_types=1);

namespace App\Service;

interface FormSanitizerInterface
{
    public function getErrors(): array;

    public function sanitizeString(string $input): string;

    public function sanitizeEmail(string $email): string;

    public function sanitizeMobilePhoneNr(string $phone): string;

    public function sanitizeLoanAmount(string $loan_amount): string;

}