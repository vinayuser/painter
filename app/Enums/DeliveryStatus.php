<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case PickedUp = 'picked_up';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::PickedUp => 'Picked Up',
            self::OutForDelivery => 'Out for Delivery',
            self::Delivered => 'Delivered',
        };
    }
}
