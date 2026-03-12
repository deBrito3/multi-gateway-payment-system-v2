<?php

namespace App\Actions\User;

use App\Models\Role;
use App\Models\User;

class CreateUserAction
{
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // Model cast handles hashing
        ]);

        if (!empty($data['roles'])) {
            $roleIds = Role::whereIn('name', $data['roles'])->pluck('id');
            $user->roles()->attach($roleIds);
        }

        return $user->load('roles');
    }
}
