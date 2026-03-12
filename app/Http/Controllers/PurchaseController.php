<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\CreatePurchaseAction;
use App\Exceptions\PaymentFailedException;
use App\Http\Requests\StorePurchaseRequest;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    public function store(StorePurchaseRequest $request, CreatePurchaseAction $action): JsonResponse
    {
        try {
            $transaction = $action->execute($request->validated());

            return response()->json([
                'data' => [
                    'transaction_id' => $transaction->id,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'card_last_numbers' => $transaction->card_last_numbers,
                    'gateway' => $transaction->gateway->name,
                    'external_id' => $transaction->external_id,
                    'client' => $transaction->client,
                    'products' => $transaction->products->map(fn($p) => [
                        'product_id' => $p->id,
                        'name' => $p->name,
                        'quantity' => $p->pivot->quantity,
                        'unit_price' => $p->pivot->unit_price,
                    ]),
                    'created_at' => $transaction->created_at,
                ],
            ], 201);
        } catch (PaymentFailedException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
