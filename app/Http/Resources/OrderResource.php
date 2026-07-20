<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => (float) $this->total_amount,
            'delivery_charge' => (float) $this->delivery_charge,
            'grand_total' => $this->grandTotal(),
            'amount_to_collect' => $this->amountToCollect(),
            'payment_method' => $this->payment_method?->value,
            'payment_status' => $this->payment_status?->value,
            'order_status' => $this->order_status?->value,
            'delivery_status' => $this->delivery_status?->value,
            'vendor_packing_status' => $this->vendor_packing_status?->value,
            'packing_deadline_at' => $this->packing_deadline_at?->toISOString(),
            'packed_at' => $this->packed_at?->toISOString(),
            'delivery_deadline_at' => $this->delivery_deadline_at?->toISOString(),
            'packing_seconds_remaining' => $this->packingSecondsRemaining(),
            'delivery_seconds_remaining' => $this->deliverySecondsRemaining(),
            'shipping_address' => $this->shipping_address,
            'shipping_phone' => $this->shipping_phone,
            'notes' => $this->notes,
            'delivery_proof_url' => $this->delivery_proof_path
                ? Storage::disk('public')->url($this->delivery_proof_path)
                : null,
            'delivered_at' => $this->delivered_at?->toISOString(),
            'customer' => new UserResource($this->whenLoaded('customer')),
            'vendor' => new UserResource($this->whenLoaded('vendor')),
            'delivery_agent' => new UserResource($this->whenLoaded('deliveryAgent')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
