<?php

namespace App\DTOs;

class PaymentData
{
    public function __construct(
        public readonly int $amount,
        public readonly string $name,
        public readonly string $email,
        public readonly string $cardNumber,
        public readonly string $cvv,
    ) {}

    public function cardLastNumbers(): string
    {
        return substr($this->cardNumber, -4);
    }
}
