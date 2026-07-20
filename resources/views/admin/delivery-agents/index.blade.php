@extends('admin.layout')

@section('title', 'Delivery Partners')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-shipping-fast mr-1"></i> Delivery Partners</h3>
        <div class="card-tools">
            <a href="{{ route('admin.delivery-agents.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Create Partner
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-bar">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="form-control form-control-sm">
            <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-search"></i> Search</button>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Vehicle</th>
                        <th>License</th>
                        <th>Completed</th>
                        <th>Active</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                        <tr>
                            <td><strong>{{ $agent->name }}</strong></td>
                            <td>{{ $agent->email }}</td>
                            <td>{{ $agent->phone ?? '—' }}</td>
                            <td>{{ $agent->vehicle_number ?? '—' }}</td>
                            <td>{{ $agent->license_number ?? '—' }}</td>
                            <td><span class="badge badge-blue">{{ $agent->completed_deliveries_count }}</span></td>
                            <td>
                                @if($agent->is_active)
                                    <span class="badge badge-green">Yes</span>
                                @else
                                    <span class="badge badge-red">No</span>
                                @endif
                            </td>
                            <td class="actions text-right">
                                <a href="{{ route('admin.delivery-agents.show', $agent) }}" class="btn btn-xs btn-info" title="Past deliveries">
                                    <i class="fas fa-history"></i>
                                </a>
                                <a href="{{ route('admin.delivery-agents.edit', $agent) }}" class="btn btn-xs btn-default" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No delivery partners yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($agents->hasPages())
        <div class="card-footer clearfix">
            {{ $agents->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
