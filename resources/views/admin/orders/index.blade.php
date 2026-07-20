@extends('admin.layout')

@section('title', 'Orders')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-shopping-cart mr-1"></i> Orders</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-bar">
            <select name="status" class="form-control form-control-sm" style="width:auto;">
                <option value="">All Statuses</option>
                @foreach(\App\Enums\OrderStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <select name="payment_method" class="form-control form-control-sm" style="width:auto;">
                <option value="">All Payment Methods</option>
                @foreach(\App\Enums\PaymentMethod::cases() as $method)
                    <option value="{{ $method->value }}" @selected(request('payment_method') === $method->value)>{{ $method->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-filter"></i> Filter</button>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Vendor</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Packing</th>
                        <th>Status</th>
                        <th>Delivery</th>
                        <th>Agent</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td><a href="{{ route('admin.users.show', $order->customer) }}">{{ $order->customer->name }}</a></td>
                            <td>{{ $order->vendor?->business_name ?? $order->vendor?->name ?? 'Platform' }}</td>
                            <td>₹{{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <span class="badge badge-gray">{{ $order->payment_method->label() }}</span>
                                <span class="badge {{ $order->payment_status->value === 'paid' ? 'badge-green' : 'badge-yellow' }}">{{ $order->payment_status->label() }}</span>
                            </td>
                            <td><span class="badge {{ $order->vendor_packing_status->value === 'packed' ? 'badge-green' : 'badge-yellow' }}">{{ $order->vendor_packing_status->label() }}</span></td>
                            <td><span class="badge badge-blue">{{ $order->order_status->label() }}</span></td>
                            <td><span class="badge badge-yellow">{{ $order->delivery_status->label() }}</span></td>
                            <td>{{ $order->deliveryAgent->name ?? '—' }}</td>
                            <td class="text-sm">{{ $order->created_at->format('M d, H:i') }}</td>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-xs btn-default"><i class="fas fa-edit"></i></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $orders->withQueryString()->links() }}</div>
</div>
@endsection
