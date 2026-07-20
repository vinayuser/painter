<?php

namespace App\Http\Resources;

use App\Enums\BookingImageType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PainterBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'booking_date' => $this->booking_date?->format('Y-m-d'),
            'booking_time' => $this->booking_time,
            'address' => $this->address,
            'notes' => $this->notes,
            'completion_notes' => $this->completion_notes,
            'status' => $this->status?->value,
            'completed_at' => $this->completed_at?->toISOString(),
            'customer' => new UserResource($this->whenLoaded('customer')),
            'painter' => new UserResource($this->whenLoaded('painter')),
            'images' => BookingImageResource::collection($this->whenLoaded('images')),
            'before_images' => BookingImageResource::collection(
                $this->whenLoaded('images', fn () => $this->images->filter(
                    fn ($img) => $img->type === BookingImageType::Before
                )->values())
            ),
            'after_images' => BookingImageResource::collection(
                $this->whenLoaded('images', fn () => $this->images->filter(
                    fn ($img) => $img->type === BookingImageType::After
                )->values())
            ),
            'reference_images' => BookingImageResource::collection(
                $this->whenLoaded('images', fn () => $this->images->filter(
                    fn ($img) => $img->type === BookingImageType::Reference
                )->values())
            ),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
