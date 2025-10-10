<?php

declare(strict_types=1);

namespace App\Model;

class Loan implements LoanInterface
{
    private ?float $amount;

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

}