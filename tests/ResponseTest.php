<?php

declare(strict_types=1);

use App\HTTP\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testDefaultConstructorValues(): void
    {
        $response = new Response();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
        $this->assertSame([], iterator_to_array($response->getHeaders()));
    }

    public function testCustomConstructorValues(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Custom' => 'HeaderValue'
        ];
        $body = '{"message": "ok"}';

        $response = new Response(201, $headers, $body);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame($body, $response->getBody());

        // getHeaders() returns an iterable — convert it to array to test
        $this->assertSame($headers, iterator_to_array($response->getHeaders()));
    }

    public function testGetHeadersIsGenerator(): void
    {
        $headers = ['A' => '1', 'B' => '2'];
        $response = new Response(200, $headers);

        $headersIterable = $response->getHeaders();

        $this->assertInstanceOf(\Traversable::class, $headersIterable, 'Headers must be iterable');
        $this->assertSame($headers, iterator_to_array($headersIterable));
    }

    public function testResponseIsImmutableFromOutside(): void
    {
        $headers = ['A' => '1'];
        $response = new Response(200, $headers);

        $headersIterable = $response->getHeaders();
        $arr = iterator_to_array($headersIterable);
        $arr['A'] = 'changed';

        // Verify that original response headers are not affected
        $this->assertSame(['A' => '1'], iterator_to_array($response->getHeaders()));
    }
}