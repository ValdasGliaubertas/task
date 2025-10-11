<?php

declare(strict_types=1);

namespace App\Service;

class FormValidatorService implements ValidatorServiceInterface, FormValidatorInterface
{
    private array $errors = [];

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function validate(array $input, array $files): void
    {
        $errors = [];

        if ($this->validateFullName($input['full_name'])) {
            return;
        }

        if ($this->validateEmail($input['email'])) {
            return;
        }

        if ($this->validateMobilePhoneNr($input['phone'])) {
            return;
        }

        if ($this->validateLoanAmount($input['loan_amount'])) {
            return;
        }

        if ($this->validateFile($files, $errors)) {
            return;
        }

        $this->errors[] = "Input do not have validation definition.";
    }

    public function validateFullName($full_name): bool
    {
        if (strlen($full_name) < 3) {
            $this->errors[] = "Name must be at least 3 characters.";
            return false;
        }
        return true;
    }

    public function validateEmail($email): bool
    {
        // Additionally 3rd party email validation services can be used here
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
            $this->errors[] = "Invalid email address.";
            return false;
        }

        $mail_parts = explode('@', $email);
        $domain = $mail_parts[1] ?? '';
        if (empty($domain) || !checkdnsrr($domain)) {
            $this->errors[] = 'Email domain cannot receive mail';
            return false;
        }

        return true;
    }

    public function validateMobilePhoneNr($phone): bool
    {
        // Simple phone validation (basic format)
        // Real life scenario would require a big lib of each country's phone formats
        // based on country code and number format rules either a 3rd party service.
        if (!preg_match('/^\+?\d{8,15}$/', $phone)) {
            $this->errors[] = "Invalid phone number format.";
            return false;
        }
        return true;
    }

    public function validateLoanAmount($loan_amount): bool
    {
        if (!is_numeric($loan_amount) || (int)$loan_amount <= 0) {
            $this->errors[] = "Loan amount must be a positive number.";
            return false;
        }
        return true;
    }

    public function validateFile(array $files, array &$errors): bool
    {
        $file = $files['file'] ?? null;
        if (empty($file['tmp_name'])) {
            $this->errors[] = "File is required.";
            return false;
        }

        $max_size = 2 * 1024 * 1024;
        $allowed_mime = ['image/jpeg'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed with error code " . $file['error'];
            return false;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed_mime)) {
            $errors[] = "Only JPEG files are allowed.";
            return false;
        }

        // Same limitations could be applied through php.ini settings: upload_max_filesize, post_max_size
        if ($file['size'] > $max_size) {
            $errors[] = "File exceeds maximum size of 2MB.";
            return false;
        }
        return true;
    }
}