<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => [
                'nullable',
                'integer',
                Rule::exists('customer_addresses', 'id')->where('user_id', auth('api')->id()),
            ],
            'shipping_address' => ['required_without:address_id', 'nullable', 'string', 'max:1000'],
            'shipping_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:online,cod'],
        ];
    }
}
