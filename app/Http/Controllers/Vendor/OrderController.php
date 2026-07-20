<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\VendorOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected VendorOrderService $vendorOrderService,
    ) {}

    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'active');

        $orders = Order::query()
            ->with(['customer', 'deliveryAgent', 'items'])
            ->where('vendor_id', auth()->id())
            ->when($filter === 'active', fn ($q) => $q->whereNotIn('order_status', [
                \App\Enums\OrderStatus::Delivered,
                \App\Enums\OrderStatus::Cancelled,
            ]))
            ->when($filter === 'past', fn ($q) => $q->where('order_status', \App\Enums\OrderStatus::Delivered))
            ->latest()
            ->paginate(15);

        return view('vendor.orders.index', compact('orders', 'filter'));
    }

    public function show(Order $order): View
    {
        if ($order->vendor_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items', 'customer', 'deliveryAgent', 'payment']);

        return view('vendor.orders.show', compact('order'));
    }

    public function markPacked(Order $order): RedirectResponse
    {
        if ($order->vendor_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->vendorOrderService->markPacked($order);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Order marked as packed. Packing timer stopped.');
    }
}
