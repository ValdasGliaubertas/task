<?php

namespace Model;

interface LoanInterface
{
    public function getAmount(): float;

    public function setAmount(float $amount): void;

}