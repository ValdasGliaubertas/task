<?php

declare(strict_types=1);

namespace App\Service;

class FormValidator implements FormValidatorInterface
{
    public function validate(array $input, array $files): array
    {
        $errors = [];

        $data['full_name'] = htmlspecialchars(trim($input['full_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $data['email'] = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $data['phone'] = preg_replace('/[^0-9+\-]/', '', $input['phone'] ?? '');
        $data['loan_amount'] = preg_replace('/[^0-9]/', '', $input['loan_amount'] ?? '');

        // Text validation
        if (strlen($data['full_name']) < 3) {
            $errors[] = "Name must be at least 3 characters.";
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }

        if (!preg_match('/^\+?\d{8,15}$/', $data['phone'])) {
            $errors[] = "Invalid phone number format.";
        }

        if (!is_numeric($data['loan_amount']) || (int)$data['loan_amount'] <= 0) {
            $errors[] = "Loan amount must be a positive number.";
        }

        // File validation
        $file = $files['file'] ?? null;
        if (empty($file['tmp_name'])) {
            $errors[] = "File is required.";
        } else {
            $this->validateFile($file, $errors);
        }

        return [$data, $errors];
    }

    private function validateFile(array $file, array &$errors): void
    {
        $max_size = 2 * 1024 * 1024;
        $allowed_mime = ['image/jpeg'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed with error code " . $file['error'];
            return;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed_mime)) {
            $errors[] = "Only JPEG files are allowed.";
        }

        if ($file['size'] > $max_size) {
            $errors[] = "File exceeds maximum size of 2MB.";
        }
    }
}