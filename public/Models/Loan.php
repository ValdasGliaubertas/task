<?php

declare(strict_types=1);

namespace Model;

class Loan implements LoanInterface
{
    private ?float $amount;

    public function setAmount(float $amount): void {
        $this->amount = $amount;
    }

    public function getAmount(): float {
        return $this->amount;
    }

}