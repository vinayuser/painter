<?php

namespace App\Exceptions;

use App\Models\User;
use Exception;

class DifferentVendorCartException extends Exception
{
    public function __construct(
        public readonly ?User $cartVendor,
        public readonly ?User $productVendor,
    ) {
        $cartLabel = self::vendorLabel($cartVendor);
        $productLabel = self::vendorLabel($productVendor);

        parent::__construct(
            "Your cart contains items from {$cartLabel}. Clear the cart or send replace=true to add from {$productLabel}."
        );
    }

    public static function vendorLabel(?User $vendor): string
    {
        if (! $vendor) {
            return 'Paint Store';
        }

        return $vendor->business_name ?: $vendor->name;
    }
}
