<?php

declare(strict_types=1);

namespace App\Service;

class FormSanitizerService implements SanitizerServiceInterface, FormSanitizerInterface
{

    private array $errors = [];

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function sanitize(array $input, array $keys): array
    {
        $output = [];
        if (empty($input)
            || empty($keys)
            || !in_array(key($input), $keys, true)) {
            $this->errors[] = 'Invalid input or keys';
            return $output;
        }

        foreach ($keys as $key) {
            switch ($key) {
                case 'full_name':
                    $output['full_name'] = $this->sanitizeString($input['full_name'] ?? '');
                    break;
                case 'email':
                    $output['email'] = $this->sanitizeEmail($input['email'] ?? '');
                    break;
                case 'phone':
                    $output['phone'] = $this->sanitizeMobilePhoneNr($input['phone'] ?? '');
                    break;
                case 'loan_amount':
                    $output['loan_amount'] = $this->sanitizeLoanAmount($input['loan_amount'] ?? '');
                    break;
                default:
                    $this->errors[] = "Unknown field: $key";
                    break;
            }
        }

        return $output;
    }

    public function sanitizeString(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    public function sanitizeEmail(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    public function sanitizeMobilePhoneNr(string $phone): string
    {
        return preg_replace('/[^0-9+\-]/', '', $phone);
    }

    public function sanitizeLoanAmount(string $loan_amount): string
    {
        // Remove everything except digits and dots
        $value = preg_replace('/[^0-9.]/', '', $loan_amount);

        // Keep only the first dot
        $parts = explode('.', $value, 2);
        $value = $parts[0] . (isset($parts[1]) ? '.' . preg_replace('/\./', '', $parts[1]) : '');

        // trim leading zeros, e.g., "0001430.43" -> "1430.43"
        $value = ltrim($value, '0');
        if ($value === '' || $value[0] === '.') {
            $value = '0' . $value;
        }
        return $value;
    }

}