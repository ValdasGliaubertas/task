<?php

declare(strict_types=1);

use App\Model\Loan;
use App\Model\LoanInterface;
use PHPUnit\Framework\TestCase;

final class LoanTest extends TestCase
{

    private LoanInterface $loan;

    public function setUp(): void
    {
        $this->loan = new Loan();
    }

    public function testSetAndGetId(): void
    {
        $this->loan->setId(1);
        $this->assertEquals(1, $this->loan->getId());
    }

    public function testSetAndGetFirstName(): void
    {
        $this->loan->setAmount(100.50);
        $this->assertEquals(100.50, $this->loan->getAmount());
    }
}