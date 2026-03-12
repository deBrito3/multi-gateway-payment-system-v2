<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

class LoginAction
{
    public function execute(string $email, string $password): ?string
    {
        $token = Auth::attempt(['email' => $email, 'password' => $password]);

        return $token ?: null;
    }
}
