<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'booking_number', 'customer_id', 'painter_id', 'booking_date', 'booking_time',
    'address', 'notes', 'completion_notes', 'status', 'completed_at',
])]
class PainterBooking extends Model
{
    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'booking_time' => 'datetime:H:i',
            'status' => BookingStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function painter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'painter_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(BookingImage::class);
    }
}
