<?php

namespace App\Enums;

enum FcmTopic: string
{
    case Customers = 'paint_store_customers';
    case Vendors = 'paint_store_vendors';
    case Painters = 'paint_store_painters';
    case DeliveryAgents = 'paint_store_delivery_agents';
    case Admins = 'paint_store_admins';

    public static function forRole(UserRole $role): self
    {
        return match ($role) {
            UserRole::Customer => self::Customers,
            UserRole::Vendor => self::Vendors,
            UserRole::Painter => self::Painters,
            UserRole::DeliveryAgent => self::DeliveryAgents,
            UserRole::Admin => self::Admins,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Customers => 'Customers',
            self::Vendors => 'Vendors',
            self::Painters => 'Painters',
            self::DeliveryAgents => 'Delivery Partners',
            self::Admins => 'Admins',
        };
    }
}
