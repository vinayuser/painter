<?php

namespace App\Http\Controllers\Delivery;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected DeliveryService $deliveryService,
    ) {}

    public function index(Request $request): View
    {
        $filter = $request->get('filter', 'active');

        $orders = Order::query()
            ->with(['customer', 'vendor', 'items'])
            ->where('delivery_agent_id', auth()->id())
            ->when($filter === 'active', fn ($q) => $q->where('order_status', '!=', \App\Enums\OrderStatus::Delivered))
            ->when($filter === 'past', fn ($q) => $q->where('order_status', \App\Enums\OrderStatus::Delivered))
            ->latest()
            ->paginate(15);

        return view('delivery.orders.index', compact('orders', 'filter'));
    }

    public function show(Order $order): View
    {
        if ($order->delivery_agent_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items', 'customer', 'vendor', 'payment']);

        return view('delivery.orders.show', compact('order'));
    }

    public function markDelivered(Request $request, Order $order): RedirectResponse
    {
        if ($order->delivery_agent_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'delivery_proof' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('delivery_proof')) {
            $this->deliveryService->uploadProof($order, $request->file('delivery_proof'));
        }

        $this->deliveryService->updateStatus($order, DeliveryStatus::Delivered);

        return back()->with('success', 'Order marked as delivered. Delivery timer stopped.');
    }
}
