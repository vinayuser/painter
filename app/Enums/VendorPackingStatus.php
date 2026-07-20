<?php

namespace App\Enums;

enum VendorPackingStatus: string
{
    case Pending = 'pending';
    case Packed = 'packed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Packing',
            self::Packed => 'Packed',
        };
    }
}
