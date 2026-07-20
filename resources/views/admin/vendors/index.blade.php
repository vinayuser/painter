@extends('admin.layout')

@section('title', 'Vendors')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-store mr-1"></i> Vendors</h3>
        <div class="card-tools">
            <a href="{{ route('admin.vendors.create') }}" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Create Vendor</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-bar">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search vendors..." class="form-control form-control-sm">
            <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-search"></i> Search</button>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr><th>Name</th><th>Business</th><th>Email</th><th>Phone</th><th>Products</th><th>Orders</th><th>Active</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr>
                            <td><a href="{{ route('admin.vendors.show', $vendor) }}">{{ $vendor->name }}</a></td>
                            <td>{{ $vendor->business_name ?? '—' }}</td>
                            <td>{{ $vendor->email }}</td>
                            <td>{{ $vendor->phone ?? '—' }}</td>
                            <td><span class="badge badge-blue">{{ $vendor->vendor_products_count }}</span></td>
                            <td><span class="badge badge-gray">{{ $vendor->vendor_orders_count }}</span></td>
                            <td>{{ $vendor->is_active ? 'Yes' : 'No' }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-xs btn-default"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No vendors yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $vendors->withQueryString()->links() }}</div>
</div>
@endsection
