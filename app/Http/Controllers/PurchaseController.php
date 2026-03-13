<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\CreatePurchaseAction;
use App\Exceptions\PaymentFailedException;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    public function store(StorePurchaseRequest $request, CreatePurchaseAction $action): JsonResponse
    {
        try {
            $transaction = $action->execute($request->validated());
            $transaction->load(['client', 'gateway', 'products']);

            return (new PurchaseResource($transaction))->response()->setStatusCode(201);
        } catch (PaymentFailedException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
