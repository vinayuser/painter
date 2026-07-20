<?php

namespace App\Http\Requests\Booking;

use App\Rules\ValidPainter;
use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'painter_id' => ['required', 'integer', new ValidPainter],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'address' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'reference_images' => ['nullable', 'array', 'max:5'],
            'reference_images.*' => ['image', 'max:5120'],
        ];
    }
}
