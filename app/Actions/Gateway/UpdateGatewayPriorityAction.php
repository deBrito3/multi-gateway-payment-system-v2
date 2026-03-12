<?php

namespace App\Actions\Gateway;

use App\Models\Gateway;
use Illuminate\Support\Facades\DB;

class UpdateGatewayPriorityAction
{
    public function execute(Gateway $gateway, int $newPriority): Gateway
    {
        return DB::transaction(function () use ($gateway, $newPriority) {
            $existing = Gateway::where('priority', $newPriority)->first();

            if ($existing && $existing->id !== $gateway->id) {
                $oldPriority = $gateway->priority;
                // Use temporary value to avoid unique constraint violation
                $gateway->update(['priority' => 0]);
                $existing->update(['priority' => $oldPriority]);
                $gateway->update(['priority' => $newPriority]);
            } else {
                $gateway->update(['priority' => $newPriority]);
            }

            return $gateway->fresh();
        });
    }
}
