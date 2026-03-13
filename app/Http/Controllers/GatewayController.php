<?php

namespace App\Http\Controllers;

use App\Actions\Gateway\ToggleGatewayAction;
use App\Actions\Gateway\UpdateGatewayPriorityAction;
use App\Http\Requests\UpdateGatewayPriorityRequest;
use App\Http\Resources\GatewayResource;
use App\Models\Gateway;
use Illuminate\Http\JsonResponse;

class GatewayController extends Controller
{
    public function toggle(Gateway $gateway, ToggleGatewayAction $action): JsonResponse
    {
        $gateway = $action->execute($gateway);
        return (new GatewayResource($gateway))->response();
    }

    public function updatePriority(
        UpdateGatewayPriorityRequest $request,
        Gateway $gateway,
        UpdateGatewayPriorityAction $action
    ): JsonResponse {
        $gateway = $action->execute($gateway, $request->validated()['priority']);
        return (new GatewayResource($gateway))->response();
    }
}
