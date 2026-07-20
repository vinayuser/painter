<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with(['customer', 'deliveryAgent', 'vendor'])
            ->when($request->status, fn ($q, $s) => $q->where('order_status', $s))
            ->when($request->payment_method, fn ($q, $m) => $q->where('payment_method', $m))
            ->latest()
            ->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'customer', 'deliveryAgent', 'vendor', 'payment']);

        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        $order->load(['items', 'customer', 'deliveryAgent', 'vendor', 'payment']);

        return view('admin.orders.form', [
            'order' => $order,
            'agents' => User::query()->where('role', UserRole::DeliveryAgent)->where('is_active', true)->get(),
            'orderStatuses' => OrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
            'deliveryStatuses' => DeliveryStatus::cases(),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'delivery_agent_id' => ['nullable', 'exists:users,id'],
            'delivery_charge' => [
                Rule::requiredIf(fn () => $request->filled('delivery_agent_id')),
                'nullable', 'numeric', 'min:0',
            ],
            'payment_status' => ['required', Rule::in(array_column(PaymentStatus::cases(), 'value'))],
            'order_status' => ['required', Rule::in(array_column(OrderStatus::cases(), 'value'))],
            'delivery_status' => ['required', Rule::in(array_column(DeliveryStatus::cases(), 'value'))],
            'shipping_address' => ['required', 'string'],
            'shipping_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        if (empty($data['delivery_agent_id'])) {
            $data['delivery_charge'] = 0;
        }

        $previousAgentId = $order->delivery_agent_id;

        if (! empty($data['delivery_agent_id']) && in_array($order->order_status, [OrderStatus::Pending, OrderStatus::Packed], true)) {
            $data['order_status'] = OrderStatus::Assigned->value;
            $data['delivery_status'] = DeliveryStatus::Pending->value;
        }

        $order->update($data);
        $order = $order->fresh(['customer', 'deliveryAgent', 'vendor']);

        if (! empty($data['delivery_agent_id']) && (int) $data['delivery_agent_id'] !== (int) $previousAgentId) {
            $this->notifications->deliveryAssigned($order);
        }

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated successfully.');
    }
}
