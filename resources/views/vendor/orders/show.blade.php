@extends('vendor.layout')

@section('title', 'Order '.$order->order_number)

@section('content')
<div class="page-header">
    <h1>Order: {{ $order->order_number }}</h1>
    <a href="{{ route('vendor.orders.index') }}" class="btn btn-secondary">Back</a>
</div>

@include('partials.order-timers', ['order' => $order])

<div class="card" style="margin-bottom:20px;">
    <h3 style="margin-bottom:12px;">Order Details</h3>
    <div class="form-row">
        <div class="form-group"><label>Customer</label><input type="text" value="{{ $order->customer->name }}" disabled></div>
        <div class="form-group"><label>Phone</label><input type="text" value="{{ $order->shipping_phone ?? $order->customer->phone }}" disabled></div>
    </div>
    <div class="form-row">
        <div class="form-group"><label>Payment Method</label><input type="text" value="{{ $order->payment_method->label() }}" disabled></div>
        <div class="form-group"><label>Payment Status</label><input type="text" value="{{ $order->payment_status->label() }}" disabled></div>
    </div>
    <div class="form-group"><label>Shipping Address</label><textarea disabled>{{ $order->shipping_address }}</textarea></div>
    @if($order->notes)<div class="form-group"><label>Notes</label><textarea disabled>{{ $order->notes }}</textarea></div>@endif
</div>

<div class="card" style="margin-bottom:20px;">
    <h3 style="margin-bottom:10px;">Items</h3>
    <table>
        <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                    <td>₹{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
            <tr><td colspan="3" style="text-align:right;font-weight:600;">Total</td><td style="font-weight:600;">₹{{ number_format($order->total_amount, 2) }}</td></tr>
        </tbody>
    </table>
</div>

@if($order->vendor_packing_status->value === 'pending')
<div class="card">
    <form method="POST" action="{{ route('vendor.orders.pack', $order) }}" onsubmit="return confirm('Mark this order as packed?')">
        @csrf
        <p style="margin-bottom:12px;">Confirm that all items are packed and ready for pickup by the delivery partner.</p>
        <button type="submit" class="btn btn-primary">Mark as Packed (Stop Timer)</button>
    </form>
</div>
@endif
@endsection
