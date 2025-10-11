<?php

declare(strict_types=1);

namespace App\Controller;

class FrontController
{
    public function render(array $response): void
    {
        include __DIR__ . '/../View/json_response.php';
    }

}