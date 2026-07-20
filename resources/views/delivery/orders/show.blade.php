@extends('delivery.layout')

@section('title', 'Delivery '.$order->order_number)

@section('content')
<div class="page-header">
    <h1>Delivery: {{ $order->order_number }}</h1>
    <a href="{{ route('delivery.orders.index') }}" class="btn btn-secondary">Back</a>
</div>

@include('partials.order-timers', ['order' => $order])

<div class="card" style="margin-bottom:20px;">
    <h3 style="margin-bottom:12px;">Delivery Details</h3>
    <div class="form-row">
        <div class="form-group"><label>Customer</label><input type="text" value="{{ $order->customer->name }}" disabled></div>
        <div class="form-group"><label>Phone</label><input type="text" value="{{ $order->shipping_phone ?? $order->customer->phone }}" disabled></div>
    </div>
    <div class="form-row">
        <div class="form-group"><label>Vendor</label><input type="text" value="{{ $order->vendor?->business_name ?? $order->vendor?->name ?? 'Platform' }}" disabled></div>
        <div class="form-group"><label>Payment</label><input type="text" value="{{ $order->payment_method->label() }} — {{ $order->payment_status->label() }}" disabled></div>
    </div>
    <div class="form-group"><label>Shipping Address</label><textarea disabled>{{ $order->shipping_address }}</textarea></div>
</div>

<div class="card" style="margin-bottom:20px;">
    <h3 style="margin-bottom:10px;">Items ({{ $order->items->count() }})</h3>
    <table>
        <thead><tr><th>Product</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
            <tr><td colspan="2" style="text-align:right;">Products subtotal</td><td>₹{{ number_format($order->total_amount, 2) }}</td></tr>
            <tr><td colspan="2" style="text-align:right;">Your delivery fee</td><td style="color:var(--success);font-weight:600;">₹{{ number_format($order->delivery_charge, 2) }}</td></tr>
            <tr><td colspan="2" style="text-align:right;font-weight:600;">Grand total</td><td style="font-weight:600;">₹{{ number_format($order->grandTotal(), 2) }}</td></tr>
        </tbody>
    </table>
</div>

@if($order->order_status->value !== 'delivered')
<div class="card">
    <form method="POST" action="{{ route('delivery.orders.deliver', $order) }}" enctype="multipart/form-data" onsubmit="return confirm('Confirm delivery?{{ $order->amountToCollect() > 0 ? ' Collect ₹'.number_format($order->amountToCollect(), 2).' from customer.' : '' }}')">
        @csrf
        <div class="form-group">
            <label>Delivery Proof (optional photo)</label>
            <input type="file" name="delivery_proof" accept="image/*">
        </div>
        @if($order->amountToCollect() > 0)
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px;margin-bottom:14px;">
                <p style="font-weight:600;color:#854d0e;margin-bottom:6px;">💵 Collect from customer</p>
                <p style="font-size:22px;font-weight:700;color:#854d0e;">₹{{ number_format($order->amountToCollect(), 2) }}</p>
                @if($order->payment_method->value === 'online' && $order->payment_status->value === 'paid')
                    <p style="font-size:12px;color:#854d0e;margin-top:4px;">Products already paid online. Collect delivery fee only (₹{{ number_format($order->delivery_charge, 2) }}).</p>
                @elseif($order->payment_method->value === 'cod')
                    <p style="font-size:12px;color:#854d0e;margin-top:4px;">Full COD: products ₹{{ number_format($order->total_amount, 2) }} + delivery ₹{{ number_format($order->delivery_charge, 2) }}</p>
                @endif
            </div>
        @endif
        <button type="submit" class="btn btn-primary">Mark as Delivered (Stop Timer)</button>
    </form>
</div>
@endif
@endsection
