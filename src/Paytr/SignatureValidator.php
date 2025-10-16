<?php

namespace App\Paytr;

class SignatureValidator
{
    private string $merchantKey;
    private string $merchantSalt;

    public function __construct(string $merchantKey, string $merchantSalt)
    {
        $this->merchantKey = $merchantKey;
        $this->merchantSalt = $merchantSalt;
    }

    public function validate(array $payload): bool
    {
        if (!isset($payload['hash']) || !isset($payload['merchant_oid']) || !isset($payload['status'])) {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $payload['merchant_oid'] . $this->merchantSalt . $payload['status'] . ($payload['total_amount'] ?? ''), $this->merchantKey, true));

        return hash_equals($expected, $payload['hash']);
    }
}