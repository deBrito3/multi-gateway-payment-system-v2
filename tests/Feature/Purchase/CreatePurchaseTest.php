<?php

namespace Tests\Feature\Purchase;

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreatePurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gateway::create(['name' => 'gateway_one', 'priority' => 1, 'is_active' => true]);
        Gateway::create(['name' => 'gateway_two', 'priority' => 2, 'is_active' => true]);
    }

    public function test_can_create_purchase_successfully(): void
    {
        $product1 = Product::create(['name' => 'A', 'amount' => 1500]);
        $product2 = Product::create(['name' => 'B', 'amount' => 2000]);

        Http::fake([
            '*/login' => Http::response(['token' => 'test-token'], 200),
            '*/transactions' => Http::response(['id' => 'ext-abc', 'status' => 'paid'], 200),
        ]);

        $response = $this->postJson('/api/purchases', [
            'client_name' => 'John Doe',
            'client_email' => 'john@test.com',
            'card_number' => '5569000000006063',
            'cvv' => '010',
            'products' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.amount', 5000)
            ->assertJsonPath('data.card_last_numbers', '6063');

        $this->assertDatabaseHas('clients', ['email' => 'john@test.com']);
        $this->assertDatabaseHas('transactions', ['external_id' => 'ext-abc']);
    }

    public function test_falls_back_to_second_gateway(): void
    {
        $product = Product::create(['name' => 'A', 'amount' => 1000]);

        Http::fake([
            '*/login' => Http::response(['token' => 'test-token'], 200),
            '*/transactions' => Http::response(['error' => 'fail'], 400),
            '*/transacoes' => Http::response(['id' => 'uuid-fallback', 'status' => 'aprovada'], 200),
        ]);

        $response = $this->postJson('/api/purchases', [
            'client_name' => 'Jane',
            'client_email' => 'jane@test.com',
            'card_number' => '5569000000006063',
            'cvv' => '010',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.gateway', 'gateway_two');
    }

    public function test_returns_error_when_all_gateways_fail(): void
    {
        $product = Product::create(['name' => 'A', 'amount' => 1000]);

        Http::fake([
            '*' => Http::response(['error' => 'fail'], 400),
        ]);

        $response = $this->postJson('/api/purchases', [
            'client_name' => 'Fail',
            'client_email' => 'fail@test.com',
            'card_number' => '5569000000006063',
            'cvv' => '100',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseMissing('transactions', ['card_last_numbers' => '6063']);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson('/api/purchases', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['client_name', 'client_email', 'card_number', 'cvv', 'products']);
    }

    public function test_validates_product_exists(): void
    {
        $response = $this->postJson('/api/purchases', [
            'client_name' => 'Test',
            'client_email' => 'test@test.com',
            'card_number' => '5569000000006063',
            'cvv' => '010',
            'products' => [
                ['product_id' => 9999, 'quantity' => 1],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['products.0.product_id']);
    }

    public function test_reuses_existing_client(): void
    {
        $product = Product::create(['name' => 'A', 'amount' => 500]);
        Client::create(['name' => 'Existing', 'email' => 'existing@test.com']);

        Http::fake([
            '*/login' => Http::response(['token' => 'test-token'], 200),
            '*/transactions' => Http::response(['id' => 'ext-1', 'status' => 'paid'], 200),
        ]);

        $this->postJson('/api/purchases', [
            'client_name' => 'Existing Updated',
            'client_email' => 'existing@test.com',
            'card_number' => '5569000000006063',
            'cvv' => '010',
            'products' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $this->assertEquals(1, Client::where('email', 'existing@test.com')->count());
    }
}
