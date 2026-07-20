<?php

namespace App\Enums;

enum BookingImageType: string
{
    case Reference = 'reference';
    case Before = 'before';
    case After = 'after';

    public function label(): string
    {
        return match ($this) {
            self::Reference => 'Reference',
            self::Before => 'Before',
            self::After => 'After',
        };
    }
}
