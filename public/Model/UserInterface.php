<?php

declare(strict_types=1);

namespace App\Model;

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

    public function getLoans(): ?array;

    public function getDocuments(): ?array;

    public function addLoan(LoanInterface $loan): void;

    public function addDocument(DocumentInterface $document): void;

}