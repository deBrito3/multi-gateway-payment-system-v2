<?php

namespace Tests\Unit\Models;

use App\Models\Transaction;
use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_belongs_to_client(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
        $gateway = Gateway::create(['name' => 'gw1', 'priority' => 1]);
        $transaction = Transaction::create([
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'ext-1',
            'status' => 'paid',
            'amount' => 1000,
            'card_last_numbers' => '6063',
        ]);

        $this->assertEquals($client->id, $transaction->client->id);
    }

    public function test_transaction_belongs_to_gateway(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
        $gateway = Gateway::create(['name' => 'gw1', 'priority' => 1]);
        $transaction = Transaction::create([
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'ext-1',
            'status' => 'paid',
            'amount' => 1000,
            'card_last_numbers' => '6063',
        ]);

        $this->assertEquals($gateway->id, $transaction->gateway->id);
    }

    public function test_transaction_has_products(): void
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

        $this->assertEquals(1, $transaction->products->count());
        $this->assertEquals(2, $transaction->products->first()->pivot->quantity);
        $this->assertEquals(500, $transaction->products->first()->pivot->unit_price);
    }

    public function test_transaction_casts_status_to_enum(): void
    {
        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com']);
        $gateway = Gateway::create(['name' => 'gw1', 'priority' => 1]);
        $transaction = Transaction::create([
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'ext-1',
            'status' => 'paid',
            'amount' => 1000,
            'card_last_numbers' => '6063',
        ]);

        $this->assertInstanceOf(\App\Enums\TransactionStatus::class, $transaction->status);
        $this->assertEquals(\App\Enums\TransactionStatus::PAID, $transaction->status);
    }
}
