<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\VendorPackingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'order_number', 'customer_id', 'vendor_id', 'delivery_agent_id', 'total_amount',
    'delivery_charge', 'payment_method', 'payment_status', 'order_status', 'delivery_status',
    'vendor_packing_status', 'packing_deadline_at', 'packed_at', 'delivery_deadline_at',
    'shipping_address', 'shipping_phone', 'notes', 'delivery_proof_path', 'delivered_at',
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'order_status' => OrderStatus::class,
            'delivery_status' => DeliveryStatus::class,
            'vendor_packing_status' => VendorPackingStatus::class,
            'packing_deadline_at' => 'datetime',
            'packed_at' => 'datetime',
            'delivery_deadline_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function deliveryAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_agent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function grandTotal(): float
    {
        return (float) $this->total_amount + (float) $this->delivery_charge;
    }

    public function amountToCollect(): float
    {
        if ($this->payment_method === PaymentMethod::Online && $this->payment_status === PaymentStatus::Paid) {
            return (float) $this->delivery_charge;
        }

        return $this->grandTotal();
    }

    public function isPackingTimerActive(): bool
    {
        return $this->vendor_packing_status === VendorPackingStatus::Pending
            && $this->packing_deadline_at !== null;
    }

    public function isDeliveryTimerActive(): bool
    {
        return $this->order_status !== OrderStatus::Delivered
            && $this->order_status !== OrderStatus::Cancelled
            && $this->delivery_deadline_at !== null;
    }

    public function packingSecondsRemaining(): int
    {
        if (! $this->isPackingTimerActive() || ! $this->packing_deadline_at) {
            return 0;
        }

        return max(0, $this->packing_deadline_at->timestamp - now()->timestamp);
    }

    public function deliverySecondsRemaining(): int
    {
        if (! $this->isDeliveryTimerActive() || ! $this->delivery_deadline_at) {
            return 0;
        }

        return max(0, $this->delivery_deadline_at->timestamp - now()->timestamp);
    }
}
