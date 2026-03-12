<?php

namespace Tests\Unit\Gateways;

use App\DTOs\PaymentData;
use App\Gateways\GatewayTwo;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayTwoTest extends TestCase
{
    private GatewayTwo $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new GatewayTwo();
    }

    public function test_get_name_returns_gateway_two(): void
    {
        $this->assertEquals('gateway_two', $this->gateway->getName());
    }

    public function test_authenticate_does_not_throw(): void
    {
        $this->gateway->authenticate();
        $this->assertTrue(true);
    }

    public function test_create_transaction_sends_portuguese_fields(): void
    {
        Http::fake([
            '*/transacoes' => Http::response(['id' => 'uuid-123', 'status' => 'aprovada'], 200),
        ]);

        $this->gateway->authenticate();
        $data = new PaymentData(2000, 'Test', 'test@test.com', '5569000000006063', '010');
        $result = $this->gateway->createTransaction($data);

        $this->assertEquals('uuid-123', $result->externalId);

        Http::assertSent(function ($request) {
            return $request->data()['valor'] === 2000
                && $request->data()['nome'] === 'Test'
                && $request->data()['numeroCartao'] === '5569000000006063';
        });
    }

    public function test_create_transaction_throws_on_error(): void
    {
        Http::fake([
            '*/transacoes' => Http::response(['error' => 'Cartao invalido'], 400),
        ]);

        $this->gateway->authenticate();
        $data = new PaymentData(2000, 'Test', 'test@test.com', '5569000000006063', '200');

        $this->expectException(\Exception::class);
        $this->gateway->createTransaction($data);
    }

    public function test_refund_sends_id_in_body(): void
    {
        Http::fake([
            '*/transacoes/reembolso' => Http::response(['status' => 'reembolsado'], 200),
        ]);

        $this->gateway->authenticate();
        $result = $this->gateway->refund('uuid-123');

        $this->assertTrue($result->success);

        Http::assertSent(function ($request) {
            return $request->data()['id'] === 'uuid-123';
        });
    }
}
