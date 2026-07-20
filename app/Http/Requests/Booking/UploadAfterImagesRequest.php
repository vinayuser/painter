<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class UploadAfterImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'after_images' => ['required', 'array', 'min:1', 'max:5'],
            'after_images.*' => ['image', 'max:5120'],
            'completion_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
