<?php

declare(strict_types=1);

namespace App\Service;

final class NativeDnsChecker implements DnsCheckerInterface
{
    public function domainHasMxRecord(string $domain): bool
    {
        return checkdnsrr($domain, 'MX');
    }
}