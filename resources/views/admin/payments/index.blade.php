@extends('admin.layout')

@section('title', 'Payments')

@section('content')
<div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>₹{{ number_format($summary['total_collected'], 0) }}</h3>
                <p>Total Collected</p>
            </div>
            <div class="icon"><i class="fas fa-rupee-sign"></i></div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $summary['online_paid'] }}</h3>
                <p>Online Paid Orders</p>
            </div>
            <div class="icon"><i class="fas fa-credit-card"></i></div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $summary['cod_pending'] }}</h3>
                <p>COD Pending</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-credit-card mr-1"></i> Payments & Collections</h3>
    </div>
    <div class="card-body pb-0">
        <form method="GET" class="filter-bar">
            <select name="payment_method" class="form-control form-control-sm filter-select">
                <option value="">All methods</option>
                @foreach(\App\Enums\PaymentMethod::cases() as $method)
                    <option value="{{ $method->value }}" @selected(request('payment_method') === $method->value)>{{ $method->label() }}</option>
                @endforeach
            </select>
            <select name="payment_status" class="form-control form-control-sm filter-select">
                <option value="">All statuses</option>
                @foreach(\App\Enums\PaymentStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('payment_status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-filter"></i> Filter</button>
            @if(request()->hasAny(['payment_method', 'payment_status']))
                <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-default">Clear</a>
            @endif
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Method</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th>Partner</th>
                    <th>Paid At</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td>{{ $order->customer->name }}</td>
                        <td><span class="badge badge-gray">{{ $order->payment_method->label() }}</span></td>
                        <td><strong>₹{{ number_format($order->grandTotal(), 2) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ $order->payment_status->value === 'paid' ? 'green' : 'yellow' }}">
                                {{ $order->payment_status->label() }}
                            </span>
                        </td>
                        <td>{{ $order->deliveryAgent?->name ?? '—' }}</td>
                        <td class="text-sm text-muted">
                            @if($order->payment_status->value === 'paid')
                                {{ $order->payment?->paid_at?->format('d M Y H:i') ?? $order->delivered_at?->format('d M Y H:i') ?? '—' }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="actions text-right">
                            <a href="{{ route('admin.payments.show', $order) }}" class="btn btn-xs btn-info" title="Payment details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No payment records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="card-footer clearfix">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
