@extends('admin.layout')

@section('title', $booking->booking_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
    <li class="breadcrumb-item active">{{ $booking->booking_number }}</li>
@endsection

@section('content')
<div class="page-header">
    <h1>Booking {{ $booking->booking_number }}</h1>
    <div>
        <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Manage</a>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-default">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Booking Details</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Status</dt><dd class="col-7"><span class="badge badge-yellow">{{ $booking->status->label() }}</span></dd>
                    <dt class="col-5">Date</dt><dd class="col-7">{{ $booking->booking_date->format('d M Y') }}</dd>
                    <dt class="col-5">Time</dt><dd class="col-7">{{ $booking->booking_time }}</dd>
                    <dt class="col-5">Customer</dt><dd class="col-7"><a href="{{ route('admin.customers.show', $booking->customer) }}">{{ $booking->customer->name }}</a></dd>
                    <dt class="col-5">Painter</dt><dd class="col-7">
                        @if($booking->painter)
                            <a href="{{ route('admin.painters.show', $booking->painter) }}">{{ $booking->painter->name }}</a>
                        @else <span class="text-muted">Not assigned</span> @endif
                    </dd>
                    <dt class="col-5">Address</dt><dd class="col-7">{{ $booking->address }}</dd>
                    @if($booking->notes)<dt class="col-5">Notes</dt><dd class="col-7">{{ $booking->notes }}</dd>@endif
                    @if($booking->completion_notes)<dt class="col-5">Completion</dt><dd class="col-7">{{ $booking->completion_notes }}</dd>@endif
                    @if($booking->completed_at)<dt class="col-5">Completed</dt><dd class="col-7">{{ $booking->completed_at->format('d M Y, H:i') }}</dd>@endif
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-images mr-1"></i> Work Images</h3></div>
            <div class="card-body">
                @if($booking->images->isEmpty())
                    <p class="text-muted text-center mb-0">No images uploaded yet.</p>
                @else
                    <div class="row">
                        @foreach($booking->images as $image)
                            <div class="col-6 col-md-4 mb-3 text-center">
                                <a href="{{ asset('storage/'.$image->image_path) }}" target="_blank">
                                    <img src="{{ asset('storage/'.$image->image_path) }}" class="img-fluid rounded border" style="height:120px;width:100%;object-fit:cover;" alt="">
                                </a>
                                <span class="badge badge-gray mt-1">{{ $image->type->label() }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
