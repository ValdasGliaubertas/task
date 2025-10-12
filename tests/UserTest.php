<?php

declare(strict_types=1);

use App\Model\DocumentInterface;
use App\Model\LoanInterface;
use App\Model\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    private User $user;

    public function testCanSetAndGetId(): void
    {
        $this->user->setId(42);
        $this->assertSame(42, $this->user->getId());
    }

    public function testCanSetAndGetFullName(): void
    {
        $this->user->setFullName('John Doe');
        $this->assertSame('John Doe', $this->user->getFullName());
    }

    public function testCanSetAndGetEmail(): void
    {
        $email = 'john@example.com';
        $this->user->setEmail($email);
        $this->assertSame($email, $this->user->getEmail());
    }

    public function testCanSetAndGetPhoneNumber(): void
    {
        $phone = '+37060000000';
        $this->user->setPhoneNumber($phone);
        $this->assertSame($phone, $this->user->getPhoneNumber());
    }

    public function testAddAndGetLoans(): void
    {
        $loanMock1 = $this->createMock(LoanInterface::class);
        $loanMock2 = $this->createMock(LoanInterface::class);

        $this->user->addLoan($loanMock1);
        $this->user->addLoan($loanMock2);

        $loans = $this->user->getLoans();

        $this->assertCount(2, $loans);
        $this->assertContains($loanMock1, $loans);
        $this->assertContains($loanMock2, $loans);
    }

    public function testAddAndGetDocuments(): void
    {
        $documentMock = $this->createMock(DocumentInterface::class);
        $this->user->addDocument($documentMock);

        $documents = $this->user->getDocuments();

        $this->assertCount(1, $documents);
        $this->assertSame($documentMock, $documents[0]);
    }

    public function testNewUserHasEmptyCollections(): void
    {
        $this->assertEmpty($this->user->getLoans());
        $this->assertEmpty($this->user->getDocuments());
    }

    protected function setUp(): void
    {
        $this->user = new User();
    }
}