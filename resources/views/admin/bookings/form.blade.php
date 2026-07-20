@extends('admin.layout')

@section('title', 'Edit Booking')

@section('content')
<div class="page-header">
    <h1>Booking: {{ $booking->booking_number }}</h1>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
        @csrf @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label>Customer</label>
                <input type="text" value="{{ $booking->customer->name }}" disabled>
            </div>
            <div class="form-group">
                <label>Assign Painter</label>
                <select name="painter_id">
                    <option value="">-- None --</option>
                    @foreach($painters as $painter)
                        <option value="{{ $painter->id }}" @selected(old('painter_id', $booking->painter_id) == $painter->id)>{{ $painter->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Booking Date</label>
                <input type="date" name="booking_date" value="{{ old('booking_date', $booking->booking_date->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label>Booking Time</label>
                <input type="time" name="booking_time" value="{{ old('booking_time', $booking->booking_time) }}" required>
            </div>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $booking->status->value) === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" required>{{ old('address', $booking->address) }}</textarea>
        </div>
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes">{{ old('notes', $booking->notes) }}</textarea>
        </div>
        <div class="form-group">
            <label>Completion Notes</label>
            <textarea name="completion_notes">{{ old('completion_notes', $booking->completion_notes) }}</textarea>
        </div>

        @if($booking->images->count())
            <div class="form-group">
                <label>Images</label>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    @foreach($booking->images as $image)
                        <div>
                            <img src="{{ asset('storage/'.$image->image_path) }}" class="product-thumb" style="width:100px;height:100px;" alt="">
                            <p style="font-size:12px;text-align:center;">{{ $image->type->label() }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <button type="submit" class="btn btn-primary">Update Booking</button>
    </form>
</div>
@endsection
