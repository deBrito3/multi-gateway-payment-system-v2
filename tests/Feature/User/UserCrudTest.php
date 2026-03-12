<?php

namespace Tests\Feature\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authAs(string $roleName): array
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->roles()->attach($role);
        $token = auth()->login($user);
        return ['Authorization' => "Bearer $token"];
    }

    public function test_admin_can_list_users(): void
    {
        User::factory()->count(3)->create();
        $response = $this->getJson('/api/users', $this->authAs('ADMIN'));
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_admin_can_create_user_with_roles(): void
    {
        Role::firstOrCreate(['name' => 'USER']);

        $response = $this->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['USER'],
        ], $this->authAs('ADMIN'));

        $response->assertCreated()
            ->assertJsonPath('data.name', 'New User');
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create(['name' => 'Old']);

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated',
        ], $this->authAs('ADMIN'));

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    }

    public function test_admin_can_soft_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}", [], $this->authAs('ADMIN'));

        $response->assertOk();
        $this->assertSoftDeleted($user);
    }

    public function test_manager_can_manage_users(): void
    {
        $response = $this->getJson('/api/users', $this->authAs('MANAGER'));
        $response->assertOk();
    }

    public function test_finance_cannot_manage_users(): void
    {
        $response = $this->getJson('/api/users', $this->authAs('FINANCE'));
        $response->assertForbidden();
    }

    public function test_create_user_validates_required_fields(): void
    {
        $response = $this->postJson('/api/users', [], $this->authAs('ADMIN'));
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_create_user_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@test.com']);

        $response = $this->postJson('/api/users', [
            'name' => 'Dup',
            'email' => 'taken@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $this->authAs('ADMIN'));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
