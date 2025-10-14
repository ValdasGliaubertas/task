<?php

namespace App\Service;

// Overrides PHP's global function *within this namespace* during tests.
function checkdnsrr(string $domain): bool
{
    // Simulate: "valid.com" has MX, anything else doesn't
    return $domain === 'valid.com';
}