<?php

namespace App\Contracts;

use App\DTOs\PaymentData;
use App\DTOs\TransactionResult;

interface PaymentServiceContract
{
    public function process(PaymentData $data): TransactionResult;
}
