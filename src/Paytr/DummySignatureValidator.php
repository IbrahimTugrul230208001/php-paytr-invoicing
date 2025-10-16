<?php

namespace App\Paytr;

class DummySignatureValidator
{
    public function validate(array $payload): bool
    {
        return true;
    }
}