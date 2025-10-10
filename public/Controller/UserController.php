<?php

namespace App\Controller;

use App\Model\Document;
use App\Model\DocumentInterface;
use App\Model\EnvConfig;
use App\Model\EnvConfigInterface;
use App\Model\Loan;
use App\Model\LoanInterface;
use App\Model\PGSQLUserRepository;
use App\Model\User;
use App\Model\UserInterface;
use App\Model\UserRepositoryInterface;
use App\Service\EncryptedFileStorageServiceInterface;
use App\Service\FormValidatorInterface;
use Throwable;

class UserController
{

    private FormValidatorInterface $validator;

    private EncryptedFileStorageServiceInterface $encryptedFileStorageService;

    private LoanInterface $loan;

    private UserInterface $user;

    private DocumentInterface $document;

    private UserRepositoryInterface $repository;

    public function __construct(
      FormValidatorInterface $validator,
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

    public function handleSubmit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        // Validation and sanitization service
        [$data, $errors] = $this->validator->validate($_POST, $_FILES);

        if (!empty($errors)) {
            include __DIR__ . '/../View/json_response.php';
            return;
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
            http_response_code(500);
            echo json_encode([
              'status' => 'error',
              'message' => 'Failed to save user data: ' . $e->getMessage(),
            ]);
            return;
        }

        $response = [
          'status' => 'success',
          'data' => ['user_id' => $user_id]
        ];

        include __DIR__ . '/../View/json_response.php';
    }
}