<?php

namespace Tests\Feature\Client;

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientEndpointsTest extends TestCase
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

    public function test_can_list_clients(): void
    {
        Client::create(['name' => 'Client A', 'email' => 'a@test.com']);
        Client::create(['name' => 'Client B', 'email' => 'b@test.com']);

        $response = $this->getJson('/api/clients', $this->authAs('USER'));

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_can_show_client_with_transactions(): void
    {
        $client = Client::create(['name' => 'Client A', 'email' => 'a@test.com']);
        $gateway = Gateway::create(['name' => 'gw1', 'priority' => 1]);
        $product = Product::create(['name' => 'Prod', 'amount' => 500]);

        $transaction = Transaction::create([
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'ext-1',
            'status' => 'paid',
            'amount' => 1000,
            'card_last_numbers' => '6063',
        ]);
        $transaction->products()->attach($product, ['quantity' => 2, 'unit_price' => 500]);

        $response = $this->getJson("/api/clients/{$client->id}", $this->authAs('USER'));

        $response->assertOk()
            ->assertJsonPath('data.name', 'Client A')
            ->assertJsonCount(1, 'data.transactions');
    }

    public function test_unauthenticated_cannot_list_clients(): void
    {
        $response = $this->getJson('/api/clients');
        $response->assertUnauthorized();
    }
}
