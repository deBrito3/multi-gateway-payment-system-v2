<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUsersAction
{
    public function execute(): LengthAwarePaginator
    {
        return User::with('roles')->paginate(100);
    }
}
