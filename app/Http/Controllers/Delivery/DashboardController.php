<?php

namespace App\Http\Controllers\Delivery;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $agentId = auth()->id();

        $deliveryLabels = [];
        $deliveryData = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $deliveryLabels[] = $day->format('D');
            $deliveryData[] = Order::query()
                ->where('delivery_agent_id', $agentId)
                ->where('order_status', OrderStatus::Delivered)
                ->whereDate('delivered_at', $day)
                ->count();
        }

        return view('delivery.dashboard', [
            'activeDeliveries' => Order::query()
                ->where('delivery_agent_id', $agentId)
                ->whereNotIn('order_status', [OrderStatus::Delivered, OrderStatus::Cancelled])
                ->count(),
            'completedDeliveries' => Order::query()
                ->where('delivery_agent_id', $agentId)
                ->where('order_status', OrderStatus::Delivered)
                ->count(),
            'codCollected' => (float) Order::query()
                ->where('delivery_agent_id', $agentId)
                ->where('payment_method', PaymentMethod::Cod)
                ->where('payment_status', \App\Enums\PaymentStatus::Paid)
                ->sum('total_amount'),
            'chartDeliveries' => ['labels' => $deliveryLabels, 'data' => $deliveryData],
            'recentDeliveries' => Order::query()
                ->with('customer')
                ->where('delivery_agent_id', $agentId)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
