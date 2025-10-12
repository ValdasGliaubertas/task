<?php

declare(strict_types=1);

namespace App\HTTP;

use JetBrains\PhpStorm\NoReturn;

final class JsonResponse extends Response
{
    #[NoReturn]
    public function __construct(array $data, int $status = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        $headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
        $headers['Pragma'] = 'no-cache';
        $headers['Expires'] = '0';
        parent::__construct($status, $headers, json_encode($data, JSON_UNESCAPED_UNICODE));
        include __DIR__ . '/../View/view.php';
        exit;
    }

}