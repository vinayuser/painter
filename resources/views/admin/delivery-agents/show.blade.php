@extends('admin.layout')

@section('title', $agent->name.' — Deliveries')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.delivery-agents.index') }}">Delivery Partners</a></li>
    <li class="breadcrumb-item active">{{ $agent->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body profile-box">
                <div class="profile-avatar">{{ strtoupper(substr($agent->name, 0, 1)) }}</div>
                <h4 class="mb-1">{{ $agent->name }}</h4>
                @if($agent->is_active)
                    <span class="badge badge-green">Active</span>
                @else
                    <span class="badge badge-red">Inactive</span>
                @endif
            </div>
            <div class="card-footer">
                <dl class="row mb-0 text-sm">
                    <dt class="col-5">Email</dt><dd class="col-7">{{ $agent->email }}</dd>
                    <dt class="col-5">Phone</dt><dd class="col-7">{{ $agent->phone ?? '—' }}</dd>
                    <dt class="col-5">Vehicle</dt><dd class="col-7">{{ $agent->vehicle_number ?? '—' }}</dd>
                    <dt class="col-5">License</dt><dd class="col-7">{{ $agent->license_number ?? '—' }}</dd>
                </dl>
            </div>
        </div>
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Completed Deliveries</span>
                <span class="info-box-number">{{ $pastDeliveries->total() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history mr-1"></i> Past Deliveries</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.delivery-agents.edit', $agent) }}" class="btn btn-sm btn-default">
                        <i class="fas fa-edit"></i> Edit Partner
                    </a>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Vendor</th>
                            <th>Products</th>
                            <th>Delivery</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Delivered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pastDeliveries as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                <td>{{ $order->customer->name }}</td>
                                <td>{{ $order->vendor?->business_name ?? $order->vendor?->name ?? 'Platform' }}</td>
                                <td>₹{{ number_format($order->total_amount, 2) }}</td>
                                <td>₹{{ number_format($order->delivery_charge, 2) }}</td>
                                <td><strong>₹{{ number_format($order->grandTotal(), 2) }}</strong></td>
                                <td><span class="badge badge-gray">{{ $order->payment_method->label() }}</span></td>
                                <td class="text-sm text-muted">{{ $order->delivered_at?->format('d M Y, H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No completed deliveries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pastDeliveries->hasPages())
                <div class="card-footer">{{ $pastDeliveries->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
