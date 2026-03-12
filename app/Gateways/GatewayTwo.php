<?php

namespace App\Gateways;

use App\Contracts\GatewayContract;
use App\DTOs\PaymentData;
use App\DTOs\RefundResult;
use App\DTOs\TransactionResult;
use Illuminate\Support\Facades\Http;

class GatewayTwo implements GatewayContract
{
    private string $baseUrl;
    private string $authToken;
    private string $authSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.gateway_two.url', 'http://localhost:3002');
        $this->authToken = config('services.gateway_two.auth_token', 'tk_f2198cc671b5289fa856');
        $this->authSecret = config('services.gateway_two.auth_secret', '3d15e8ed6131446ea7e3456728b1211f');
    }

    public function getName(): string
    {
        return 'gateway_two';
    }

    public function authenticate(): void
    {
        // Gateway 2 uses static headers — no login needed
    }

    public function createTransaction(PaymentData $data): TransactionResult
    {
        $response = Http::withHeaders([
            'Gateway-Auth-Token' => $this->authToken,
            'Gateway-Auth-Secret' => $this->authSecret,
        ])->post("{$this->baseUrl}/transacoes", [
            'valor' => $data->amount,
            'nome' => $data->name,
            'email' => $data->email,
            'numeroCartao' => $data->cardNumber,
            'cvv' => $data->cvv,
        ]);

        if ($response->failed()) {
            throw new \Exception("Gateway Two transaction failed: {$response->body()}");
        }

        return new TransactionResult(
            externalId: (string) $response->json('id'),
            status: $response->json('status', 'paid'),
        );
    }

    public function refund(string $externalId): RefundResult
    {
        $response = Http::withHeaders([
            'Gateway-Auth-Token' => $this->authToken,
            'Gateway-Auth-Secret' => $this->authSecret,
        ])->post("{$this->baseUrl}/transacoes/reembolso", [
            'id' => $externalId,
        ]);

        if ($response->failed()) {
            return new RefundResult(success: false, message: $response->body());
        }

        return new RefundResult(success: true);
    }
}
