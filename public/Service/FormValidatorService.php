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
        /** @var StringValidatorInterface[] */
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

            if ($validator->validate($input[$key])) {
                continue;
            }
            $errors = $validator->getErrors();
            if (empty($errors)) {
                continue;
            }
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    public function validateFiles(array $files): void
    {
        foreach ($files as $file) {
            $processed = false;
            foreach ($this->file_validators as $validator) {
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