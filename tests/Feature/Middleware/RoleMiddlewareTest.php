<?php

namespace Tests\Feature\Middleware;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateWithRole(string $roleName): string
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->roles()->attach($role);

        return auth()->login($user);
    }

    public function test_user_with_correct_role_can_access(): void
    {
        $token = $this->authenticateWithRole('ADMIN');

        $response = $this->getJson('/api/users', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertOk();
    }

    public function test_user_without_role_gets_403(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'USER']);
        $user->roles()->attach($role);
        $token = auth()->login($user);

        $response = $this->getJson('/api/users', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertForbidden();
    }
}
