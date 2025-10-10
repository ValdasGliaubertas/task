<?php

declare(strict_types=1);

namespace App\Model;

interface DocumentInterface
{
    public function getId(): int;

    public function setId(int $id): void;

    public function getName(): string;

    public function setName(string $name);

}