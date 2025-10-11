<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\UserInterface;

interface RepositoryInterface
{

    public function save(UserInterface $user): int;

}