<?php

namespace App\Repository;

use PDO;

interface PDOFactoryInterface
{
    public function create(): PDO;
}
