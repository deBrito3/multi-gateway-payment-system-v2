<?php

namespace App\Services;

use App\Contracts\GatewayContract;
use App\Contracts\PaymentServiceContract;
use App\DTOs\PaymentData;
use App\DTOs\TransactionResult;
use App\Exceptions\PaymentFailedException;
use App\Models\Gateway;
use Illuminate\Support\Facades\Log;

class PaymentService implements PaymentServiceContract
{
    private string $usedGatewayName = '';

    /**
     * @param array<string, GatewayContract> $gateways
     */
    public function __construct(private array $gateways) {}

    public function process(PaymentData $data): TransactionResult
    {
        $activeGateways = Gateway::where('is_active', true)
            ->orderBy('priority')
            ->get();

        $errors = [];

        foreach ($activeGateways as $gatewayModel) {
            $gateway = $this->gateways[$gatewayModel->name] ?? null;

            if (!$gateway) {
                Log::warning("No implementation found for gateway: {$gatewayModel->name}");
                continue;
            }

            try {
                $gateway->authenticate();
                $result = $gateway->createTransaction($data);
                $this->usedGatewayName = $gateway->getName();
                return $result;
            } catch (\Throwable $e) {
                Log::error("Gateway {$gatewayModel->name} failed: {$e->getMessage()}");
                $errors[] = "{$gatewayModel->name}: {$e->getMessage()}";
            }
        }

        throw new PaymentFailedException(
            'All payment gateways failed. Errors: ' . implode(' | ', $errors)
        );
    }

    public function getUsedGatewayName(): string
    {
        return $this->usedGatewayName;
    }
}
