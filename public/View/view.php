<?php

declare(strict_types=1);

namespace App\View;

http_response_code($this->getStatusCode());

foreach ($this->getHeaders() as $header => $value) {
    header("$header: $value");
}

echo $this->getBody();