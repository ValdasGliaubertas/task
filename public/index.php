<?php

declare(strict_types=1);

use App\Controller\UserController;
use App\Model\Document;
use App\Model\EnvConfig;
use App\Model\Loan;
use App\Model\PGSQLUserRepository;
use App\Model\User;
use App\Service\DataEncryptor;
use App\Service\EncryptedFileStorageService;
use App\Service\FormValidator;

require_once "../vendor/autoload.php";

header('application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
$env = new EnvConfig();
$repository = new PGSQLUserRepository($env);
$user = new User();
$document = new Document();
$loan = new Loan();
$form_validator = new FormValidator();
$data_encryptor = new DataEncryptor();
$encryptor_service = new EncryptedFileStorageService($data_encryptor);
$controller = new UserController($form_validator, $encryptor_service, $loan, $user, $document, $repository);
$controller->handleSubmit();






