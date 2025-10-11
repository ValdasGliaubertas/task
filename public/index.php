<?php

declare(strict_types=1);

use App\Controller\UserController;
use App\Model\Document;
use App\Model\Loan;
use App\Model\User;
use App\Service\DataEncryptorService;
use App\Service\EncryptedFileStorageService;
use App\Service\EnvConfigService;
use App\Service\FormValidatorService;
use App\Service\PGSQLUserRepository;

require_once "../vendor/autoload.php";

header('application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$env = new EnvConfigService();
$repository = new PGSQLUserRepository($env);
$user = new User();
$document = new Document();
$loan = new Loan();
$form_validator = new FormValidatorService();
$data_encryptor = new DataEncryptorService();
$encryptor_service = new EncryptedFileStorageService($data_encryptor);
$controller = new UserController($form_validator, $encryptor_service, $loan, $user, $document, $repository);
$controller->handleSubmit();






