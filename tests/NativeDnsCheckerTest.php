<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Service\DnsCheckerInterface;
use App\Service\NativeDnsChecker;
use PHPUnit\Framework\TestCase;

final class NativeDnsCheckerTest extends TestCase
{

    public function testImplementsInterface(): void
    {
        $checker = new NativeDnsChecker();
        $this->assertInstanceOf(DnsCheckerInterface::class, $checker);
    }

    public function testDomainHasMxRecordUsesCheckdnsrrFunction(): void
    {
        // Include stub that overrides checkdnsrr()
        require_once __DIR__ . '/_stubs/checkdnsrr_stub.php';

        $checker = new NativeDnsChecker();
        $this->assertTrue($checker->domainHasMxRecord('valid.com'));
        $this->assertFalse($checker->domainHasMxRecord('nope.com'));
    }
}
