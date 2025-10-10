<?php

declare(strict_types=1);

require_once "../vendor/autoload.php";

header('application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize & validate text inputs
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $loan_amount = trim($_POST['loan_amount'] ?? '');
    $errors = [];

    // --- SANITIZATION ---
    $full_name = htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8');
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/[^0-9+\-]/', '', $phone); // Keep digits and +/-
    $loan_amount = preg_replace('/[^0-9]/', '', $loan_amount); // Keep only digits

    // --- VALIDATION ---
    if (empty($full_name) || strlen($full_name) < 3) {
        $errors[] = "Name must be at least 3 characters.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    if (!preg_match('/^\+?\d{8,15}$/', $phone)) {
        $errors[] = "Invalid phone number format.";
    }

    if (!is_numeric($loan_amount) || (int)$loan_amount <= 0) {
        $errors[] = "Loan amount must be a positive number.";
    }

    // --- FILE VALIDATION ---
    $maxSize = 2 * 1024 * 1024; // 2MB
    $allowedMime = ['image/jpeg'];

    if (empty($_FILES['file']['tmp_name'])) {
        $errors[] = "File is required.";
    } else {
        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed with error code " . $file['error'];
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowedMime)) {
                $errors[] = "Only JPEG files are allowed.";
            }

            if ($file['size'] > $maxSize) {
                $errors[] = "File exceeds maximum size of 2MB.";
            }
        }
    }

    // --- HANDLE ERRORS ---
    if (!empty($errors)) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit;
    }

    // --- SAFE FILE STORAGE ---
    // @todo: use env variable for upload dir
    $uploadDir = __DIR__ . '/../uploads';


    // Generate unique filename
    try {
        $newFileName = sprintf(
          '%s_%s.jpg',
          preg_replace('/[^a-zA-Z0-9]/', '_', pathinfo($file['name'], PATHINFO_FILENAME)),
          bin2hex(random_bytes(5))
        );
    } Catch (Exception $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to generate unique filename.']);
        exit;
    }

    $destination = $uploadDir . DIRECTORY_SEPARATOR . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
        exit;
    }


    $loan = new \Model\Loan();
    $loan->setAmount($loan_amount);

    $document = new \Model\Document();
    $document->setName($destination);

    $user = new \Model\User();
    $user->setFullName($full_name);
    $user->setEmail($email);
    $user->setPhoneNumber($phone);

    $user->addLoan($loan);
    $user->addDocument($document);

    // Save to Repository
    try {
        $envConfig = new \Model\EnvConfig();
        $userRepo = new \Model\PGSQLUserRepository($envConfig);
        $userId = $userRepo->save($user);
    } catch (Throwable $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save user data: ' . $e->getMessage()]);
        exit;
    }


    // --- SUCCESS RESPONSE ---
    header('Content-Type: application/json');
    echo json_encode([
      'status' => 'success',
      'message' => 'Form data validated successfully.',
      'data' => [
        'name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'loan_amount' => (int)$loan_amount,
        'uploaded_file' => basename($destination),
        'user_id' => $userId
      ]
    ]);
}





