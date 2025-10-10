<?php

declare(strict_types=1);

namespace Model;

class User implements UserInterface
{

    private ?int $id;
    private ?string $full_name;
    private ?string $email;
    private ?string $phone_number;

    /** @var LoanInterface[] */
    private array $loans = [];

    /** @var DocumentInterface[] */
    private array $documents = [];

    public function __construct()
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->full_name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhoneNumber(): string
    {
        return $this->phone_number;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setFullName(string $full_name): void
    {
        $this->full_name = $full_name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPhoneNumber(string $phone_number): void
    {
        $this->phone_number = $phone_number;
    }

    public function getLoans(): ?array
    {
        return $this->loans;
    }

    public function addLoan(LoanInterface $loan): void
    {
        $this->loans[] = $loan;
    }

    public function getDocuments(): ?array
    {
        return $this->documents;
    }

    public function addDocument(DocumentInterface $document): void
    {
        $this->documents[] = $document;
    }

}