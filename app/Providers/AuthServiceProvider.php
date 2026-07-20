<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\PainterBooking;
use App\Policies\OrderPolicy;
use App\Policies\PainterBookingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected array $policies = [
        Order::class => OrderPolicy::class,
        PainterBooking::class => PainterBookingPolicy::class,
    ];

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
