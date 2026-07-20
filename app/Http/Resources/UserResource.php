<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->avatar) : null,
            'role' => $this->role?->value,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'experience_years' => $this->when($this->isPainter(), $this->experience_years),
            'experience_text' => $this->when($this->isPainter(), $this->experience_text),
            'cost_per_hour' => $this->when($this->isPainter(), $this->cost_per_hour),
            'aadhar_number' => $this->when($this->isPainter(), $this->aadhar_number),
            'specialization' => $this->when($this->isPainter(), $this->specialization),
            'license_number' => $this->when($this->isDeliveryAgent(), $this->license_number),
            'vehicle_number' => $this->when($this->isDeliveryAgent(), $this->vehicle_number),
            'portfolios' => PainterPortfolioResource::collection($this->whenLoaded('portfolios')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
