<?php

namespace Tests\Feature\Purchase;

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RefundTest extends TestCase
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

    private function createPaidTransaction(string $gatewayName = 'gateway_one'): Transaction
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
        $gateway = Gateway::create(['name' => $gatewayName, 'priority' => 1]);

        return Transaction::create([
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'ext-refund-1',
            'status' => 'paid',
            'amount' => 1000,
            'card_last_numbers' => '6063',
        ]);
    }

    public function test_admin_can_refund_transaction(): void
    {
        $transaction = $this->createPaidTransaction();

        Http::fake([
            '*/login' => Http::response(['token' => 'test-token'], 200),
            '*/transactions/ext-refund-1/charge_back' => Http::response(['status' => 'refunded'], 200),
        ]);

        $response = $this->postJson(
            "/api/transactions/{$transaction->id}/refund",
            [],
            $this->authAs('ADMIN')
        );

        $response->assertOk()
            ->assertJsonPath('data.status', 'refunded');
    }

    public function test_finance_can_refund_transaction(): void
    {
        $transaction = $this->createPaidTransaction();

        Http::fake([
            '*/login' => Http::response(['token' => 'test-token'], 200),
            '*/transactions/ext-refund-1/charge_back' => Http::response(['status' => 'refunded'], 200),
        ]);

        $response = $this->postJson(
            "/api/transactions/{$transaction->id}/refund",
            [],
            $this->authAs('FINANCE')
        );

        $response->assertOk();
    }

    public function test_user_cannot_refund(): void
    {
        $transaction = $this->createPaidTransaction();

        $response = $this->postJson(
            "/api/transactions/{$transaction->id}/refund",
            [],
            $this->authAs('USER')
        );

        $response->assertForbidden();
    }

    public function test_cannot_refund_non_paid_transaction(): void
    {
        $transaction = $this->createPaidTransaction();
        $transaction->update(['status' => 'refunded']);

        $response = $this->postJson(
            "/api/transactions/{$transaction->id}/refund",
            [],
            $this->authAs('ADMIN')
        );

        $response->assertUnprocessable();
    }
}
