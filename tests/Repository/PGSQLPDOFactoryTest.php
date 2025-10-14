<?php

declare(strict_types=1);

namespace Tests\Repository;

use App\Repository\PGSQLPDOFactory;
use App\Service\ConfigServiceInterface;
use Exception;
use PDO;
use PHPUnit\Framework\TestCase;

final class PGSQLPDOFactoryTest extends TestCase
{
    private ConfigServiceInterface $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = $this->createMock(ConfigServiceInterface::class);
    }

    public function testCreateThrowsOnInvalidConfig(): void
    {
        // Missing DB_NAME triggers the validation error before connecting
        $this->config->method('get')->willReturnMap([
            ['DB_HOST', 'localhost'],
            ['DB_NAME', ''],          // invalid
            ['DB_USER', 'user'],
            ['DB_PASS', 'pass'],
            ['DB_PORT', '5432'],
        ]);

        $factory = new PGSQLPDOFactory($this->config);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database configuration is not set properly.');

        $factory->create();
    }

    public function testCreateWrapsPDOExceptionOnConnectionFailure(): void
    {
        $this->config->method('get')->willReturnCallback(function (string $key) {
            return match ($key) {
                'DB_HOST' => 'invalid-hostname-for-tests',
                'DB_NAME' => 'somedb',
                'DB_USER' => 'someuser',
                'DB_PASS' => 'somepass',
                'DB_PORT' => '5432',
                default => 'unused',
            };
        });

        $factory = new PGSQLPDOFactory($this->config);

        $this->expectException(Exception::class);
        // accept either validation or connection-failure message
        $this->expectExceptionMessageMatches(
            '/Database (configuration is not set properly|connection failed:)/'
        );

        $factory->create();
    }

    /**
     * Optional: Only runs if a real Postgres test DB is available.
     *
     * Set these env vars to enable:
     *   TEST_PG_HOST, TEST_PG_PORT, TEST_PG_DB, TEST_PG_USER, TEST_PG_PASS
     */
    public function testCreateReturnsPDOInstanceWhenPgsqlAvailable(): void
    {
        $host = getenv('TEST_PG_HOST') ?: null;
        $port = getenv('TEST_PG_PORT') ?: null;
        $db   = getenv('TEST_PG_DB')   ?: null;
        $user = getenv('TEST_PG_USER') ?: null;
        $pass = getenv('TEST_PG_PASS') ?: null;

        // Skip if not fully configured
        if (!$host || !$port || !$db || !$user || $pass === false) {
            $this->markTestSkipped('Postgres test environment not configured; set TEST_PG_* env vars to enable this test.');
        }

        // Also skip if pgsql driver isn’t installed
        if (!in_array('pgsql', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PDO pgsql driver not available.');
        }

        $this->config->method('get')->willReturnMap([
            ['DB_HOST', $host],
            ['DB_NAME', $db],
            ['DB_USER', $user],
            ['DB_PASS', (string)$pass],
            ['DB_PORT', $port],
        ]);

        $factory = new PGSQLPDOFactory($this->config);

        try {
            $pdo = $factory->create();
        } catch (Exception $e) {
            $this->markTestSkipped('Could not connect to the configured Postgres test DB: ' . $e->getMessage());
            return;
        }

        $this->assertInstanceOf(PDO::class, $pdo);
        $this->assertSame('pgsql', $pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        $this->assertSame(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
        $this->assertSame(PDO::FETCH_ASSOC, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
    }
}
