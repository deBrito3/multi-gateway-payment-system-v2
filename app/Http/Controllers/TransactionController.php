<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\RefundPurchaseAction;
use App\Actions\Transaction\ListTransactionsAction;
use App\Actions\Transaction\ShowTransactionAction;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function index(ListTransactionsAction $action): JsonResponse
    {
        return TransactionResource::collection($action->execute())->response();
    }

    public function show(Transaction $transaction, ShowTransactionAction $action): JsonResponse
    {
        return (new TransactionResource($action->execute($transaction)))->response();
    }

    public function refund(Transaction $transaction, RefundPurchaseAction $action): JsonResponse
    {
        try {
            $transaction = $action->execute($transaction);
            return (new TransactionResource($transaction->load(['client', 'gateway', 'products'])))->response();
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
