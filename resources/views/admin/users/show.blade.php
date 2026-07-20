@extends('admin.layout')

@section('title', $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')
<div class="page-header">
    <h1>{{ $user->name }}</h1>
    <div>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-default">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body profile-box">
                <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <h4>{{ $user->name }}</h4>
                <span class="badge badge-blue">{{ $user->role->label() }}</span>
                @if($user->is_active)<span class="badge badge-green">Active</span>@else<span class="badge badge-red">Inactive</span>@endif
            </div>
            <div class="card-footer">
                <dl class="row mb-0 text-sm">
                    <dt class="col-5">Email</dt><dd class="col-7">{{ $user->email ?? '—' }}</dd>
                    <dt class="col-5">Phone</dt><dd class="col-7">{{ $user->phone ?? '—' }}</dd>
                    <dt class="col-5">Joined</dt><dd class="col-7">{{ $user->created_at->format('d M Y') }}</dd>
                    @if($user->address)
                        <dt class="col-5">Address</dt><dd class="col-7">{{ $user->address }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row mb-3">
            <div class="col-4"><div class="stat-tile"><strong>{{ $user->orders->count() }}</strong><span>Orders</span></div></div>
            <div class="col-4"><div class="stat-tile"><strong>{{ $user->addresses->count() }}</strong><span>Addresses</span></div></div>
            <div class="col-4"><div class="stat-tile"><strong>{{ $user->painterBookings->count() }}</strong><span>Bookings</span></div></div>
        </div>

        @if($user->isCustomer() && $user->addresses->isNotEmpty())
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-map-marker-alt mr-1"></i> Saved Addresses</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Label</th><th>Address</th><th>Default</th></tr></thead>
                        <tbody>
                            @foreach($user->addresses as $addr)
                                <tr>
                                    <td>{{ $addr->label }}</td>
                                    <td>{{ $addr->formatShippingAddress() }}</td>
                                    <td>@if($addr->is_default)<span class="badge badge-green">Default</span>@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($user->orders->isNotEmpty())
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-shopping-cart mr-1"></i> Recent Orders</h3></div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Order #</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach($user->orders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.edit', $order) }}">{{ $order->order_number }}</a></td>
                                    <td>₹{{ number_format($order->total_amount, 0) }}</td>
                                    <td><span class="badge badge-blue">{{ $order->order_status->label() }}</span></td>
                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($user->painterBookings->isNotEmpty())
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar mr-1"></i> Painter Bookings</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Booking #</th><th>Date</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($user->painterBookings as $booking)
                                <tr>
                                    <td><a href="{{ route('admin.bookings.edit', $booking) }}">{{ $booking->booking_number }}</a></td>
                                    <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                    <td><span class="badge badge-yellow">{{ $booking->status->label() }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
