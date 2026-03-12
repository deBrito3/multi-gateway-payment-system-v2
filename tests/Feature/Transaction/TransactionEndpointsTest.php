<?php

namespace Tests\Feature\Transaction;

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionEndpointsTest extends TestCase
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

    private function createTransaction(): Transaction
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
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

        return $transaction;
    }

    public function test_can_list_transactions(): void
    {
        $this->createTransaction();

        $response = $this->getJson('/api/transactions', $this->authAs('USER'));

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_can_show_transaction_detail(): void
    {
        $transaction = $this->createTransaction();

        $response = $this->getJson("/api/transactions/{$transaction->id}", $this->authAs('USER'));

        $response->assertOk()
            ->assertJsonPath('data.external_id', 'ext-1')
            ->assertJsonStructure(['data' => ['client', 'gateway', 'products']]);
    }
}
