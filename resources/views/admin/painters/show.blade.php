@extends('admin.layout')

@section('title', $painter->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.painters.index') }}">Painters</a></li>
    <li class="breadcrumb-item active">{{ $painter->name }}</li>
@endsection

@section('content')
<div class="page-header">
    <h1>{{ $painter->name }}</h1>
    <div>
        <a href="{{ route('admin.users.edit', $painter) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
        <a href="{{ route('admin.painters.index') }}" class="btn btn-sm btn-default">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body profile-box">
                <div class="profile-avatar">{{ strtoupper(substr($painter->name, 0, 1)) }}</div>
                <h4 class="mb-1">{{ $painter->name }}</h4>
                <p class="text-muted">{{ $painter->specialization ?? 'General painter' }}</p>
                <p>
                    @if($painter->is_verified)
                        <span class="badge badge-green"><i class="fas fa-check-circle"></i> Verified</span>
                    @else
                        <span class="badge badge-yellow">Verification pending</span>
                    @endif
                    @if($painter->is_active)
                        <span class="badge badge-blue">Active</span>
                    @else
                        <span class="badge badge-red">Inactive</span>
                    @endif
                </p>
            </div>
            <div class="card-footer">
                <dl class="row mb-0 text-sm">
                    <dt class="col-5">Phone</dt><dd class="col-7">{{ $painter->phone ?? '—' }}</dd>
                    <dt class="col-5">Email</dt><dd class="col-7">{{ $painter->email ?? '—' }}</dd>
                    <dt class="col-5">Experience</dt><dd class="col-7">{{ $painter->experience_text ?? ($painter->experience_years ? $painter->experience_years.' years' : '—') }}</dd>
                    <dt class="col-5">Rate</dt><dd class="col-7">{{ $painter->cost_per_hour ? '₹'.$painter->cost_per_hour.'/hr' : '—' }}</dd>
                    <dt class="col-5">Aadhar</dt><dd class="col-7">{{ $painter->aadhar_number ? '****'.substr($painter->aadhar_number, -4) : '—' }}</dd>
                </dl>
            </div>
        </div>

        @if($painter->portfolios->isNotEmpty())
            <div class="card">
                <div class="card-header"><h3 class="card-title">Portfolio</h3></div>
                <div class="card-body">
                    <div class="row">
                        @foreach($painter->portfolios as $item)
                            <div class="col-6 mb-2">
                                <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->title }}" class="img-fluid rounded" style="height:100px;width:100%;object-fit:cover;">
                                @if($item->title)<small class="d-block text-muted">{{ $item->title }}</small>@endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="row mb-3">
            <div class="col-4"><div class="stat-tile"><strong>{{ $painter->assignedBookings->count() }}</strong><span>Recent bookings</span></div></div>
            <div class="col-4"><div class="stat-tile"><strong>{{ $painter->portfolios->count() }}</strong><span>Portfolio items</span></div></div>
            <div class="col-4"><div class="stat-tile"><strong>{{ $painter->completedJobsCount() }}</strong><span>Completed jobs</span></div></div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Recent Bookings</h3></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Booking #</th><th>Customer</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($painter->assignedBookings as $booking)
                            <tr>
                                <td><a href="{{ route('admin.bookings.edit', $booking) }}">{{ $booking->booking_number }}</a></td>
                                <td>{{ $booking->customer->name }}</td>
                                <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                <td><span class="badge badge-yellow">{{ $booking->status->label() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No bookings assigned.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($painter->bio)
            <div class="card">
                <div class="card-header"><h3 class="card-title">Bio</h3></div>
                <div class="card-body">{{ $painter->bio }}</div>
            </div>
        @endif
    </div>
</div>
@endsection
