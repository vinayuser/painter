<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PainterBooking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PainterBookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = PainterBooking::query()
            ->with(['customer', 'painter'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15);

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(PainterBooking $booking): View
    {
        $booking->load(['customer', 'painter', 'images']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function edit(PainterBooking $booking): View
    {
        $booking->load(['customer', 'painter', 'images']);

        return view('admin.bookings.form', [
            'booking' => $booking,
            'painters' => User::query()->where('role', UserRole::Painter)->where('is_active', true)->get(),
            'statuses' => BookingStatus::cases(),
        ]);
    }

    public function update(Request $request, PainterBooking $booking): RedirectResponse
    {
        $data = $request->validate([
            'painter_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::in(array_column(BookingStatus::cases(), 'value'))],
            'booking_date' => ['required', 'date'],
            'booking_time' => ['required'],
            'address' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'completion_notes' => ['nullable', 'string'],
        ]);

        if ($data['status'] === BookingStatus::Completed->value) {
            $data['completed_at'] = now();
        }

        $booking->update($data);

        return redirect()->route('admin.bookings.index')->with('success', 'Booking updated successfully.');
    }
}
