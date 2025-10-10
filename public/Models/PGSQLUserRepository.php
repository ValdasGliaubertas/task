<?php

declare(strict_types=1);

namespace Model;

use Model\EnvConfig;
use PDO;
use PDOException;
use Throwable;

class PGSQLUserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    private EnvConfigInterface $envConfig;

    /**
     * @throws \Exception
     */
    public function __construct(EnvConfigInterface $envConfig)
    {
        try {
            $dbHost = $envConfig->get('DB_HOST');
            $dbName = $envConfig->get('DB_NAME');
            $dbUser = $envConfig->get('DB_USER');
            $dbPass = $envConfig->get('DB_PASS');
            $dbPort = $envConfig->get('DB_PORT');

            if (empty($dbPort)
              || !is_numeric($dbPort)
              || empty($dbName)
              || empty($dbUser)
              || empty($dbPass)) {
                throw new \Exception("Database configuration is not set properly.");
            }

            // Build DSN (Data Source Name)
            $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";

            // Create PDO instance
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
              PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,     // Throw exceptions
              PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Return rows as associative arrays
              PDO::ATTR_EMULATE_PREPARES => false,             // Use native prepared statements
            ]);

            $this->pdo = $pdo;
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function save(UserInterface $user): int
    {
        $this->pdo->beginTransaction();

        try {
            // Insert user
            $query = $this->pdo->prepare(
              "
                INSERT INTO users (full_name, email, phone_number) VALUES (:full_name, :email, :phone_number)
                RETURNING id
            "
            );
            $query->execute([
              ':name' => $user->getFullName(),
              ':email' => $user->getEmail(),
              ':phone_number' => $user->getPhoneNumber(),
            ]);
            $user_id = (int)$this->pdo->lastInsertId();
            $user->setId($user_id);

            if ($user->getLoans() !== null) {
                $loans = $user->getLoans();
                foreach ($loans as $loan) {
                    $query = $this->pdo->prepare(
                      "
                        INSERT INTO loans (user_id, amount)
                        VALUES (:user_id, :amount)
                    "
                    );
                    $query->execute([
                      ':user_id' => $user->getId(),
                      ':amount' => $loan->getAmount(),
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $user_id;
    }
}