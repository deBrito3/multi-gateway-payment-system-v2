<?php

namespace App\Actions\Client;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

class ListClientsAction
{
    public function execute(): Collection
    {
        return Client::all();
    }
}
