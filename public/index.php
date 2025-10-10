<?php

require_once "../vendor/autoload.php";

header('application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "Form fields:\n";
    print_r($_POST);

    echo "\nUploaded files:\n";
    print_r($_FILES);
}






