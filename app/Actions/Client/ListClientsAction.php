<?php

namespace App\Actions\Client;

use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListClientsAction
{
    public function execute(): LengthAwarePaginator
    {
        return Client::paginate(100);
    }
}
