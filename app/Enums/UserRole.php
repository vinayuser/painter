<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Customer = 'customer';
    case Painter = 'painter';
    case DeliveryAgent = 'delivery_agent';
    case Vendor = 'vendor';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Customer => 'Customer',
            self::Painter => 'Painter',
            self::DeliveryAgent => 'Delivery Agent',
            self::Vendor => 'Vendor',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
