<?php

declare(strict_types=1);

namespace App\HTTP;

class JsonResponse extends Response
{
    public function __construct(array $data, int $status = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        parent::__construct($status, $headers, json_encode($data));
        include __DIR__ . '/../View/view.php';
        exit;
    }

}