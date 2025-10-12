<?php

declare(strict_types=1);

namespace App\HTTP;

interface ResponseInterface
{

    public function getStatusCode(): int;

    public function getHeaders(): iterable;

    public function getBody(): string;

}