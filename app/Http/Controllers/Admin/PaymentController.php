<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with(['customer', 'deliveryAgent', 'payment', 'vendor'])
            ->when($request->payment_method, fn ($q, $m) => $q->where('payment_method', $m))
            ->when($request->payment_status, fn ($q, $s) => $q->where('payment_status', $s))
            ->latest()
            ->paginate(15);

        $summary = [
            'total_collected' => (float) Order::query()->where('payment_status', PaymentStatus::Paid)->get()->sum(fn (Order $o) => $o->grandTotal()),
            'cod_pending' => Order::query()->where('payment_method', PaymentMethod::Cod)->where('payment_status', PaymentStatus::Pending)->count(),
            'online_paid' => Order::query()->where('payment_method', PaymentMethod::Online)->where('payment_status', PaymentStatus::Paid)->count(),
        ];

        return view('admin.payments.index', compact('orders', 'summary'));
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'deliveryAgent', 'vendor', 'payment', 'items']);

        return view('admin.payments.show', compact('order'));
    }
}
