<?php

namespace App\Actions\Purchase;

use App\Contracts\PaymentServiceContract;
use App\DTOs\PaymentData;
use App\Exceptions\PaymentFailedException;
use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class CreatePurchaseAction
{
    public function __construct(
        private PaymentServiceContract $paymentService,
    ) {}

    public function execute(array $data, ?string $idempotencyKey = null): Transaction
    {
        if ($idempotencyKey) {
            $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing->load('client', 'gateway', 'products');
            }
        }

        return DB::transaction(function () use ($data, $idempotencyKey) {
            $client = Client::firstOrCreate(
                ['email' => $data['client_email']],
                ['name' => $data['client_name']]
            );

            $products = collect($data['products'])->map(function ($item) {
                $product = Product::findOrFail($item['product_id']);
                return [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->amount,
                ];
            });

            $totalAmount = $products->sum(fn($p) => $p['unit_price'] * $p['quantity']);

            $firstGateway = Gateway::where('is_active', true)
                ->orderBy('priority')
                ->first();

            $transaction = Transaction::create([
                'client_id' => $client->id,
                'gateway_id' => $firstGateway->id,
                'external_id' => 'pending',
                'status' => 'pending',
                'amount' => $totalAmount,
                'card_last_numbers' => substr($data['card_number'], -4),
                'idempotency_key' => $idempotencyKey,
            ]);

            foreach ($products as $p) {
                $transaction->products()->attach($p['product']->id, [
                    'quantity' => $p['quantity'],
                    'unit_price' => $p['unit_price'],
                ]);
            }

            $paymentData = new PaymentData(
                amount: $totalAmount,
                name: $data['client_name'],
                email: $data['client_email'],
                cardNumber: $data['card_number'],
                cvv: $data['cvv'],
            );

            try {
                $result = $this->paymentService->process($paymentData);

                $gatewayName = $this->paymentService instanceof PaymentService
                    ? $this->paymentService->getUsedGatewayName()
                    : '';

                $gateway = Gateway::where('name', $gatewayName)->first();

                $transaction->update([
                    'status' => 'paid',
                    'external_id' => $result->externalId,
                    'gateway_id' => $gateway ? $gateway->id : $transaction->gateway_id,
                ]);

                return $transaction->fresh()->load('client', 'gateway', 'products');
            } catch (PaymentFailedException $e) {
                throw $e;
            }
        });
    }
}
