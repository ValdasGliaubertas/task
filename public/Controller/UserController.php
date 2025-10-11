<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\DocumentInterface;
use App\Model\LoanInterface;
use App\Model\UserInterface;
use App\Service\EncryptedFileStorageServiceInterface;
use App\Service\FormValidatorServiceInterface;
use App\Service\UserRepositoryInterface;
use Throwable;

class UserController
{

    private FormValidatorServiceInterface $validator;

    private EncryptedFileStorageServiceInterface $encryptedFileStorageService;

    private LoanInterface $loan;

    private UserInterface $user;

    private DocumentInterface $document;

    private UserRepositoryInterface $repository;

    public function __construct(
      FormValidatorServiceInterface $validator,
      EncryptedFileStorageServiceInterface $encryptedFileStorageService,
      LoanInterface $loan,
      UserInterface $user,
      DocumentInterface $document,
      UserRepositoryInterface $repository
    ) {
        $this->validator = $validator;
        $this->encryptedFileStorageService = $encryptedFileStorageService;
        $this->loan = $loan;
        $this->user = $user;
        $this->document = $document;
        $this->repository = $repository;
    }

    public function handleSubmit(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Responses should be handled by a front controller through Response object
            return ['status' => 'error', 'message' => 'Invalid request method'];
        }

        // Validation and sanitization service
        [$data, $errors] = $this->validator->validate($_POST, $_FILES);

        if (!empty($errors)) {
            return ['status' => 'error', 'errors' => $errors];
        }

        // Encryption and file saving
        $file_path = $this->encryptedFileStorageService->store($_FILES['file']);

        // Build domain objects
        $this->loan->setAmount((float)$data['loan_amount']);

        $this->document->setName($file_path);

        $this->user->setFullName($data['full_name']);
        $this->user->setEmail($data['email']);
        $this->user->setPhoneNumber($data['phone']);
        $this->user->addLoan($this->loan);
        $this->user->addDocument($this->document);

        try {
            $user_id = $this->repository->save($this->user);
        } catch (Throwable $e) {
            return [
              'status' => 'error',
              'message' => 'Failed to save user data: ' . $e->getMessage(),
            ];
        }

        return [
          'status' => 'success',
          'data' => ['user_id' => $user_id]
        ];
    }
}