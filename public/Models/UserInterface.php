<?php

declare(strict_types=1);

namespace Model;

interface UserInterface
{
    public function getId(): int;

    public function getFullName(): string;

    public function getEmail(): string;

    public function getPhoneNumber(): string;

    public function setId(int $id): void;

    public function setFullName(string $full_name): void;

    public function setPhoneNumber(string $phone_number): void;

    public function setEmail(string $email): void;

    public function getLoan(): ?LoanInterface;

    public function getDocument(): ?DocumentInterface;

    public function setLoan(LoanInterface $loan): void;

    public function setDocument(DocumentInterface $document): void;

}