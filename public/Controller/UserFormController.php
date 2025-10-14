<?php

declare(strict_types=1);

namespace App\Controller;

use App\HTTP\HTMLResponse;
use App\HTTP\JsonResponse;
use App\HTTP\Response;
use App\Model\DocumentInterface;
use App\Model\LoanInterface;
use App\Model\UserInterface;
use App\Repository\RepositoryInterface;
use App\Service\FileStorageServiceInterface;
use App\Service\SanitizerServiceInterface;
use App\Service\ValidatorServiceInterface;
use App\maps\InputMap;
use Throwable;

final readonly class UserFormController
{

    public function __construct(
        private SanitizerServiceInterface $sanitizer,
        private ValidatorServiceInterface $validator,
        private FileStorageServiceInterface $fileEncryptionStorageService,
        private LoanInterface $loan,
        private UserInterface $user,
        private DocumentInterface $document,
        private RepositoryInterface $repository
    ) {
    }

    public function handleSubmit(): Response
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return new HTMLResponse([
                '<h1>Welcome!</h1>',
                '<p>Please submit the form to register a new user with a loan and a document</p>',
                '<p>use POST method, a header: Content-Type: multipart/form-data;</p>',
                '<p>Fields: full_name, email, phone, loan_amount, file (image/jpeg, under 2MB)</p>'
            ], 200);
        }

        // Validation and sanitization services
        // If input do not have sanitization class matched, the then error is thrown too, to not bypass
        $data = $this->sanitizer->sanitizeInputs($_POST, [
            InputMap::FULL_NAME,
            InputMap::EMAIL,
            InputMap::PHONE,
            InputMap::LOAN_AMOUNT
        ]);
        if (!empty($this->sanitizer->getErrors())) {
            return new JsonResponse(['status' => 'error', 'errors' => $this->sanitizer->getErrors()], 200);
        }

        // If input do not have validation class matched, the error is thrown too, to not bypass validation
        $this->validator->validateInputs($data);
        $this->validator->validateFiles($_FILES, [InputMap::FILE_NAME]);
        $errors = $this->validator->getErrors();
        if (!empty($errors)) {
            return new JsonResponse(['status' => 'error', 'errors' => $errors], 200);
        }

        try {
            // Encryption and file saving
            $file_path = $this->fileEncryptionStorageService->store($_FILES['file']);

            // Build objects
            $this->loan->setAmount((float)$data['loan_amount']);

            $this->document->setName($file_path);

            $this->user->setFullName($data['full_name']);
            $this->user->setEmail($data['email']);
            $this->user->setPhoneNumber($data['phone']);
            $this->user->addLoan($this->loan);
            $this->user->addDocument($this->document);

            $user_id = $this->repository->save($this->user);
        } catch (Throwable $e) {
            // Would not expose $e->getMessage() in production, but for this demo it is useful
            // in a production scenario, would log the error to a file or monitoring system
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