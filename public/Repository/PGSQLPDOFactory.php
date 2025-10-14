<?php

namespace App\Repository;

use App\Service\ConfigServiceInterface;
use Exception;
use PDO;
use PDOException;

final readonly class PGSQLPDOFactory implements PDOFactoryInterface
{
    public function __construct(private ConfigServiceInterface $config)
    {
    }

    /**
     * @throws Exception
     */
    public function create(): PDO
    {
        $dbHost = $this->config->get('DB_HOST');
        $dbName = $this->config->get('DB_NAME');
        $dbUser = $this->config->get('DB_USER');
        $dbPass = $this->config->get('DB_PASS');
        $dbPort = $this->config->get('DB_PORT');

        if (
            empty($dbPort) || !is_numeric($dbPort) ||
            empty($dbName) || empty($dbUser) || empty($dbPass)
        ) {
            throw new Exception("Database configuration is not set properly.");
        }

        try {
            $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";

            return new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
}
