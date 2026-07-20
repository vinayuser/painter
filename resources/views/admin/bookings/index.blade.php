@extends('admin.layout')

@section('title', 'Painter Bookings')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-check mr-1"></i> Painter Bookings</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-bar">
            <select name="status" class="form-control form-control-sm" style="width:auto;">
                <option value="">All Statuses</option>
                @foreach(\App\Enums\BookingStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Customer</th>
                        <th>Painter</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td><a href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->booking_number }}</a></td>
                            <td>{{ $booking->customer->name }}</td>
                            <td>{{ $booking->painter->name ?? '—' }}</td>
                            <td>{{ $booking->booking_date->format('d M Y') }}</td>
                            <td>{{ $booking->booking_time }}</td>
                            <td><span class="badge badge-yellow">{{ $booking->status->label() }}</span></td>
                            <td class="actions">
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-xs btn-default"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $bookings->withQueryString()->links() }}</div>
</div>
@endsection
