@extends('admin.layout')

@section('title', 'Customers')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-friends mr-1"></i> Customers</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-bar">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone..." class="form-control form-control-sm">
            <select name="verified" class="form-control form-control-sm" style="width:auto;">
                <option value="">All</option>
                <option value="1" @selected(request('verified') === '1')>Verified</option>
                <option value="0" @selected(request('verified') === '0')>Not verified</option>
            </select>
            <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-filter"></i> Filter</button>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Orders</th>
                        <th>Addresses</th>
                        <th>Bookings</th>
                        <th>Verified</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td><a href="{{ route('admin.customers.show', $customer) }}">{{ $customer->name }}</a></td>
                            <td>{{ $customer->phone ?? '—' }}</td>
                            <td>{{ $customer->email ?? '—' }}</td>
                            <td><span class="badge badge-blue">{{ $customer->orders_count }}</span></td>
                            <td>{{ $customer->addresses_count }}</td>
                            <td>{{ $customer->painter_bookings_count }}</td>
                            <td>{{ $customer->is_verified ? 'Yes' : 'No' }}</td>
                            <td class="text-sm text-muted">{{ $customer->created_at->format('d M Y') }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.users.edit', $customer) }}" class="btn btn-xs btn-default"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $customers->withQueryString()->links() }}</div>
</div>
@endsection
