<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\PainterBooking;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->role === UserRole::Admin
            || $order->customer_id === $user->id
            || $order->delivery_agent_id === $user->id
            || $order->vendor_id === $user->id;
    }

    public function updateVendor(User $user, Order $order): bool
    {
        return $user->role === UserRole::Admin
            || ($user->role === UserRole::Vendor && $order->vendor_id === $user->id);
    }

    public function updateDelivery(User $user, Order $order): bool
    {
        return $user->role === UserRole::Admin
            || ($user->role === UserRole::DeliveryAgent && $order->delivery_agent_id === $user->id);
    }
}
