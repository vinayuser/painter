<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'items' => CartItemResource::collection($this->resource['items']),
            'item_count' => $this->resource['item_count'],
            'subtotal' => (float) $this->resource['subtotal'],
            'total_amount' => (float) $this->resource['total_amount'],
            'vendor' => isset($this->resource['vendor']) && $this->resource['vendor']
                ? new VendorSummaryResource($this->resource['vendor'])
                : null,
            'vendor_label' => $this->resource['vendor_label'] ?? null,
            'addresses' => CustomerAddressResource::collection($this->resource['addresses'] ?? []),
            'default_address' => $this->resource['default_address']
                ? new CustomerAddressResource($this->resource['default_address'])
                : null,
        ];
    }
}
