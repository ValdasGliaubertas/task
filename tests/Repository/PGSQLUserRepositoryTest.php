<?php

declare(strict_types=1);

namespace Tests\Repository;

use App\Repository\PGSQLUserRepository;
use App\Repository\PDOFactoryInterface;
use App\Model\UserInterface;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Exception;

final class PGSQLUserRepositoryTest extends TestCase
{
    private PDOFactoryInterface $factory;
    private PDO $pdo;
    private PDOStatement $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = $this->createMock(PDOFactoryInterface::class);
        $this->pdo = $this->createMock(PDO::class);
        $this->query = $this->createMock(PDOStatement::class);

        $this->factory->method('create')->willReturn($this->pdo);
    }

    public function testSaveInsertsUserAndCommitsTransaction(): void
    {
        // Setup mock user
        $user = $this->createConfiguredMock(UserInterface::class, [
            'getFullName' => 'John Doe',
            'getEmail' => 'john@example.com',
            'getPhoneNumber' => '123456789',
            'getLoans' => null,
            'getDocuments' => null,
        ]);
        $user->expects($this->once())->method('setId')->with(42);

        // PDO and statement behavior
        $this->pdo->expects($this->once())->method('beginTransaction');
        $this->pdo->expects($this->once())->method('commit');
        $this->pdo->expects($this->never())->method('rollBack');
        $this->pdo->method('prepare')->willReturn($this->query);
        $this->pdo->method('lastInsertId')->willReturn('42');

        // SELECT (no duplicate found)
        $this->query->method('execute')->willReturn(true);
        $this->query->method('fetch')->willReturn(false);

        $repo = new PGSQLUserRepository($this->factory);
        $id = $repo->save($user);

        $this->assertSame(42, $id);
    }

    public function testSaveThrowsExceptionWhenUserAlreadyExists(): void
    {
        $user = $this->createConfiguredMock(UserInterface::class, [
            'getFullName' => 'Jane Doe',
            'getEmail' => 'jane@example.com',
            'getPhoneNumber' => '555-123',
            'getLoans' => null,
            'getDocuments' => null,
        ]);

        $this->pdo->method('beginTransaction')->willReturn(true);
        $this->pdo->method('prepare')->willReturn($this->query);
        $this->query->method('execute')->willReturn(true);
        $this->pdo->method('inTransaction')->willReturn(true);
        $this->query->method('fetch')->willReturn(['id' => 1]); // simulate duplicate found

        $this->pdo->expects($this->once())->method('rollBack');

        $repo = new PGSQLUserRepository($this->factory);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User already exists.');

        $repo->save($user);
    }

    public function testSaveRollsBackOnFailureDuringInsert(): void
    {
        $user = $this->createConfiguredMock(UserInterface::class, [
            'getFullName' => 'Alice',
            'getEmail' => 'alice@example.com',
            'getPhoneNumber' => '999-123',
            'getLoans' => null,
            'getDocuments' => null,
        ]);

        $this->pdo->method('beginTransaction')->willReturn(true);
        $this->pdo->method('inTransaction')->willReturn(true);
        $this->pdo->expects($this->once())->method('rollBack');
        $this->pdo->method('prepare')->willReturn($this->query);

        // Simulate failure during insert
        $this->query->method('execute')
            ->willReturnCallback(function () {
                static $callCount = 0;
                $callCount++;
                if ($callCount === 2) {
                    throw new Exception('DB insert failed');
                }
                return true;
            });

        $repo = new PGSQLUserRepository($this->factory);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('DB insert failed');

        $repo->save($user);
    }

    public function testSaveAlsoInsertsLoansAndDocuments(): void
    {
        // Mock related models
        $loan = new class {
            public function getAmount(): int { return 1000; }
        };
        $document = new class {
            public function getName(): string { return 'contract.pdf'; }
        };

        $user = $this->createConfiguredMock(UserInterface::class, [
            'getFullName' => 'Bob',
            'getEmail' => 'bob@example.com',
            'getPhoneNumber' => '321-999',
            'getLoans' => [$loan],
            'getDocuments' => [$document],
        ]);

        $user->expects($this->once())->method('setId')->with(1);

        // PDO mocks
        $this->pdo->method('prepare')->willReturn($this->query);
        $this->pdo->method('lastInsertId')->willReturn('1');
        $this->query->method('execute')->willReturn(true);
        $this->query->method('fetch')->willReturn(false);
        $this->pdo->expects($this->once())->method('beginTransaction');
        $this->pdo->expects($this->once())->method('commit');

        $repo = new PGSQLUserRepository($this->factory);
        $id = $repo->save($user);

        $this->assertSame(1, $id);
    }
}
