@extends('admin.layout')

@section('title', 'Customer Addresses')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-map-marker-alt mr-2"></i>Customer Addresses</h1>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filter-bar mb-0 w-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer, city, pincode..." class="form-control form-control-sm">
            <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Label</th>
                    <th>Recipient</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>Pincode</th>
                    <th>Default</th>
                </tr>
            </thead>
            <tbody>
                @forelse($addresses as $address)
                    <tr>
                        <td>
                            @if($address->user)
                                <a href="{{ route('admin.users.show', $address->user) }}">{{ $address->user->name }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="badge badge-gray">{{ $address->label }}</span></td>
                        <td>{{ $address->recipient_name }}</td>
                        <td>{{ $address->phone }}</td>
                        <td>{{ $address->address_line1 }}{{ $address->address_line2 ? ', '.$address->address_line2 : '' }}</td>
                        <td>{{ $address->city }}, {{ $address->state }}</td>
                        <td>{{ $address->pincode }}</td>
                        <td>
                            @if($address->is_default)
                                <span class="badge badge-green"><i class="fas fa-star"></i> Default</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No saved addresses yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($addresses->hasPages())
        <div class="card-footer">{{ $addresses->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
