@extends('delivery.layout')

@section('title', $filter === 'past' ? 'Past Deliveries' : 'Active Deliveries')

@section('content')
<div class="page-header">
    <h1>{{ $filter === 'past' ? 'Past Deliveries' : 'Active Deliveries' }}</h1>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('delivery.orders.index') }}" class="btn {{ $filter !== 'past' ? 'btn-primary' : 'btn-secondary' }} btn-sm">Active</a>
        <a href="{{ route('delivery.orders.index', ['filter' => 'past']) }}" class="btn {{ $filter === 'past' ? 'btn-primary' : 'btn-secondary' }} btn-sm">Past</a>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Vendor</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer->name }}</td>
                    <td>{{ $order->vendor?->business_name ?? $order->vendor?->name ?? 'Platform' }}</td>
                    <td>₹{{ number_format($order->total_amount, 2) }}</td>
                    <td><span class="badge badge-gray">{{ $order->payment_method->label() }}</span></td>
                    <td><span class="badge badge-blue">{{ $order->order_status->label() }}</span></td>
                    <td>{{ $order->created_at->format('M d, H:i') }}</td>
                    <td><a href="{{ route('delivery.orders.show', $order) }}" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:var(--text-muted);">No deliveries assigned yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="pagination">{{ $orders->withQueryString()->links() }}</div>
</div>
@endsection
