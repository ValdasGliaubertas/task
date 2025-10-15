<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

final class UserFormIntegrationTest extends TestCase
{
    // Port from inside the docker will be 80, from outside 7070, check docker-compose.yml
    private string $baseUrl = 'http://localhost:80/index.php';

    public function testFullUserFormSubmission(): void
    {
        // Prepare form fields
        $fields = [
            'full_name' => 'John Example',
            'email' => 'john22@example.com',
            'phone' => '+371032567120',
            'loan_amount' => '10.50',
        ];

        // Prepare file to upload
        $filePath = __DIR__ . '/passport.jpg';
        $this->assertFileExists($filePath, 'Test file passport.jpg must exist in tests/assets');

        $boundary = 'WebForm';
        $body = $this->buildMultipartBody($fields, $filePath, $boundary);

        // Send real HTTP POST request
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: multipart/form-data; boundary={$boundary}"
            ],
            CURLOPT_POSTFIELDS => $body,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $this->assertEmpty($err, "Curl error: $err");
        $this->assertNotFalse($response, 'Empty response from server');

        if ($httpCode == 200) {
            $this->assertSame(200, $httpCode, "Expected 200 OK but got $httpCode");

            $data = json_decode($response, true);
            $this->assertIsArray($data, 'Response must be JSON');
            $this->assertSame('success', $data['status'] ?? null);
            $this->assertArrayHasKey('data', $data);
            $this->assertArrayHasKey('user_id', $data['data']);
            $this->assertIsInt($data['data']['user_id']);
        }
        elseif ($httpCode == 500) {
            $this->assertSame(500, $httpCode, "Server error 500. Response: $response");
            $data = json_decode($response, true);
            $this->assertIsArray($data, 'Response must be JSON');
            $this->assertSame('error', $data['status'] ?? null);
            $this->assertSame('Failed to save user data: User already exists.', $data['message'] ?? null);
        }
    }

    /**
     * Builds a multipart/form-data body.
     */
    private function buildMultipartBody(array $fields, string $filePath, string $boundary): string
    {
        $body = '';

        foreach ($fields as $name => $value) {
            $body .= "--$boundary\r\n";
            $body .= "Content-Disposition: form-data; name=\"$name\"\r\n\r\n";
            $body .= "$value\r\n";
        }

        $fileName = basename($filePath);
        $fileData = file_get_contents($filePath);
        $mimeType = 'image/jpeg';

        $body .= "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"file\"; filename=\"$fileName\"\r\n";
        $body .= "Content-Type: $mimeType\r\n\r\n";
        $body .= $fileData . "\r\n";
        $body .= "--$boundary--\r\n";

        return $body;
    }
}
