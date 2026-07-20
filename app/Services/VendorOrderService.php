<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\VendorPackingStatus;
use App\Models\Order;

class VendorOrderService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function markPacked(Order $order): Order
    {
        if ($order->vendor_packing_status === VendorPackingStatus::Packed) {
            throw new \RuntimeException('Order is already packed.');
        }

        $order->update([
            'vendor_packing_status' => VendorPackingStatus::Packed,
            'packed_at' => now(),
            'order_status' => OrderStatus::Packed,
        ]);

        $order = $order->fresh(['items', 'customer', 'deliveryAgent', 'payment', 'vendor']);

        $this->notifications->orderPacked($order);

        return $order;
    }
}
