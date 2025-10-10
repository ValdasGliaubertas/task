<?php

namespace App\Controller;

use App\Model\Document;
use App\Model\EnvConfig;
use App\Model\Loan;
use App\Model\PGSQLUserRepository;
use App\Model\User;
use App\Service\EncryptedFileStorageServiceInterface;
use App\Service\FormValidatorInterface;
use Throwable;

class UserController
{

    private FormValidatorInterface $validator;

    private EncryptedFileStorageServiceInterface $encryptedFileStorageService;

    public function __construct(
      FormValidatorInterface $validator,
      EncryptedFileStorageServiceInterface $encryptedFileStorageService
    ) {
        $this->validator = $validator;
        $this->encryptedFileStorageService = $encryptedFileStorageService;
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
        $loan = new Loan();
        $loan->setAmount((float)$data['loan_amount']);

        $document = new Document();
        $document->setName($file_path);

        $user = new User();
        $user->setFullName($data['full_name']);
        $user->setEmail($data['email']);
        $user->setPhoneNumber($data['phone']);
        $user->addLoan($loan);
        $user->addDocument($document);

        try {
            $env = new EnvConfig();
            $repo = new PGSQLUserRepository($env);
            $user_id = $repo->save($user);
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