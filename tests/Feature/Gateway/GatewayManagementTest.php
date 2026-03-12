<?php

namespace Tests\Feature\Gateway;

use App\Models\Gateway;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayManagementTest extends TestCase
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

    public function test_admin_can_toggle_gateway(): void
    {
        $gateway = Gateway::create(['name' => 'gw1', 'priority' => 1, 'is_active' => true]);

        $response = $this->patchJson("/api/gateways/{$gateway->id}/toggle", [], $this->authAs('ADMIN'));

        $response->assertOk();
        $this->assertFalse($gateway->fresh()->is_active);
    }

    public function test_admin_can_update_gateway_priority(): void
    {
        $gw1 = Gateway::create(['name' => 'gw1', 'priority' => 1]);
        $gw2 = Gateway::create(['name' => 'gw2', 'priority' => 2]);

        $response = $this->patchJson("/api/gateways/{$gw1->id}/priority", [
            'priority' => 2,
        ], $this->authAs('ADMIN'));

        $response->assertOk();
        $this->assertEquals(2, $gw1->fresh()->priority);
        $this->assertEquals(1, $gw2->fresh()->priority);
    }

    public function test_non_admin_cannot_manage_gateways(): void
    {
        $gateway = Gateway::create(['name' => 'gw1', 'priority' => 1]);

        $response = $this->patchJson("/api/gateways/{$gateway->id}/toggle", [], $this->authAs('MANAGER'));

        $response->assertForbidden();
    }
}
