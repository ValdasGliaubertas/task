<?php

declare(strict_types=1);

namespace App\HTTP;

class Response implements ResponseInterface
{
    private int $statusCode;
    private iterable $headers;
    private string $body;

    public function __construct(int $statusCode = 200, iterable $headers = [], string $body = '')
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): iterable
    {
        Yield from $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

}