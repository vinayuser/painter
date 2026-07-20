<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Requests\Booking\UploadAfterImagesRequest;
use App\Http\Requests\Booking\UploadBeforeImagesRequest;
use App\Http\Requests\Booking\UploadWorkImagesRequest;
use App\Http\Resources\PainterBookingResource;
use App\Models\PainterBooking;
use App\Repositories\PainterBookingRepository;
use App\Services\NotificationService;
use App\Services\PainterBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PainterBookingController extends Controller
{
    public function __construct(
        protected PainterBookingRepository $bookingRepository,
        protected PainterBookingService $bookingService,
        protected NotificationService $notifications,
    ) {}

    public function customerIndex(Request $request): JsonResponse
    {
        $bookings = $this->bookingRepository->forCustomer(
            auth('api')->id(),
            (int) $request->get('per_page', 15),
            $request->get('filter')
        );

        return PainterBookingResource::collection($bookings)->response();
    }

    public function painterIndex(Request $request): JsonResponse
    {
        $bookings = $this->bookingRepository->forPainter(
            auth('api')->id(),
            (int) $request->get('per_page', 15),
            $request->get('filter')
        );

        return PainterBookingResource::collection($bookings)->response();
    }

    public function store(CreateBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->createBooking(
            auth('api')->id(),
            $request->validated(),
            $request->file('reference_images', [])
        );

        return response()->json([
            'message' => 'Painter booked successfully. Waiting for painter confirmation.',
            'data' => new PainterBookingResource($booking),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findWithRelations($id);

        if (! $booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        $this->authorize('view', $booking);

        return response()->json(['data' => new PainterBookingResource($booking)]);
    }

    public function accept(int $id): JsonResponse
    {
        $booking = $this->getPainterBooking($id);

        if ($booking->status !== BookingStatus::Assigned) {
            return response()->json(['message' => 'Booking cannot be accepted in current status.'], 422);
        }

        $booking->update(['status' => BookingStatus::Accepted]);
        $booking = $booking->fresh(['customer', 'painter', 'images']);
        $this->notifications->bookingAccepted($booking);

        return response()->json([
            'message' => 'Booking accepted.',
            'data' => new PainterBookingResource($booking),
        ]);
    }

    public function reject(int $id): JsonResponse
    {
        $booking = $this->getPainterBooking($id);

        if ($booking->status !== BookingStatus::Assigned) {
            return response()->json(['message' => 'Booking cannot be rejected in current status.'], 422);
        }

        $booking->update(['status' => BookingStatus::Rejected]);
        $booking = $booking->fresh(['customer', 'painter', 'images']);
        $this->notifications->bookingRejected($booking);

        return response()->json([
            'message' => 'Booking rejected.',
            'data' => new PainterBookingResource($booking),
        ]);
    }

    public function startWork(int $id): JsonResponse
    {
        $booking = $this->getPainterBooking($id);

        if ($booking->status !== BookingStatus::Accepted) {
            return response()->json(['message' => 'Booking must be accepted before starting work.'], 422);
        }

        $booking->update(['status' => BookingStatus::InProgress]);
        $booking = $booking->fresh(['customer', 'painter', 'images']);
        $this->notifications->bookingStarted($booking);

        return response()->json([
            'message' => 'Work started.',
            'data' => new PainterBookingResource($booking),
        ]);
    }

    public function uploadBeforeImages(UploadBeforeImagesRequest $request, int $id): JsonResponse
    {
        $booking = $this->getPainterBooking($id);

        try {
            $booking = $this->bookingService->uploadBeforeImages(
                $booking,
                $request->file('before_images', []),
                $request->input('work_notes'),
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Before images uploaded successfully.',
            'data' => new PainterBookingResource($booking),
        ]);
    }

    public function uploadAfterImages(UploadAfterImagesRequest $request, int $id): JsonResponse
    {
        $booking = $this->getPainterBooking($id);

        try {
            $booking = $this->bookingService->uploadAfterImagesAndComplete(
                $booking,
                $request->file('after_images', []),
                $request->input('completion_notes'),
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'After images uploaded. Booking marked as completed.',
            'data' => new PainterBookingResource($booking),
        ]);
    }

    public function uploadWorkImages(UploadWorkImagesRequest $request, int $id): JsonResponse
    {
        $booking = $this->getPainterBooking($id);

        if ($booking->status !== BookingStatus::InProgress) {
            return response()->json(['message' => 'Booking must be in progress. Call /start first.'], 422);
        }

        try {
            $booking = $this->bookingService->completeWithImages(
                $booking,
                $request->file('before_images', []),
                $request->file('after_images', []),
                $request->input('completion_notes'),
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Work completed and images uploaded.',
            'data' => new PainterBookingResource($booking),
        ]);
    }

    private function getPainterBooking(int $id): PainterBooking
    {
        $booking = $this->bookingRepository->findWithRelations($id);

        if (! $booking) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json(['message' => 'Booking not found.'], 404)
            );
        }

        $this->authorize('update', $booking);

        return $booking;
    }
}
