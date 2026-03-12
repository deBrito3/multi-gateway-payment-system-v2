<?php

namespace App\DTOs;

class TransactionResult
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $status,
    ) {}
}
