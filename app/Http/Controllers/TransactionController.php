<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\RefundPurchaseAction;
use App\Actions\Transaction\ListTransactionsAction;
use App\Actions\Transaction\ShowTransactionAction;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function index(ListTransactionsAction $action): JsonResponse
    {
        return response()->json(['data' => $action->execute()]);
    }

    public function show(Transaction $transaction, ShowTransactionAction $action): JsonResponse
    {
        return response()->json(['data' => $action->execute($transaction)]);
    }

    public function refund(Transaction $transaction, RefundPurchaseAction $action): JsonResponse
    {
        try {
            $transaction = $action->execute($transaction);
            return response()->json(['data' => $transaction]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
