<?php

namespace App\Actions\Client;

use App\Models\Client;

class ShowClientAction
{
    public function execute(Client $client): Client
    {
        return $client->load('transactions.products', 'transactions.gateway');
    }
}
