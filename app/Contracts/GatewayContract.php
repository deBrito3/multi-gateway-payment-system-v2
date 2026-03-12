<?php

namespace App\Contracts;

use App\DTOs\PaymentData;
use App\DTOs\TransactionResult;
use App\DTOs\RefundResult;

interface GatewayContract
{
    public function getName(): string;
    public function authenticate(): void;
    public function createTransaction(PaymentData $data): TransactionResult;
    public function refund(string $externalId): RefundResult;
}
