<?php

namespace App\Http\Requests\Profile;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge(['phone' => PhoneNumber::normalize($this->input('phone'))]);
        }
    }

    public function rules(): array
    {
        $user = auth('api')->user();
        $userId = $user?->id;

        $rules = [
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'phone' => ['sometimes', 'string', 'size:10', 'regex:/^[6-9]\d{9}$/', Rule::unique('users')->ignore($userId)],
            'address' => ['nullable', 'string', 'max:1000'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar' => ['nullable', 'image', 'max:5120'],
            'portfolio_images' => ['nullable', 'array', 'max:5'],
            'portfolio_images.*' => ['image', 'max:5120'],
            'portfolio_titles' => ['nullable', 'array'],
            'portfolio_titles.*' => ['nullable', 'string', 'max:255'],
        ];

        if ($user?->isPainter()) {
            $rules['experience_years'] = ['sometimes', 'integer', 'min:0', 'max:60'];
            $rules['experience_text'] = ['sometimes', 'string', 'max:50'];
            $rules['cost_per_hour'] = ['sometimes', 'numeric', 'gt:0'];
            $rules['specialization'] = ['nullable', 'string', 'max:150'];
        }

        if ($user?->isDeliveryAgent()) {
            $rules['license_number'] = ['nullable', 'string', 'max:50'];
            $rules['vehicle_number'] = ['nullable', 'string', 'max:20'];
        }

        return $rules;
    }
}
