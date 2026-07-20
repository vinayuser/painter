<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from') ?? now()->subDays(30)->startOfDay();
        $to = $request->date('to') ?? now()->endOfDay();

        $orders = Order::query()->whereBetween('created_at', [$from, $to]);

        $onlineRevenue = (float) Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount');

        $codRevenue = (float) Order::query()
            ->where('payment_method', PaymentMethod::Cod)
            ->where('payment_status', PaymentStatus::Paid)
            ->whereBetween('delivered_at', [$from, $to])
            ->sum('total_amount');

        $stats = [
            'total_orders' => (clone $orders)->count(),
            'delivered' => (clone $orders)->where('order_status', OrderStatus::Delivered)->count(),
            'cancelled' => (clone $orders)->where('order_status', OrderStatus::Cancelled)->count(),
            'revenue' => $onlineRevenue + $codRevenue,
            'online_revenue' => $onlineRevenue,
            'cod_revenue' => $codRevenue,
            'avg_order_value' => (clone $orders)->avg('total_amount') ?? 0,
        ];

        $dailyRevenue = Order::query()
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as orders'), DB::raw('SUM(total_amount) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.subtotal) as revenue'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        return view('admin.reports.index', compact('stats', 'dailyRevenue', 'topProducts', 'from', 'to'));
    }
}
