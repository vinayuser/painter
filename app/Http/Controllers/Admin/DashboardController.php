<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\VendorPackingStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PainterBooking;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $revenueToday = $this->revenueForDay($today);
        $revenueMonth = $this->revenueSince($monthStart);
        $revenueTotal = $this->totalRevenue();

        $ordersToday = Order::query()->whereDate('created_at', $today)->count();
        $ordersMonth = Order::query()->where('created_at', '>=', $monthStart)->count();

        return view('admin.dashboard', [
            'stats' => [
                'customers' => User::query()->where('role', UserRole::Customer)->count(),
                'vendors' => User::query()->where('role', UserRole::Vendor)->where('is_active', true)->count(),
                'delivery_agents' => User::query()->where('role', UserRole::DeliveryAgent)->where('is_active', true)->count(),
                'products' => Product::query()->where('is_active', true)->count(),
                'pending_orders' => Order::query()->where('order_status', OrderStatus::Pending)->count(),
                'active_orders' => Order::query()
                    ->whereNotIn('order_status', [OrderStatus::Delivered, OrderStatus::Cancelled])
                    ->count(),
                'awaiting_packing' => Order::query()
                    ->where('vendor_packing_status', VendorPackingStatus::Pending)
                    ->whereNotIn('order_status', [OrderStatus::Delivered, OrderStatus::Cancelled])
                    ->count(),
                'delivered_orders' => Order::query()->where('order_status', OrderStatus::Delivered)->count(),
                'active_bookings' => PainterBooking::query()
                    ->whereIn('status', [BookingStatus::Assigned, BookingStatus::Accepted, BookingStatus::InProgress])
                    ->count(),
                'revenue_today' => $revenueToday,
                'revenue_month' => $revenueMonth,
                'revenue_total' => $revenueTotal,
                'orders_today' => $ordersToday,
                'orders_month' => $ordersMonth,
                'painters' => User::query()->where('role', UserRole::Painter)->count(),
                'cod_orders' => Order::query()->where('payment_method', PaymentMethod::Cod)->count(),
                'online_orders' => Order::query()->where('payment_method', PaymentMethod::Online)->count(),
            ],
            'chartData' => [
                'revenue' => $this->last7DaysRevenue(),
                'orders' => $this->last7DaysOrders(),
                'orderStatus' => $this->orderStatusBreakdown(),
                'paymentMethods' => $this->paymentMethodBreakdown(),
            ],
            'topVendors' => Order::query()
                ->select('vendor_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total_amount) as revenue'))
                ->whereNotNull('vendor_id')
                ->groupBy('vendor_id')
                ->orderByDesc('revenue')
                ->limit(5)
                ->with('vendor')
                ->get(),
            'recent_orders' => Order::query()
                ->with(['customer', 'vendor'])
                ->latest()
                ->limit(8)
                ->get(),
            'recent_bookings' => PainterBooking::query()
                ->with('customer')
                ->latest()
                ->limit(5)
                ->get(),
            'featured_products' => Product::query()
                ->with(['category', 'vendor', 'images'])
                ->where('is_featured', true)
                ->latest()
                ->limit(12)
                ->get(),
            'featured_count' => Product::query()->where('is_featured', true)->count(),
        ]);
    }

    private function revenueForDay(\DateTimeInterface $day): float
    {
        $date = $day->format('Y-m-d');

        $online = (float) Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->whereDate('paid_at', $date)
            ->sum('amount');

        $cod = (float) Order::query()
            ->where('payment_method', PaymentMethod::Cod)
            ->where('payment_status', PaymentStatus::Paid)
            ->whereDate('delivered_at', $date)
            ->sum('total_amount');

        return $online + $cod;
    }

    private function revenueSince(\DateTimeInterface $since): float
    {
        $online = (float) Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->where('paid_at', '>=', $since)
            ->sum('amount');

        $cod = (float) Order::query()
            ->where('payment_method', PaymentMethod::Cod)
            ->where('payment_status', PaymentStatus::Paid)
            ->where('delivered_at', '>=', $since)
            ->sum('total_amount');

        return $online + $cod;
    }

    private function totalRevenue(): float
    {
        $online = (float) Payment::query()->where('status', PaymentStatus::Paid)->sum('amount');
        $cod = (float) Order::query()
            ->where('payment_method', PaymentMethod::Cod)
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total_amount');

        return $online + $cod;
    }

    private function last7DaysRevenue(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $labels[] = $day->format('D');
            $data[] = round($this->revenueForDay($day), 2);
        }

        return compact('labels', 'data');
    }

    private function last7DaysOrders(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = $day->format('D');
            $data[] = Order::query()->whereDate('created_at', $day)->count();
        }

        return compact('labels', 'data');
    }

    private function orderStatusBreakdown(): array
    {
        $rows = Order::query()
            ->select('order_status', DB::raw('COUNT(*) as total'))
            ->groupBy('order_status')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => $this->enumLabel($r->order_status, OrderStatus::class))->values()->all(),
            'data' => $rows->pluck('total')->map(fn ($v) => (int) $v)->values()->all(),
        ];
    }

    private function paymentMethodBreakdown(): array
    {
        $rows = Order::query()
            ->select('payment_method', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_method')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => $this->enumLabel($r->payment_method, PaymentMethod::class))->values()->all(),
            'data' => $rows->pluck('total')->map(fn ($v) => (int) $v)->values()->all(),
        ];
    }

    /**
     * @param  class-string<OrderStatus|PaymentMethod>  $enumClass
     */
    private function enumLabel(mixed $value, string $enumClass): string
    {
        if ($value instanceof $enumClass) {
            return $value->label();
        }

        return $enumClass::from($value)->label();
    }
}
