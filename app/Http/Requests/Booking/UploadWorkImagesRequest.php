<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class UploadWorkImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'before_images' => ['nullable', 'array', 'max:5'],
            'before_images.*' => ['image', 'max:5120'],
            'after_images' => ['required', 'array', 'min:1', 'max:5'],
            'after_images.*' => ['image', 'max:5120'],
            'completion_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'after_images.required' => 'At least one after image is required to complete the job.',
            'after_images.min' => 'At least one after image is required to complete the job.',
        ];
    }
}
