<?php

namespace App\Gateways;

use App\Contracts\GatewayContract;
use App\DTOs\PaymentData;
use App\DTOs\RefundResult;
use App\DTOs\TransactionResult;
use App\Exceptions\GatewayAuthException;
use Illuminate\Support\Facades\Http;

class GatewayOne implements GatewayContract
{
    private string $token = '';
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.gateway_one.url', 'http://localhost:3001');
    }

    public function getName(): string
    {
        return 'gateway_one';
    }

    public function authenticate(): void
    {
        $response = Http::post("{$this->baseUrl}/login", [
            'email' => config('services.gateway_one.email', 'dev@betalent.tech'),
            'token' => config('services.gateway_one.token', 'FEC9BB078BF338F464F96B48089EB498'),
        ]);

        if ($response->failed()) {
            throw new GatewayAuthException($this->getName(), $response->body());
        }

        $this->token = $response->json('token');
    }

    public function createTransaction(PaymentData $data): TransactionResult
    {
        $response = Http::withToken($this->token)
            ->post("{$this->baseUrl}/transactions", [
                'amount' => $data->amount,
                'name' => $data->name,
                'email' => $data->email,
                'cardNumber' => $data->cardNumber,
                'cvv' => $data->cvv,
            ]);

        if ($response->failed()) {
            throw new \Exception("Gateway One transaction failed: {$response->body()}");
        }

        return new TransactionResult(
            externalId: (string) $response->json('id'),
            status: $response->json('status', 'paid'),
        );
    }

    public function refund(string $externalId): RefundResult
    {
        $response = Http::withToken($this->token)
            ->post("{$this->baseUrl}/transactions/{$externalId}/charge_back");

        if ($response->failed()) {
            return new RefundResult(success: false, message: $response->body());
        }

        return new RefundResult(success: true);
    }
}
