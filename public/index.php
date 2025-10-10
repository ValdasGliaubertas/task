<?php

declare(strict_types=1);

use App\Controller\UserController;
use App\Service\DataEncryptor;
use App\Service\EncryptedFileStorageService;
use App\Service\FormValidator;

require_once "../vendor/autoload.php";

header('application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$form_validator = new FormValidator();
$data_encryptor = new DataEncryptor();
$encryptor_service = new EncryptedFileStorageService($data_encryptor);
$controller = new UserController($form_validator, $encryptor_service);
$controller->handleSubmit();






