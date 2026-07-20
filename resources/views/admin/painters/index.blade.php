@extends('admin.layout')

@section('title', 'Painters')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-paint-brush mr-2"></i>Painters</h1>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filter-bar mb-0 w-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or phone..." class="form-control form-control-sm">
            <select name="verified" class="form-control form-control-sm" style="width:auto;">
                <option value="">All verification</option>
                <option value="1" @selected(request('verified') === '1')>Verified</option>
                <option value="0" @selected(request('verified') === '0')>Not verified</option>
            </select>
            <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-filter"></i> Filter</button>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Experience</th>
                    <th>Rate/hr</th>
                    <th>Bookings</th>
                    <th>Portfolio</th>
                    <th>Verified</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($painters as $painter)
                    <tr>
                        <td><strong>{{ $painter->name }}</strong></td>
                        <td>{{ $painter->phone ?? '—' }}</td>
                        <td>{{ $painter->experience_years ? $painter->experience_years.' yrs' : '—' }}</td>
                        <td>{{ $painter->cost_per_hour ? '₹'.$painter->cost_per_hour : '—' }}</td>
                        <td><span class="badge badge-blue">{{ $painter->assigned_bookings_count }}</span></td>
                        <td>{{ $painter->portfolios_count }} images</td>
                        <td>
                            @if($painter->is_verified)
                                <span class="badge badge-green"><i class="fas fa-check"></i> Yes</span>
                            @else
                                <span class="badge badge-yellow">Pending</span>
                            @endif
                        </td>
                        <td>{{ $painter->is_active ? 'Yes' : 'No' }}</td>
                        <td class="actions">
                            <a href="{{ route('admin.painters.show', $painter) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.users.edit', $painter) }}" class="btn btn-xs btn-default"><i class="fas fa-edit"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No painters registered yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($painters->hasPages())
        <div class="card-footer">{{ $painters->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
