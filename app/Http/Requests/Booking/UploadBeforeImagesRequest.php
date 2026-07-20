<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class UploadBeforeImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'before_images' => ['required', 'array', 'min:1', 'max:5'],
            'before_images.*' => ['image', 'max:5120'],
            'work_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
