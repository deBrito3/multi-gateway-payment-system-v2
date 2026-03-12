<?php

namespace App\Actions\User;

use App\Models\Role;
use App\Models\User;

class UpdateUserAction
{
    public function execute(User $user, array $data): User
    {
        $userData = [];
        if (array_key_exists('name', $data)) {
            $userData['name'] = $data['name'];
        }
        if (array_key_exists('email', $data)) {
            $userData['email'] = $data['email'];
        }
        if (array_key_exists('password', $data)) {
            $userData['password'] = $data['password']; // Model cast handles hashing
        }

        $user->update($userData);

        if (isset($data['roles'])) {
            $roleIds = Role::whereIn('name', $data['roles'])->pluck('id');
            $user->roles()->sync($roleIds);
        }

        return $user->fresh('roles');
    }
}
