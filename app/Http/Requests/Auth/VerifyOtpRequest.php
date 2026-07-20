<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyOtpRequest extends FormRequest
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
            'otp' => ['required', 'string', 'regex:/^\d{6}$/'],
            'session_id' => ['required', 'string', 'max:255'],
            'role' => ['sometimes', Rule::in([
                UserRole::Customer->value,
                UserRole::Painter->value,
                UserRole::DeliveryAgent->value,
            ])],
            'fcm_token' => ['sometimes', 'nullable', 'string', 'max:512'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'otp.regex' => 'OTP must be 6 digits.',
        ];
    }
}
