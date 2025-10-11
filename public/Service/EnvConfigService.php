<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Secure configuration loader for PHP applications.
 *
 * Features:
 *  - Loads environment variables from .env file
 *  - Caches results in memory
 */
class EnvConfigService implements ConfigServiceInterface
{
    private static ?array $cache = null;
    private static string $envPath = __DIR__ . '/../../.env';

    /**
     * Get a configuration value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        self::load();
        return self::$cache[$key] ?? $default;
    }

    /**
     * Load and parse .env file
     */
    private function load(): void
    {
        // check if already loaded
        if (self::$cache !== null) {
            return;
        }

        $envContent = null;

        if (file_exists(self::$envPath)) {
            $envContent = file_get_contents(self::$envPath);
        } else {
            throw new \RuntimeException('No .env file found');
        }

        self::$cache = $this->parseEnvContent($envContent);
    }

    /**
     * Parse env content (string → associative array)
     */
    private function parseEnvContent(string $envContent): array
    {
        $data = [];
        $lines = explode("\n", $envContent);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            $data[$key] = $value;
        }

        return $data;
    }
}