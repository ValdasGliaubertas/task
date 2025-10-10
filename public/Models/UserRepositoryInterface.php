<?php

namespace Model;

interface UserRepositoryInterface
{

    public function save(UserInterface $user): int;

}