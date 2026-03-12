<?php

namespace App\Http\Controllers;

use App\Actions\Gateway\ToggleGatewayAction;
use App\Actions\Gateway\UpdateGatewayPriorityAction;
use App\Http\Requests\UpdateGatewayPriorityRequest;
use App\Models\Gateway;
use Illuminate\Http\JsonResponse;

class GatewayController extends Controller
{
    public function toggle(Gateway $gateway, ToggleGatewayAction $action): JsonResponse
    {
        $gateway = $action->execute($gateway);
        return response()->json(['data' => $gateway]);
    }

    public function updatePriority(
        UpdateGatewayPriorityRequest $request,
        Gateway $gateway,
        UpdateGatewayPriorityAction $action
    ): JsonResponse {
        $gateway = $action->execute($gateway, $request->validated()['priority']);
        return response()->json(['data' => $gateway]);
    }
}
