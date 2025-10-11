<?php

declare(strict_types=1);

namespace App\Service;

interface FormValidatorInterface
{
    public function validateEmail($email): bool;

    public function validateFullName($full_name): bool;

    public function validateMobilePhoneNr($phone): bool;

    public function validateLoanAmount($loan_amount): bool;

    public function validateFile(array $files, array &$errors): bool;
}