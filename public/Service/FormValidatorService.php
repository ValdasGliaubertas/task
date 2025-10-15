<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Validators\FileValidatorInterface;
use App\Service\Validators\StringValidatorInterface;

final class FormValidatorService implements ValidatorServiceInterface
{
    private array $errors = [];

    /** @var array<string, StringValidatorInterface> */
    private array $validator_map = [];

    public function __construct(
        /** @var StringValidatorInterface[]|FileValidatorInterface[] */
        private readonly iterable $validators,
        /** @var FileValidatorInterface[] */
        private readonly iterable $file_validators = []
    ) {
        $this->buildValidatorMap();
    }

    private function buildValidatorMap(): void
    {
        foreach ($this->validators as $validator) {
            foreach ($validator->supportedKeys() as $key) {
                $this->validator_map[$key] = $validator;
            }
        }
    }

    public function validateInputs(array $input): void
    {
        foreach ($input as $key => $value) {
            $validator = $this->validator_map[$key] ?? null;
            if ($validator === null) {
                $this->errors[] = "No validator found for key: $key";
                continue;
            }

            if ($validator->validate($value)) {
                continue;
            }
            $errors = $validator->getErrors();
            if (empty($errors)) {
                continue;
            }
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    public function validateFiles(array $files, array $keys): void
    {
        if (empty($keys)) {
            $this->errors[] = "Expected name for file validation is missing.";
            return;
        }

        if (empty($files)) {
            $this->errors[] = "No files uploaded.";
            return;
        }

        foreach ($keys as $file_name) {
            if (!isset($files[$file_name])) {
                $this->errors[] = "File input '$file_name' is missing.";
                continue;
            }
            $file = $files[$file_name];

            $processed = false;
            foreach ($this->file_validators as $validator) {
                // By file name suffix we determine which validator to use
                if (!$validator->supports($file['name'])) {
                    continue;
                }
                if (!$validator->validate($file)) {
                    $this->errors = array_merge($this->errors, $validator->getErrors());
                }
                $processed = true;
            }
            if (!$processed) {
                $this->errors[] = "Input file '{$file['name']}' do not have validation to be processed.";
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}