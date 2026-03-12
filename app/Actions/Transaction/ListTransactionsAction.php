<?php

namespace App\Actions\Transaction;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class ListTransactionsAction
{
    public function execute(): Collection
    {
        return Transaction::with('client', 'gateway', 'products')->get();
    }
}
