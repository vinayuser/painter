@extends('admin.layout')

@section('title', $vendor->business_name ?? $vendor->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.vendors.index') }}">Vendors</a></li>
    <li class="breadcrumb-item active">{{ $vendor->business_name ?? $vendor->name }}</li>
@endsection

@section('content')
<div class="page-header">
    <h1>{{ $vendor->business_name ?? $vendor->name }}</h1>
    <div>
        <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
        <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-default">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card card-success card-outline">
            <div class="card-body profile-box">
                <div class="profile-avatar" style="color:#28a745;">{{ strtoupper(substr($vendor->name, 0, 1)) }}</div>
                <h4>{{ $vendor->business_name ?? $vendor->name }}</h4>
                <p class="text-muted">{{ $vendor->name }}</p>
                @if($vendor->is_active)<span class="badge badge-green">Active</span>@else<span class="badge badge-red">Inactive</span>@endif
            </div>
            <div class="card-footer">
                <dl class="row mb-0 text-sm">
                    <dt class="col-5">Email</dt><dd class="col-7">{{ $vendor->email }}</dd>
                    <dt class="col-5">Phone</dt><dd class="col-7">{{ $vendor->phone ?? '—' }}</dd>
                    <dt class="col-5">Address</dt><dd class="col-7">{{ $vendor->address ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row mb-3">
            <div class="col-4"><div class="stat-tile"><strong>{{ $stats['products'] }}</strong><span>Products</span></div></div>
            <div class="col-4"><div class="stat-tile"><strong>{{ $stats['orders'] }}</strong><span>Orders</span></div></div>
            <div class="col-4"><div class="stat-tile"><strong>₹{{ number_format($stats['revenue'], 0) }}</strong><span>Revenue</span></div></div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-fill-drip mr-1"></i> Products</h3></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Product</th><th>Price</th><th>Stock</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($vendor->vendorProducts as $product)
                            <tr>
                                <td><a href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a></td>
                                <td>₹{{ number_format($product->price, 2) }}</td>
                                <td>{{ $product->stock_quantity }}</td>
                                <td>{{ $product->is_active ? 'Active' : 'Inactive' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No products listed.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-shopping-cart mr-1"></i> Recent Orders</h3></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($vendor->vendorOrders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.edit', $order) }}">{{ $order->order_number }}</a></td>
                                <td>{{ $order->customer->name }}</td>
                                <td>₹{{ number_format($order->total_amount, 0) }}</td>
                                <td><span class="badge badge-blue">{{ $order->order_status->label() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
