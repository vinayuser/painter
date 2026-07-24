<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'product' => $this->when(
                $this->relationLoaded('product') && $this->product,
                fn () => new ProductResource($this->product)
            ),
            'subtotal' => $this->when(
                $this->relationLoaded('product') && $this->product,
                fn () => (float) ($this->product->price * $this->quantity)
            ),
        ];
    }
}
