<?php

declare(strict_types=1);

namespace App\HTTP;

use JetBrains\PhpStorm\NoReturn;

final class HTMLResponse extends Response
{
    #[NoReturn]
    public function __construct(array $data, int $status = 200, iterable $headers = [])
    {
        $headers['Content-Type'] = 'text/html; charset=UTF-8';
        parent::__construct($status, $headers, implode('', $data));
        include __DIR__ . '/../View/view.php';
        exit;
    }

}