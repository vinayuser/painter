@extends('admin.layout')

@section('title', 'Order '.$order->order_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
    <li class="breadcrumb-item active">{{ $order->order_number }}</li>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:.5rem;">
    <div class="d-flex flex-wrap align-items-center" style="gap:.4rem;">
        <span class="badge badge-blue">{{ $order->order_status->label() }}</span>
        <span class="badge badge-{{ $order->payment_status->value === 'paid' ? 'green' : 'yellow' }}">{{ $order->payment_status->label() }}</span>
        <span class="badge badge-{{ $order->vendor_packing_status->value === 'packed' ? 'green' : 'yellow' }}">{{ $order->vendor_packing_status->label() }}</span>
        <span class="badge badge-gray">{{ $order->delivery_status->label() }}</span>
        <span class="text-muted text-sm ml-1">{{ $order->created_at->format('d M Y, H:i') }}</span>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Manage</a>
        <a href="{{ route('admin.payments.show', $order) }}" class="btn btn-sm btn-info"><i class="fas fa-credit-card"></i> Payment</a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-default">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="info-box order-timer-box {{ $order->isPackingTimerActive() ? 'timer-active' : ($order->vendor_packing_status->value === 'packed' ? 'timer-done' : 'timer-expired') }}"
             data-timer="packing"
             data-deadline="{{ $order->packing_deadline_at?->toIso8601String() }}"
             data-active="{{ $order->isPackingTimerActive() ? '1' : '0' }}">
            <span class="info-box-icon {{ $order->vendor_packing_status->value === 'packed' ? 'bg-success' : ($order->isPackingTimerActive() ? 'bg-warning' : 'bg-danger') }}">
                <i class="fas fa-box-open"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Vendor Packing</span>
                <span class="info-box-number" id="packing-timer-{{ $order->id }}">
                    @if($order->vendor_packing_status->value === 'packed')
                        Packed
                    @elseif($order->isPackingTimerActive())
                        <span class="countdown">--:--</span>
                    @else
                        Expired
                    @endif
                </span>
                <span class="progress-description">
                    Deadline {{ $order->packing_deadline_at?->format('d M, H:i') ?? '—' }}
                    @if($order->packed_at) · Packed {{ $order->packed_at->format('d M, H:i') }} @endif
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-box order-timer-box {{ $order->isDeliveryTimerActive() ? 'timer-active' : ($order->order_status->value === 'delivered' ? 'timer-done' : 'timer-expired') }}"
             data-timer="delivery"
             data-deadline="{{ $order->delivery_deadline_at?->toIso8601String() }}"
             data-active="{{ $order->isDeliveryTimerActive() ? '1' : '0' }}">
            <span class="info-box-icon {{ $order->order_status->value === 'delivered' ? 'bg-success' : ($order->isDeliveryTimerActive() ? 'bg-warning' : 'bg-danger') }}">
                <i class="fas fa-shipping-fast"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Delivery</span>
                <span class="info-box-number" id="delivery-timer-{{ $order->id }}">
                    @if($order->order_status->value === 'delivered')
                        Delivered
                    @elseif($order->isDeliveryTimerActive())
                        <span class="countdown">--:--</span>
                    @else
                        Expired
                    @endif
                </span>
                <span class="progress-description">
                    Deadline {{ $order->delivery_deadline_at?->format('d M, H:i') ?? '—' }}
                    @if($order->delivered_at) · Delivered {{ $order->delivered_at->format('d M, H:i') }} @endif
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-list mr-1"></i> Items</h3>
                <div class="card-tools">
                    <span class="text-muted text-sm">{{ $order->items->count() }} item{{ $order->items->count() === 1 ? '' : 's' }}</span>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:50%;">Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td><strong>{{ $item->product_name }}</strong></td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-right">₹{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <div class="order-totals">
                    <div class="order-total-row">
                        <span>Products</span>
                        <strong>₹{{ number_format($order->total_amount, 2) }}</strong>
                    </div>
                    <div class="order-total-row">
                        <span>Delivery charge</span>
                        <strong>₹{{ number_format($order->delivery_charge, 2) }}</strong>
                    </div>
                    <div class="order-total-row order-total-grand">
                        <span>Grand total</span>
                        <strong>₹{{ number_format($order->grandTotal(), 2) }}</strong>
                    </div>
                    <div class="order-total-row text-muted">
                        <span>Collect on delivery</span>
                        <strong>₹{{ number_format($order->amountToCollect(), 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-map-marker-alt mr-1"></i> Shipping</h3>
            </div>
            <div class="card-body pt-0">
                <p class="mb-2">{{ $order->shipping_address }}</p>
                @if($order->shipping_phone)
                    <p class="mb-0 text-muted"><i class="fas fa-phone mr-1"></i>{{ $order->shipping_phone }}</p>
                @endif
                @if($order->notes)
                    <div class="callout callout-info mt-3 mb-0">
                        <strong>Notes</strong>
                        <p class="mb-0">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-outline card-primary">
            <div class="card-header border-0">
                <h3 class="card-title">Payment</h3>
            </div>
            <div class="card-body pt-0">
                <dl class="order-meta mb-0">
                    <div><dt>Method</dt><dd>{{ $order->payment_method->label() }}</dd></div>
                    <div><dt>Status</dt><dd><span class="badge badge-{{ $order->payment_status->value === 'paid' ? 'green' : 'yellow' }}">{{ $order->payment_status->label() }}</span></dd></div>
                    @if($order->payment?->razorpay_payment_id)
                        <div><dt>Razorpay</dt><dd class="text-sm"><code>{{ $order->payment->razorpay_payment_id }}</code></dd></div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">People</h3>
            </div>
            <div class="card-body pt-0">
                <div class="order-person">
                    <div class="order-person-avatar">{{ strtoupper(substr($order->customer->name, 0, 1)) }}</div>
                    <div>
                        <div class="text-muted text-xs text-uppercase">Customer</div>
                        <a href="{{ route('admin.customers.show', $order->customer) }}"><strong>{{ $order->customer->name }}</strong></a>
                        <div class="text-sm text-muted">{{ $order->customer->phone }}</div>
                    </div>
                </div>
                <div class="order-person">
                    <div class="order-person-avatar vendor">{{ strtoupper(substr($order->vendor?->business_name ?? $order->vendor?->name ?? 'P', 0, 1)) }}</div>
                    <div>
                        <div class="text-muted text-xs text-uppercase">Vendor</div>
                        @if($order->vendor)
                            <a href="{{ route('admin.vendors.show', $order->vendor) }}"><strong>{{ $order->vendor->business_name ?? $order->vendor->name }}</strong></a>
                        @else
                            <strong>Platform</strong>
                        @endif
                    </div>
                </div>
                <div class="order-person mb-0">
                    <div class="order-person-avatar agent">{{ strtoupper(substr($order->deliveryAgent?->name ?? '?', 0, 1)) }}</div>
                    <div>
                        <div class="text-muted text-xs text-uppercase">Delivery partner</div>
                        <strong>{{ $order->deliveryAgent?->name ?? 'Not assigned' }}</strong>
                        @if($order->deliveryAgent?->phone)
                            <div class="text-sm text-muted">{{ $order->deliveryAgent->phone }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">Timeline</h3>
            </div>
            <div class="card-body pt-0">
                <ul class="order-timeline">
                    <li>
                        <span class="dot bg-primary"></span>
                        <div>
                            <strong>Created</strong>
                            <div class="text-muted text-sm">{{ $order->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </li>
                    <li class="{{ $order->packed_at ? '' : 'is-pending' }}">
                        <span class="dot {{ $order->packed_at ? 'bg-success' : 'bg-secondary' }}"></span>
                        <div>
                            <strong>Packed</strong>
                            <div class="text-muted text-sm">{{ $order->packed_at?->format('d M Y, H:i') ?? 'Pending' }}</div>
                        </div>
                    </li>
                    <li class="{{ $order->delivered_at ? '' : 'is-pending' }}">
                        <span class="dot {{ $order->delivered_at ? 'bg-success' : 'bg-secondary' }}"></span>
                        <div>
                            <strong>Delivered</strong>
                            <div class="text-muted text-sm">{{ $order->delivered_at?->format('d M Y, H:i') ?? 'Pending' }}</div>
                        </div>
                    </li>
                </ul>
                @if($order->delivery_proof_path)
                    <a href="{{ asset('storage/'.$order->delivery_proof_path) }}" target="_blank" class="btn btn-sm btn-default btn-block mt-2">
                        <i class="fas fa-image"></i> View delivery proof
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-timer]').forEach(function(card) {
    if (card.dataset.active !== '1') return;
    var deadline = new Date(card.dataset.deadline);
    var countdownEl = card.querySelector('.countdown');
    if (!countdownEl) return;

    function tick() {
        var diff = Math.max(0, Math.floor((deadline - new Date()) / 1000));
        var m = String(Math.floor(diff / 60)).padStart(2, '0');
        var s = String(diff % 60).padStart(2, '0');
        countdownEl.textContent = m + ':' + s;
        if (diff <= 0) {
            countdownEl.textContent = '00:00';
            card.classList.remove('timer-active');
            card.classList.add('timer-expired');
            clearInterval(interval);
        }
    }
    tick();
    var interval = setInterval(tick, 1000);
});
</script>
@endpush
