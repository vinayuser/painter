<?php

namespace App\Http\Requests\Booking;

use App\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                BookingStatus::Accepted->value,
                BookingStatus::Rejected->value,
                BookingStatus::InProgress->value,
                BookingStatus::Completed->value,
            ])],
            'completion_notes' => ['nullable', 'string', 'max:2000'],
            'before_images' => ['nullable', 'array', 'max:5'],
            'before_images.*' => ['image', 'max:5120'],
            'after_images' => ['nullable', 'array', 'max:5'],
            'after_images.*' => ['image', 'max:5120'],
        ];
    }
}
