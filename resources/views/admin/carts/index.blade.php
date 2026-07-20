@extends('admin.layout')

@section('title', 'Abandoned Carts')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-shopping-basket mr-2"></i>Abandoned Carts</h1>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-shopping-basket"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active Carts</span>
                <span class="info-box-number">{{ $stats['unique_carts'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-cubes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Items</span>
                <span class="info-box-number">{{ $stats['total_items'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-rupee-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Est. Cart Value</span>
                <span class="info-box-number">₹{{ number_format($stats['estimated_value'], 0) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filter-bar mb-0 w-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer..." class="form-control form-control-sm">
            <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cartItems as $item)
                    <tr>
                        <td>
                            @if($item->user)
                                <a href="{{ route('admin.users.show', $item->user) }}">{{ $item->user->name }}</a>
                            @else — @endif
                        </td>
                        <td>{{ $item->user?->phone ?? '—' }}</td>
                        <td>{{ $item->product?->name ?? 'Product #'.$item->product_id }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ number_format($item->product?->price ?? 0, 2) }}</td>
                        <td>₹{{ number_format(($item->product?->price ?? 0) * $item->quantity, 2) }}</td>
                        <td class="text-muted text-sm">{{ $item->updated_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No items in customer carts.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cartItems->hasPages())
        <div class="card-footer">{{ $cartItems->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
