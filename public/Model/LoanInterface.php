<?php

declare(strict_types=1);

namespace App\Model;

interface LoanInterface
{
    public function getId(): ?int;

    public function setId(int $id): void;

    public function getAmount(): float;

    public function setAmount(float $amount): void;

}