<?php

namespace App\Actions\Gateway;

use App\Models\Gateway;

class ToggleGatewayAction
{
    public function execute(Gateway $gateway): Gateway
    {
        $gateway->update(['is_active' => !$gateway->is_active]);
        return $gateway->fresh();
    }
}
