<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\VendorPackingStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $vendorId = auth()->id();
        $monthStart = now()->startOfMonth();

        $revenueMonth = (float) Order::query()
            ->where('vendor_id', $vendorId)
            ->where('order_status', OrderStatus::Delivered)
            ->where('updated_at', '>=', $monthStart)
            ->sum('total_amount');

        $orderLabels = [];
        $orderData = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $orderLabels[] = $day->format('D');
            $orderData[] = Order::query()
                ->where('vendor_id', $vendorId)
                ->whereDate('created_at', $day)
                ->count();
        }

        return view('vendor.dashboard', [
            'productCount' => Product::query()->where('vendor_id', $vendorId)->count(),
            'activeOrders' => Order::query()
                ->where('vendor_id', $vendorId)
                ->whereNotIn('order_status', [OrderStatus::Delivered, OrderStatus::Cancelled])
                ->count(),
            'pendingPacking' => Order::query()
                ->where('vendor_id', $vendorId)
                ->where('vendor_packing_status', VendorPackingStatus::Pending)
                ->whereNotIn('order_status', [OrderStatus::Delivered, OrderStatus::Cancelled])
                ->count(),
            'completedOrders' => Order::query()
                ->where('vendor_id', $vendorId)
                ->where('order_status', OrderStatus::Delivered)
                ->count(),
            'revenueMonth' => $revenueMonth,
            'chartOrders' => ['labels' => $orderLabels, 'data' => $orderData],
            'paymentSplit' => Order::query()
                ->select('payment_method', DB::raw('COUNT(*) as total'))
                ->where('vendor_id', $vendorId)
                ->groupBy('payment_method')
                ->get()
                ->map(fn ($r) => [
                    'label' => $r->payment_method instanceof PaymentMethod
                        ? $r->payment_method->label()
                        : PaymentMethod::from($r->payment_method)->label(),
                    'value' => (int) $r->total,
                ]),
            'recentOrders' => Order::query()
                ->with('customer')
                ->where('vendor_id', $vendorId)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
