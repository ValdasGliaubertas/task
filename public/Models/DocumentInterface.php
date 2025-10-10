<?php

namespace Model;

interface DocumentInterface
{
    public function getId(): int;

    public function setId(int $id): void;

    public function getName(): string;

    public function setName(string $name);

}