<?php

declare(strict_types=1);

require_once "../vendor/autoload.php";

use App\Controller\UserFormController;
use App\Model\Document;
use App\Model\Loan;
use App\Model\User;
use App\Repository\PGSQLPDOFactory;
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

// PHP-DI package could be used for autowiring dependencies
// https://php-di.org/doc/autowiring.html
// But for this showcase, I manually wire dependencies and don't use external code packages.

// Initialize services and dependencies
$env = new EnvConfigService();
$pdo_factory = new PGSQLPDOFactory($env);
$user_repository = new PGSQLUserRepository($pdo_factory);
$user = new User();
$document = new Document();
$loan = new Loan();

$form_sanitizer = new FormSanitizerService([
    new SanitizeLoanAmount(),
    new SanitizeEmail(),
    new SanitizePhoneNr(),
    new SanitizeString(),
]);
$dns_checker = new NativeDnsChecker();
$form_validator = new FormValidatorService(
    // Text input validators
    [
        new ValidateEmail(new NativeDnsChecker()),
        new ValidateFullName(),
        new ValidatePhoneNr(),
        new validateLoanAmount(),
    ],
    // File validators
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
    $user_repository
);
$response = $user_form_controller->handleSubmit();
$response->send();
