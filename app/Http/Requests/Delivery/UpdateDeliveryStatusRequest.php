<?php

namespace App\Http\Requests\Delivery;

use App\Enums\DeliveryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                DeliveryStatus::PickedUp->value,
                DeliveryStatus::OutForDelivery->value,
                DeliveryStatus::Delivered->value,
            ])],
            'delivery_proof' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
