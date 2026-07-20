@extends('admin.layout')

@section('title', 'Payment — '.$order->order_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
    <li class="breadcrumb-item active">{{ $order->order_number }}</li>
@endsection

@section('content')
<div class="page-header">
    <h1>Payment: {{ $order->order_number }}</h1>
    <div>
        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info"><i class="fas fa-box"></i> View Order</a>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-default">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Payment Summary</h3></div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-6">Payment Method</dt><dd class="col-6"><span class="badge badge-gray">{{ $order->payment_method->label() }}</span></dd>
                    <dt class="col-6">Payment Status</dt><dd class="col-6"><span class="badge badge-{{ $order->payment_status->value === 'paid' ? 'green' : 'yellow' }}">{{ $order->payment_status->label() }}</span></dd>
                    <dt class="col-6">Products Total</dt><dd class="col-6">₹{{ number_format($order->total_amount, 2) }}</dd>
                    <dt class="col-6">Delivery Charge</dt><dd class="col-6">₹{{ number_format($order->delivery_charge, 2) }}</dd>
                    <dt class="col-6">Grand Total</dt><dd class="col-6"><strong class="text-success">₹{{ number_format($order->grandTotal(), 2) }}</strong></dd>
                    <dt class="col-6">Collect (delivery)</dt><dd class="col-6">₹{{ number_format($order->amountToCollect(), 2) }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Razorpay / Transaction</h3></div>
            <div class="card-body">
                @if($order->payment_method->value === 'online')
                    @if($order->payment)
                        <dl class="row mb-0 text-sm">
                            <dt class="col-5">Razorpay Order</dt><dd class="col-7"><code>{{ $order->payment->razorpay_order_id ?? '—' }}</code></dd>
                            <dt class="col-5">Payment ID</dt><dd class="col-7"><code>{{ $order->payment->razorpay_payment_id ?? '—' }}</code></dd>
                            <dt class="col-5">Amount</dt><dd class="col-7">₹{{ number_format($order->payment->amount, 2) }}</dd>
                            <dt class="col-5">Paid At</dt><dd class="col-7">{{ $order->payment->paid_at?->format('d M Y, H:i') ?? '—' }}</dd>
                        </dl>
                    @else
                        <p class="text-muted mb-0">Online payment not yet completed.</p>
                    @endif
                @else
                    <p class="mb-2"><i class="fas fa-money-bill-wave text-success"></i> Cash on Delivery</p>
                    <p class="mb-0 text-sm text-muted">Payment collected on delivery. Delivered at: {{ $order->delivered_at?->format('d M Y, H:i') ?? 'Pending' }}</p>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Customer</h3></div>
            <div class="card-body">
                <p class="mb-0">
                    <a href="{{ route('admin.customers.show', $order->customer) }}">{{ $order->customer->name }}</a><br>
                    <small class="text-muted">{{ $order->customer->phone }} · {{ $order->customer->email }}</small>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
