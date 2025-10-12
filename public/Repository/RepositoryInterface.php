<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\UserInterface;

interface RepositoryInterface
{

    public function save(UserInterface $user): int;

}