<?php

declare(strict_types=1);

use App\Service\EnvConfigService;
use PHPUnit\Framework\TestCase;

final class EnvConfigServiceTest extends TestCase
{
    private string $tempEnvFile;

    protected function setUp(): void
    {
        // Create a temporary .env file in a test directory
        $this->tempEnvFile = sys_get_temp_dir() . '/.env_test_' . uniqid();
        file_put_contents($this->tempEnvFile, <<<ENV
# Comment line
APP_ENV=testing
DB_HOST=localhost
DB_USER="root"
DB_PASS='secret'
EMPTY_KEY=
INVALID_LINE
ENV);

        // Reset static cache before each test
        $ref = new ReflectionClass(EnvConfigService::class);

        // --- Handle static $cache property ---
        $cacheProp = $ref->getProperty('cache');
        // In PHP 8.3+, we must always pass two arguments
        $cacheProp->setValue(null, null);

        // --- Handle static or instance $envPath property ---
        $pathProp = $ref->getProperty('envPath');

        // If envPath is static, use null; otherwise, set it on an instance
        if ($pathProp->isStatic()) {
            $pathProp->setValue(null, $this->tempEnvFile);
        } else {
            // Create an instance only if needed
            $instance = new EnvConfigService();
            $pathProp->setValue($instance, $this->tempEnvFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempEnvFile)) {
            unlink($this->tempEnvFile);
        }
    }

    public function testGetReturnsValueFromEnv(): void
    {
        $service = new EnvConfigService();
        $this->assertSame('testing', $service->get('APP_ENV'));
        $this->assertSame('localhost', $service->get('DB_HOST'));
        $this->assertSame('root', $service->get('DB_USER'));
        $this->assertSame('secret', $service->get('DB_PASS'));
    }

    public function testGetReturnsDefaultForMissingKey(): void
    {
        $service = new EnvConfigService();
        $this->assertSame('defaultValue', $service->get('NON_EXISTENT', 'defaultValue'));
    }

    public function testThrowsExceptionIfEnvFileMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No .env file found');

        // Point to non-existent file
        $ref = new ReflectionClass(EnvConfigService::class);
        $pathProp = $ref->getProperty('envPath');

        if ($pathProp->isStatic()) {
            $pathProp->setValue(null, '/tmp/nonexistent_' . uniqid());
        } else {
            $instance = new EnvConfigService();
            $pathProp->setValue($instance, '/tmp/nonexistent_' . uniqid());
        }

        $service = new EnvConfigService();
        $service->get('ANY');
    }

    public function testIgnoresCommentsAndInvalidLines(): void
    {
        $service = new EnvConfigService();
        $result = $service->get('INVALID_LINE');
        $this->assertNull($result);
    }

    public function testStaticCachePreventsReload(): void
    {
        $service = new EnvConfigService();
        $firstValue = $service->get('APP_ENV');

        // Change file content — it should NOT reload because cache already set
        file_put_contents($this->tempEnvFile, "APP_ENV=changed");
        $secondValue = $service->get('APP_ENV');

        $this->assertSame($firstValue, $secondValue, 'Cache should prevent reloading env file');
    }
}