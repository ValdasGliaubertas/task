<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Validators\FileValidatorInterface;
use App\Service\Validators\StringValidatorInterface;

final class FormValidatorService implements ValidatorServiceInterface
{
    private array $errors = [];

    public function __construct(
        /** @var StringValidatorInterface[] */
        private array $validators,
        /** @var FileValidatorInterface[] */
        private array $fileValidators = []
    ) {
    }

    public function validate(array $input, array $files): void
    {
        foreach ($input as $key => $value) {
            $processed = false;
            foreach ($this->validators as $validator) {
                if (!$validator->supports($key)) {
                    continue;
                }
                if (!$validator->validate($value)) {
                    $this->errors = array_merge($this->errors, $validator->getErrors());
                }
                $processed = true;
            }
            if (!$processed) {
                $this->errors[] = "Input '$key' do not have validation definition.";
            }
        }

        foreach ($files as $file) {
            $processed = false;
            foreach ($this->fileValidators as $validator) {
                // By file mime type or by file name
                if (!$validator->supports($file['name'])) {
                    continue;
                }
                if (!$validator->validate($file)) {
                    $this->errors = array_merge($this->errors, $validator->getErrors());
                }
                $processed = true;
            }
            if (!$processed) {
                $this->errors[] = "Input file '{$file['name']}' do not have validation definition.";
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}