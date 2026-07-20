<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PainterBooking;
use App\Models\User;

class PainterBookingPolicy
{
    public function view(User $user, PainterBooking $booking): bool
    {
        return $user->role === UserRole::Admin
            || $booking->customer_id === $user->id
            || $booking->painter_id === $user->id;
    }

    public function update(User $user, PainterBooking $booking): bool
    {
        return $user->role === UserRole::Admin
            || ($user->role === UserRole::Painter && $booking->painter_id === $user->id);
    }
}
