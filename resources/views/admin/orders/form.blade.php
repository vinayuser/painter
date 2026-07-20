@extends('admin.layout')

@section('title', 'Order '.$order->order_number)

@section('content')
<div class="page-header">
    <h1>Order: {{ $order->order_number }}</h1>
    <div>
        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-default">Back</a>
    </div>
</div>

@include('partials.order-timers', ['order' => $order])

<div class="card" style="margin-bottom:20px;">
    <h3 style="margin-bottom:12px;">Order Summary</h3>
    <div class="form-row">
        <div class="form-group"><label>Customer</label><input type="text" value="{{ $order->customer->name }} ({{ $order->customer->phone ?? $order->shipping_phone }})" disabled></div>
        <div class="form-group"><label>Vendor</label><input type="text" value="{{ $order->vendor?->business_name ?? $order->vendor?->name ?? 'Platform Store' }}" disabled></div>
    </div>
    <div class="form-row">
        <div class="form-group"><label>Products Total</label><input type="text" value="₹{{ number_format($order->total_amount, 2) }}" disabled></div>
        <div class="form-group"><label>Delivery Charge</label><input type="text" value="₹{{ number_format($order->delivery_charge, 2) }}" disabled></div>
    </div>
    <div class="form-row">
        <div class="form-group"><label>Grand Total (customer pays)</label><input type="text" value="₹{{ number_format($order->grandTotal(), 2) }}" disabled style="font-weight:700;"></div>
        <div class="form-group"><label>Payment Method</label><input type="text" value="{{ $order->payment_method->label() }}" disabled></div>
    </div>
    <div class="form-row">
        <div class="form-group"><label>Delivery Partner</label><input type="text" value="{{ $order->deliveryAgent?->name ?? 'Not assigned' }}" disabled></div>
        <div class="form-group"><label>Amount delivery partner collects</label><input type="text" value="₹{{ number_format($order->amountToCollect(), 2) }}" disabled></div>
    </div>
    <div class="form-row">
        <div class="form-group"><label>Order Created</label><input type="text" value="{{ $order->created_at->format('M d, Y H:i:s') }}" disabled></div>
        <div class="form-group"><label>Packed At</label><input type="text" value="{{ $order->packed_at?->format('M d, Y H:i:s') ?? 'Not yet packed' }}" disabled></div>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <h3 style="margin-bottom:10px;">Order Items</h3>
    <table>
        <thead>
            <tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                    <td>₹{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.orders.update', $order) }}" id="orderForm">
        @csrf @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label>Payment Status</label>
                <select name="payment_status">
                    @foreach($paymentStatuses as $status)
                        <option value="{{ $status->value }}" @selected(old('payment_status', $order->payment_status->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Order Status</label>
                <select name="order_status">
                    @foreach($orderStatuses as $status)
                        <option value="{{ $status->value }}" @selected(old('order_status', $order->order_status->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Delivery Status</label>
                <select name="delivery_status">
                    @foreach($deliveryStatuses as $status)
                        <option value="{{ $status->value }}" @selected(old('delivery_status', $order->delivery_status->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Delivery Partner</label>
                <select name="delivery_agent_id" id="deliveryAgentSelect">
                    <option value="">-- None --</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" @selected(old('delivery_agent_id', $order->delivery_agent_id) == $agent->id)>{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Delivery Charge (₹) <span style="color:var(--text-muted);font-weight:400;">— required when partner assigned</span></label>
                <input type="number" step="0.01" min="0" name="delivery_charge" id="deliveryChargeInput"
                    value="{{ old('delivery_charge', $order->delivery_charge) }}"
                    placeholder="e.g. 50.00">
                <p style="font-size:12px;color:var(--text-muted);margin-top:6px;">
                    Delivery partner earns this fee. Customer grand total:
                    <strong id="grandTotalPreview">₹{{ number_format($order->grandTotal(), 2) }}</strong>
                </p>
            </div>
            <div class="form-group">
                <label>Collect from customer (COD / delivery fee)</label>
                <input type="text" id="collectPreview" value="₹{{ number_format($order->amountToCollect(), 2) }}" disabled>
            </div>
        </div>
        <div class="form-group">
            <label>Shipping Address</label>
            <textarea name="shipping_address" required>{{ old('shipping_address', $order->shipping_address) }}</textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Shipping Phone</label>
                <input type="text" name="shipping_phone" value="{{ old('shipping_phone', $order->shipping_phone) }}">
            </div>
            <div class="form-group">
                <label>Customer Notes</label>
                <input type="text" value="{{ $order->notes ?? 'None' }}" disabled>
            </div>
        </div>
        <div class="form-group">
            <label>Admin Notes</label>
            <textarea name="notes">{{ old('notes', $order->notes) }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Order</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const productsTotal = {{ (float) $order->total_amount }};
    const isCod = {{ $order->payment_method->value === 'cod' ? 'true' : 'false' }};
    const isPaidOnline = {{ $order->payment_method->value === 'online' && $order->payment_status->value === 'paid' ? 'true' : 'false' }};
    const chargeInput = document.getElementById('deliveryChargeInput');
    const grandEl = document.getElementById('grandTotalPreview');
    const collectEl = document.getElementById('collectPreview');

    function updateTotals() {
        const charge = parseFloat(chargeInput.value) || 0;
        const grand = productsTotal + charge;
        grandEl.textContent = '₹' + grand.toFixed(2);
        let collect = grand;
        if (!isCod && isPaidOnline) collect = charge;
        collectEl.value = '₹' + collect.toFixed(2);
    }

    chargeInput.addEventListener('input', updateTotals);
})();
</script>
@endpush
