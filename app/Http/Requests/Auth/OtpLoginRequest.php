<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OtpLoginRequest extends FormRequest
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
        return [
            'phone' => ['required', 'string', 'size:10', 'regex:/^[6-9]\d{9}$/'],
            'role' => ['sometimes', Rule::in([UserRole::Painter->value, UserRole::DeliveryAgent->value])],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid 10-digit Indian mobile number.',
        ];
    }
}
