<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Packed = 'packed';
    case Assigned = 'assigned';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Packed => 'Packed',
            self::Assigned => 'Assigned',
            self::OutForDelivery => 'Out for Delivery',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }
}
