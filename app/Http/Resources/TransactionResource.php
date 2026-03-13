<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'status' => $this->status,
            'amount' => $this->amount,
            'card_last_numbers' => $this->card_last_numbers,
            'client' => new ClientResource($this->whenLoaded('client')),
            'gateway' => new GatewayResource($this->whenLoaded('gateway')),
            'products' => TransactionProductResource::collection($this->whenLoaded('products')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
