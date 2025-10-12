<?php

declare(strict_types=1);

namespace App\Service\Validators;

final class ValidateJPGFile implements FileValidatorInterface
{

    private array $errors = [];

    public function validate(array $input): bool
    {
        if (empty($input['tmp_name'])) {
            $this->errors[] = "File is required.";
            return false;
        }

        $max_size = 2 * 1024 * 1024;
        $allowed_mime = ['image/jpeg'];

        if ($input['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = "File upload failed with error code " . $input['error'];
            return false;
        }

        $mime = mime_content_type($input['tmp_name']);
        if (!in_array($mime, $allowed_mime)) {
            $this->errors[] = "Only JPEG files are allowed.";
            return false;
        }

        // Same limitations could be applied through php.ini settings: upload_max_filesize, post_max_size
        if ($input['size'] > $max_size) {
            $this->errors[] = "File exceeds maximum size of 2MB.";
            return false;
        }
        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function supports(string $key): bool
    {
        return str_ends_with(strtolower($key), ".jpg") || str_ends_with($key, ".jpeg");
    }
}