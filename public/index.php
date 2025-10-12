<?php

declare(strict_types=1);

use App\Controller\UserFormController;
use App\Model\Document;
use App\Model\Loan;
use App\Model\User;
use App\Repository\PGSQLUserRepository;
use App\Service\DataEncryptionService;
use App\Service\EnvConfigService;
use App\Service\FileEncryptionStorageService;
use App\Service\FormSanitizerService;
use App\Service\FormValidatorService;
use App\Service\NativeDnsChecker;
use App\Service\Sanitizers\SanitizeEmail;
use App\Service\Sanitizers\SanitizeLoanAmount;
use App\Service\Sanitizers\SanitizePhoneNr;
use App\Service\Sanitizers\SanitizeString;
use App\Service\Validators\ValidateEmail;
use App\Service\Validators\ValidateFullName;
use App\Service\Validators\ValidateJPGFile;
use App\Service\Validators\validateLoanAmount;
use App\Service\Validators\validatePhoneNr;

require_once "../vendor/autoload.php";

// Initialize services and dependencies
$env = new EnvConfigService();
$repository = new PGSQLUserRepository($env);
$user = new User();
$document = new Document();
$loan = new Loan();
// Using strategy pattern for sanitization and validation
$form_sanitizer = new FormSanitizerService([
    new SanitizeLoanAmount(),
    new SanitizeEmail(),
    new SanitizePhoneNr(),
    new SanitizeString(),
]);
$dns_checker = new NativeDnsChecker();
$form_validator = new FormValidatorService([
    new ValidateEmail(new NativeDnsChecker()),
    new ValidateFullName(),
    new ValidatePhoneNr(),
    new validateLoanAmount(),
],
    [
        new ValidateJPGFile()
    ]
);
$data_encryption = new DataEncryptionService();
$file_encryption_service = new FileEncryptionStorageService($data_encryption);

$user_form_controller = new UserFormController(
    $form_sanitizer,
    $form_validator,
    $file_encryption_service,
    $loan,
    $user,
    $document,
    $repository
);
$user_form_controller->handleSubmit();






