<?php

declare(strict_types=1);

namespace App\Controller;

use App\HTTP\HTTPResponse;
use App\HTTP\JsonResponse;
use App\HTTP\Response;
use App\Model\DocumentInterface;
use App\Model\LoanInterface;
use App\Model\UserInterface;
use App\Service\FileStorageServiceInterface;
use App\Service\RepositoryInterface;
use App\Service\ValidatorServiceInterface;
use Throwable;

class UserController
{

    private ValidatorServiceInterface $validator;

    private FileStorageServiceInterface $fileEncryptionStorageService;

    private LoanInterface $loan;

    private UserInterface $user;

    private DocumentInterface $document;

    private RepositoryInterface $repository;

    public function __construct(
      ValidatorServiceInterface $validator,
      FileStorageServiceInterface $fileEncryptionStorageService,
      LoanInterface $loan,
      UserInterface $user,
      DocumentInterface $document,
      RepositoryInterface $repository
    ) {
        $this->validator = $validator;
        $this->fileEncryptionStorageService = $fileEncryptionStorageService;
        $this->loan = $loan;
        $this->user = $user;
        $this->document = $document;
        $this->repository = $repository;
    }

    public function handleSubmit(): Response
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return new HTTPResponse([
              '<h1>Welcome!</h1>',
              '<p>Please submit the form to register a new user with a loan and a document</p>',
              '<p>use POST method, a header: Content-Type: multipart/form-data;</p>',
              '<p>Fields: full_name, email, phone, loan_amount, file (image/jpeg, under 2MB)</p>'
            ], 200);
        }

        // Validation and sanitization service
        [$data, $errors] = $this->validator->validate($_POST, $_FILES);

        if (!empty($errors)) {
            return new JsonResponse(['status' => 'error', 'errors' => $errors], 200);
        }

        // Encryption and file saving
        $file_path = $this->fileEncryptionStorageService->store($_FILES['file']);

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
            return new JsonResponse([
              'status' => 'error',
              'message' => 'Failed to save user data: ' . $e->getMessage()
            ], 500);
        }

        return new JsonResponse([
          'status' => 'success',
          'data' => ['user_id' => $user_id]
        ], 200);
    }
}