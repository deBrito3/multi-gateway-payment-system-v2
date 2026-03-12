<?php

namespace Tests\Unit\Gateways;

use App\DTOs\PaymentData;
use App\Gateways\GatewayOne;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayOneTest extends TestCase
{
    private GatewayOne $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new GatewayOne();
    }

    public function test_get_name_returns_gateway_one(): void
    {
        $this->assertEquals('gateway_one', $this->gateway->getName());
    }

    public function test_authenticate_stores_bearer_token(): void
    {
        Http::fake([
            '*/login' => Http::response(['token' => 'test-bearer-token'], 200),
        ]);

        $this->gateway->authenticate();

        Http::fake([
            '*/transactions' => Http::response(['id' => 'ext-1', 'status' => 'paid'], 200),
        ]);

        $data = new PaymentData(1000, 'Test', 'test@test.com', '5569000000006063', '010');
        $result = $this->gateway->createTransaction($data);

        $this->assertEquals('ext-1', $result->externalId);
    }

    public function test_create_transaction_returns_result(): void
    {
        Http::fake([
            '*/login' => Http::response(['token' => 'test-token'], 200),
            '*/transactions' => Http::response(['id' => 'ext-123', 'status' => 'approved'], 200),
        ]);

        $this->gateway->authenticate();
        $data = new PaymentData(1000, 'Test', 'test@test.com', '5569000000006063', '010');
        $result = $this->gateway->createTransaction($data);

        $this->assertEquals('ext-123', $result->externalId);
    }

    public function test_create_transaction_throws_on_error(): void
    {
        Http::fake([
            '*/login' => Http::response(['token' => 'test-token'], 200),
            '*/transactions' => Http::response(['error' => 'Invalid card'], 400),
        ]);

        $this->gateway->authenticate();
        $data = new PaymentData(1000, 'Test', 'test@test.com', '5569000000006063', '100');

        $this->expectException(\Exception::class);
        $this->gateway->createTransaction($data);
    }

    public function test_refund_returns_result(): void
    {
        Http::fake([
            '*/login' => Http::response(['token' => 'test-token'], 200),
            '*/transactions/ext-1/charge_back' => Http::response(['status' => 'refunded'], 200),
        ]);

        $this->gateway->authenticate();
        $result = $this->gateway->refund('ext-1');

        $this->assertTrue($result->success);
    }
}
