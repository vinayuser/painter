<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\PainterResource;
use App\Models\BookingImage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PainterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $painters = User::query()
            ->where('role', UserRole::Painter)
            ->where('is_active', true)
            ->where('is_verified', true)
            ->withCount(['assignedBookings as completed_jobs_count' => fn ($q) => $q->where('status', BookingStatus::Completed)])
            ->with(['portfolios'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate((int) $request->get('per_page', 15));

        return PainterResource::collection($painters)->response();
    }

    public function show(int $id): JsonResponse
    {
        $painter = User::query()
            ->where('role', UserRole::Painter)
            ->where('is_active', true)
            ->where('is_verified', true)
            ->with(['portfolios'])
            ->withCount(['assignedBookings as completed_jobs_count' => fn ($q) => $q->where('status', BookingStatus::Completed)])
            ->find($id);

        if (! $painter) {
            return response()->json(['message' => 'Painter not found.'], 404);
        }

        $recentWorks = BookingImage::query()
            ->where('type', 'after')
            ->whereHas('booking', fn ($q) => $q->where('painter_id', $painter->id)->where('status', BookingStatus::Completed))
            ->latest()
            ->limit(12)
            ->get();

        $painter->setRelation('recentWorks', $recentWorks);

        return response()->json(['data' => new PainterResource($painter)]);
    }
}
