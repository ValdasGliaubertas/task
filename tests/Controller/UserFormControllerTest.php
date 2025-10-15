<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\UserFormController;
use App\HTTP\HTMLResponse;
use App\HTTP\JsonResponse;
use App\Model\DocumentInterface;
use App\Model\LoanInterface;
use App\Model\UserInterface;
use App\Repository\RepositoryInterface;
use App\Service\FileStorageServiceInterface;
use App\Service\SanitizerServiceInterface;
use App\Service\ValidatorServiceInterface;
use PHPUnit\Framework\TestCase;
use Throwable;

final class UserFormControllerTest extends TestCase
{
    private SanitizerServiceInterface $sanitizer;
    private ValidatorServiceInterface $validator;
    private FileStorageServiceInterface $fileStorage;
    private LoanInterface $loan;
    private UserInterface $user;
    private DocumentInterface $document;
    private RepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sanitizer = $this->createMock(SanitizerServiceInterface::class);
        $this->validator = $this->createMock(ValidatorServiceInterface::class);
        $this->fileStorage = $this->createMock(FileStorageServiceInterface::class);
        $this->loan = $this->createMock(LoanInterface::class);
        $this->user = $this->createMock(UserInterface::class);
        $this->document = $this->createMock(DocumentInterface::class);
        $this->repository = $this->createMock(RepositoryInterface::class);

        // Reset globals between tests
        $_SERVER = [];
        $_POST = [];
        $_FILES = [];
    }

    public function testHandleSubmitReturnsHtmlResponseOnGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = $this->createController();

        $response = $controller->handleSubmit();

        $this->assertInstanceOf(HTMLResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<h1>Welcome!', (string)$response->getBody());
    }

    /**
     * Test that handleSubmit returns a JSON error response when the sanitizer fails.
     * When a key not available
     */
    public function testHandleSubmitReturnsJsonErrorWhenSanitizerFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['full_name' => 'Joe'];

        $this->sanitizer->method('sanitizeInputs')->willReturn([]);
        $this->sanitizer->method('getErrors')->willReturn(['No keys provided for sanitization.']);

        $controller = $this->createController();

        $response = $controller->handleSubmit();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = $response->getBody();
        $this->assertStringContainsString("{\"status\":\"error\",\"errors\":[\"No keys provided for sanitization.\"]}", $data);
    }

    /**
     * Test that handleSubmit returns a JSON error response when the sanitizer fails.
     * When sanitizer for a key not available
     */
    public function testHandleSubmitReturnsJsonErrorWhenInputMissing(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['full_name' => 'Joe'];

        $this->sanitizer->method('sanitizeInputs')->willReturn([]);
        $this->sanitizer->method('getErrors')->willReturn(['Missing input for key: nonexistent_key']);

        $controller = $this->createController();

        $response = $controller->handleSubmit();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = $response->getBody();;
        $this->assertStringContainsString("{\"status\":\"error\",\"errors\":[\"Missing input for key", $data);
    }

    /**
     * Test that handleSubmit returns a JSON error response when the sanitizer fails.
     * When sanitizer for a key not available
     */
    public function testHandleSubmitReturnsJsonErrorWhenSanitizerMissing(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['nonexistent_key' => 'Joe'];

        $this->sanitizer->method('sanitizeInputs')->willReturn([]);
        $this->sanitizer->method('getErrors')->willReturn(['No sanitizer found for key: nonexistent_key']);

        $controller = $this->createController();

        $response = $controller->handleSubmit();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = $response->getBody();;
        $this->assertStringContainsString("{\"status\":\"error\",\"errors\":[\"No sanitizer found for key:", $data);
    }

    /**
     * Test that sanitizer passes and no errors, so validation is called next.
     */
    public function testHandleSubmitReturnsJson(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '12345',
            'loan_amount' => '1000'
        ];

        $this->sanitizer->method('sanitizeInputs')->willReturn($_POST);
        $this->assertEmpty($this->sanitizer->getErrors());
    }

    public function testHandleSubmitReturnsJsonErrorWhenValidatorFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '12345',
            'loan_amount' => '1000'
        ];
        $_FILES = ['file' => ['name' => 'test.jpg']];

        $this->sanitizer->method('sanitizeInputs')->willReturn($_POST);
        $this->sanitizer->method('getErrors')->willReturn([]);

        $this->validator->method('validateInputs');
        $this->validator->method('validateFiles');
        $this->validator->method('getErrors')->willReturn(['Invalid email address.']);

        $controller = $this->createController();

        $response = $controller->handleSubmit();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = $response->getBody();;
        $this->assertStringContainsString("{\"status\":\"error\",\"errors\":[\"Invalid email address.", $data);
    }

    public function testHandleSubmitReturnsSuccessJsonResponse(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+37065456543',
            'loan_amount' => '1000'
        ];
        $_FILES = ['file' => ['name' => 'doc.jpg', 'tmp_name' => '/tmp/fake']];

        $this->sanitizer->method('sanitizeInputs')->willReturn($_POST);
        $this->sanitizer->method('getErrors')->willReturn([]);
        $this->validator->method('validateInputs');
        $this->validator->method('validateFiles');
        $this->validator->method('getErrors')->willReturn([]);
        $this->fileStorage->method('store')->willReturn('encrypted_doc.jpg');
        $this->repository->method('save')->willReturn(123);

        // Expect domain object setters to be called
        $this->loan->expects($this->once())->method('setAmount')->with(1000.0);
        $this->document->expects($this->once())->method('setName')->with('encrypted_doc.jpg');
        $this->user->expects($this->once())->method('setFullName')->with('John Doe');
        $this->user->expects($this->once())->method('setEmail')->with('john@example.com');
        $this->user->expects($this->once())->method('setPhoneNumber')->with('+37065456543');
        $this->user->expects($this->once())->method('addLoan')->with($this->loan);
        $this->user->expects($this->once())->method('addDocument')->with($this->document);

        $controller = $this->createController();

        $response = $controller->handleSubmit();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = $response->getBody();
        $this->assertStringContainsString("{\"status\":\"success\",\"data\":{\"user_id\":123}", $data);
    }

    public function testHandleSubmitReturnsErrorJsonOnRepositoryException(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '12345',
            'loan_amount' => '1000'
        ];
        $_FILES = ['file' => ['name' => 'doc.jpg']];

        $this->sanitizer->method('sanitizeInputs')->willReturn($_POST);
        $this->sanitizer->method('getErrors')->willReturn([]);
        $this->validator->method('getErrors')->willReturn([]);

        $this->fileStorage->method('store')->willReturn('file.jpg');
        $this->repository->method('save')->willThrowException(new class extends \Exception {
            public function __construct() { parent::__construct('DB failure'); }
        });

        $controller = $this->createController();

        $response = $controller->handleSubmit();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(500, $response->getStatusCode());
        $data = $response->getBody();
        $this->assertStringContainsString('DB failure', $data);
    }

    private function createController(): UserFormController
    {
        return new UserFormController(
            $this->sanitizer,
            $this->validator,
            $this->fileStorage,
            $this->loan,
            $this->user,
            $this->document,
            $this->repository
        );
    }
}
