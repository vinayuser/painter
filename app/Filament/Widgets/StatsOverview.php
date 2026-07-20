<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\PainterBooking;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = Payment::query()->where('status', PaymentStatus::Paid)->sum('amount');

        return [
            Stat::make('Total Customers', User::query()->where('role', UserRole::Customer)->count()),
            Stat::make('Active Products', Product::query()->where('is_active', true)->count()),
            Stat::make('Pending Orders', Order::query()->where('order_status', OrderStatus::Pending)->count()),
            Stat::make('Active Bookings', PainterBooking::query()
                ->whereIn('status', [BookingStatus::Assigned, BookingStatus::Accepted, BookingStatus::InProgress])
                ->count()),
            Stat::make('Total Revenue', '₹'.number_format((float) $totalRevenue, 2)),
            Stat::make('Painters', User::query()->where('role', UserRole::Painter)->count()),
        ];
    }
}
