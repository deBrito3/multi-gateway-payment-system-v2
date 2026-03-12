<?php

namespace App\Actions\Purchase;

use App\Enums\TransactionStatus;
use App\Gateways\GatewayOne;
use App\Gateways\GatewayTwo;
use App\Models\Transaction;

class RefundPurchaseAction
{
    public function execute(Transaction $transaction): Transaction
    {
        if ($transaction->status !== TransactionStatus::PAID) {
            throw new \InvalidArgumentException('Only paid transactions can be refunded');
        }

        $gatewayName = $transaction->gateway->name;
        $gateways = $this->resolveGateways();
        $gateway = $gateways[$gatewayName] ?? null;

        if (!$gateway) {
            throw new \RuntimeException("Gateway implementation not found: {$gatewayName}");
        }

        $gateway->authenticate();
        $result = $gateway->refund($transaction->external_id);

        if (!$result->success) {
            throw new \RuntimeException("Refund failed: {$result->message}");
        }

        $transaction->update(['status' => 'refunded']);

        return $transaction->fresh()->load('client', 'gateway', 'products');
    }

    private function resolveGateways(): array
    {
        return [
            'gateway_one' => new GatewayOne(),
            'gateway_two' => new GatewayTwo(),
        ];
    }
}
