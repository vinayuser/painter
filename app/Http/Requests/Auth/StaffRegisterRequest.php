<?php

namespace App\Http\Requests\Auth;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class StaffRegisterRequest extends FormRequest
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

        if ($this->has('aadhar_number')) {
            $this->merge(['aadhar_number' => preg_replace('/\s+/', '', (string) $this->input('aadhar_number'))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'size:10', 'regex:/^[6-9]\d{9}$/', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'cost_per_hour' => ['required', 'numeric', 'gt:0'],
            'aadhar_number' => ['required', 'string', 'size:12', 'regex:/^\d{12}$/', 'unique:users,aadhar_number'],
            'specialization' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'aadhar_number.regex' => 'Aadhar number must be 12 digits.',
        ];
    }
}
