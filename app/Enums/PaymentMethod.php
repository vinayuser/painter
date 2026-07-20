<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Online = 'online';
    case Cod = 'cod';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online (Razorpay)',
            self::Cod => 'Cash on Delivery',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
