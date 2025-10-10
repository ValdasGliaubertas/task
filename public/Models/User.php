<?php

declare(strict_types=1);

namespace Model;

class User implements UserInterface
{

    private ?int $id;
    private string $full_name;
    private string $email;
    private string $phone_number;

    private ?LoanInterface $loan;

    private ?DocumentInterface $document;

    private RepositoryInterface $repository;

    public function __construct(string $full_name, string $email, string $phone_number, RepositoryInterface $repository)
    {
        $this->repository = $repository;
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

    public function getLoan(): ?LoanInterface
    {
        return $this->loan;
    }

    public function getDocument(): ?DocumentInterface
    {
        return $this->document;
    }

    public function setLoan(LoanInterface $loan): void
    {
        $this->loan = $loan;
    }

    public function setDocument(DocumentInterface $document): void
    {
        $this->document = $document;
    }

}