<?php

declare(strict_types=1);

use App\Controller\UserFormController;
use App\Model\Document;
use App\Model\Loan;
use App\Model\User;
use App\Service\DataEncryptionService;
use App\Service\EnvConfigService;
use App\Service\FileEncryptionStorageService;
use App\Service\FormSanitizerService;
use App\Service\FormValidatorService;
use App\Service\PGSQLUserRepository;

require_once "../vendor/autoload.php";

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Initialize services and dependencies
$env = new EnvConfigService();
$repository = new PGSQLUserRepository($env);
$user = new User();
$document = new Document();
$loan = new Loan();
$form_sanitizer = new FormSanitizerService();
$form_validator = new FormValidatorService();
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






