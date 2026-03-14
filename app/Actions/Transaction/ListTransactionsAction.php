<?php

namespace App\Actions\Transaction;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListTransactionsAction
{
    public function execute(): LengthAwarePaginator
    {
        return Transaction::with('client', 'gateway', 'products')->paginate(100);
    }
}
