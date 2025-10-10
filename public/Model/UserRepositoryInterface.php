<?php

declare(strict_types=1);

namespace App\Model;

interface UserRepositoryInterface
{

    public function save(UserInterface $user): int;

}