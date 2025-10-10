<?php

namespace Model;

interface EntityInterface
{
    public function save(EntityInterface $entity): int;
}