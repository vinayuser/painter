<?php

namespace App\Models;

use App\Enums\BookingImageType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['painter_booking_id', 'image_path', 'type'])]
class BookingImage extends Model
{
    protected function casts(): array
    {
        return [
            'type' => BookingImageType::class,
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(PainterBooking::class, 'painter_booking_id');
    }
}
