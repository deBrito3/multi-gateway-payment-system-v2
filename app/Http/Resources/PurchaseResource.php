<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'transaction_id' => $this->id,
            'status' => $this->status,
            'amount' => $this->amount,
            'card_last_numbers' => $this->card_last_numbers,
            'gateway' => $this->gateway->name,
            'external_id' => $this->external_id,
            'client' => new ClientResource($this->client),
            'products' => TransactionProductResource::collection($this->products),
            'created_at' => $this->created_at,
        ];
    }
}
