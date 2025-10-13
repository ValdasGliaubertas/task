<?php

declare(strict_types=1);

namespace App\Service\Validators;

use App\Service\DnsCheckerInterface;
use App\maps\InputMap;

final class ValidateEmail implements StringValidatorInterface
{

    private array $errors = [];

    public function __construct(private readonly DnsCheckerInterface $dnsChecker)
    {
    }

    public function validate(string $input): bool
    {
        // Additionally 3rd party email validation services can be used here
        if (empty($input) || !filter_var($input, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
            $this->errors[] = "Invalid email address.";
            return false;
        }

        $mail_parts = explode('@', $input);
        $domain = $mail_parts[1] ?? '';
        if (empty($domain) || !$this->dnsChecker->domainHasMxRecord($domain)) {
            $this->errors[] = 'Email domain cannot receive mail';
            return false;
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function supportedKeys(): array
    {
        return [InputMap::EMAIL];
    }
}