<?php

namespace App\Actions\Transaction;

use App\Models\Transaction;

class ShowTransactionAction
{
    public function execute(Transaction $transaction): Transaction
    {
        return $transaction->load('client', 'gateway', 'products');
    }
}
