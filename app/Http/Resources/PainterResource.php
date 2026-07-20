<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PainterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar ? Storage::disk('public')->url($this->avatar) : null,
            'experience_years' => $this->experience_years,
            'experience_text' => $this->experience_text,
            'cost_per_hour' => $this->cost_per_hour,
            'specialization' => $this->specialization,
            'is_verified' => $this->is_verified,
            'completed_jobs' => $this->when(isset($this->completed_jobs_count), $this->completed_jobs_count),
            'portfolios' => PainterPortfolioResource::collection($this->whenLoaded('portfolios')),
            'recent_works' => BookingImageResource::collection($this->whenLoaded('recentWorks')),
        ];
    }
}
