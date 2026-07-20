<?php

namespace App\Services;

use App\Enums\BookingImageType;
use App\Enums\BookingStatus;
use App\Models\PainterBooking;
use App\Repositories\PainterBookingRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PainterBookingService
{
    public function __construct(
        protected PainterBookingRepository $bookingRepository,
        protected NotificationService $notifications,
    ) {}

    public function createBooking(int $customerId, array $data, array $referenceImages = []): PainterBooking
    {
        $booking = $this->bookingRepository->create([
            'booking_number' => 'BK-'.strtoupper(Str::random(10)),
            'customer_id' => $customerId,
            'painter_id' => $data['painter_id'],
            'booking_date' => $data['booking_date'],
            'booking_time' => $data['booking_time'],
            'address' => $data['address'],
            'notes' => $data['notes'] ?? null,
            'status' => BookingStatus::Assigned,
        ]);

        foreach ($referenceImages as $image) {
            $this->storeImage($booking, $image, BookingImageType::Reference);
        }

        $booking = $booking->load(['images', 'painter', 'customer']);
        $this->notifications->bookingCreated($booking);

        return $booking;
    }

    public function uploadBeforeImages(PainterBooking $booking, array $files, ?string $workNotes = null): PainterBooking
    {
        if (! in_array($booking->status, [BookingStatus::Accepted, BookingStatus::InProgress], true)) {
            throw new \RuntimeException('Before images can only be uploaded for accepted or in-progress bookings.');
        }

        foreach ($files as $image) {
            $this->storeImage($booking, $image, BookingImageType::Before);
        }

        $updates = [];
        if ($booking->status === BookingStatus::Accepted) {
            $updates['status'] = BookingStatus::InProgress;
        }
        if ($workNotes) {
            $updates['notes'] = trim(($booking->notes ? $booking->notes."\n" : '').'Before work: '.$workNotes);
        }

        if ($updates !== []) {
            $booking->update($updates);
        }

        $booking = $booking->fresh(['customer', 'painter', 'images']);

        if (($updates['status'] ?? null) === BookingStatus::InProgress) {
            $this->notifications->bookingStarted($booking);
        }

        return $booking;
    }

    public function uploadAfterImagesAndComplete(PainterBooking $booking, array $files, ?string $completionNotes = null): PainterBooking
    {
        if ($booking->status !== BookingStatus::InProgress) {
            throw new \RuntimeException('After images can only be uploaded when work is in progress.');
        }

        if (! $booking->images()->where('type', BookingImageType::Before)->exists()) {
            throw new \RuntimeException('Upload before images first.');
        }

        foreach ($files as $image) {
            $this->storeImage($booking, $image, BookingImageType::After);
        }

        $booking->update([
            'status' => BookingStatus::Completed,
            'completion_notes' => $completionNotes,
            'completed_at' => now(),
        ]);

        $booking = $booking->fresh(['customer', 'painter', 'images']);
        $this->notifications->bookingCompleted($booking);

        return $booking;
    }

    public function completeWithImages(
        PainterBooking $booking,
        array $beforeFiles,
        array $afterFiles,
        ?string $completionNotes = null,
    ): PainterBooking {
        if ($booking->status !== BookingStatus::InProgress) {
            throw new \RuntimeException('Booking must be in progress to complete work.');
        }

        foreach ($beforeFiles as $image) {
            $this->storeImage($booking, $image, BookingImageType::Before);
        }

        if (empty($afterFiles)) {
            throw new \RuntimeException('At least one after image is required.');
        }

        foreach ($afterFiles as $image) {
            $this->storeImage($booking, $image, BookingImageType::After);
        }

        $booking->refresh();

        if (! $booking->images()->where('type', BookingImageType::Before)->exists()) {
            throw new \RuntimeException('At least one before image is required.');
        }

        if (! $booking->images()->where('type', BookingImageType::After)->exists()) {
            throw new \RuntimeException('At least one after image is required.');
        }

        $booking->update([
            'status' => BookingStatus::Completed,
            'completion_notes' => $completionNotes,
            'completed_at' => now(),
        ]);

        $booking = $booking->fresh(['customer', 'painter', 'images']);
        $this->notifications->bookingCompleted($booking);

        return $booking;
    }
    {
        $typeValue = $type instanceof BookingImageType ? $type->value : $type;
        $path = $file->store("bookings/{$booking->id}", 'public');

        $booking->images()->create([
            'image_path' => $path,
            'type' => $typeValue,
        ]);
    }
}
