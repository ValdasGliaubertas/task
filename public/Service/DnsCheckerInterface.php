<?php

declare(strict_types=1);

namespace App\Service;

interface DnsCheckerInterface
{
    public function domainHasMxRecord(string $domain): bool;

}