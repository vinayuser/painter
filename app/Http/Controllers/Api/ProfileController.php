<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\PainterPortfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $user = auth('api')->user();

        if ($user->isPainter()) {
            $user->load('portfolios');
        }

        return response()->json(['data' => new UserResource($user)]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $data = $request->only(['name', 'email', 'phone', 'address', 'bio']);

        if ($user->isPainter()) {
            $data = array_merge($data, $request->only([
                'experience_years', 'experience_text', 'cost_per_hour', 'specialization',
            ]));

            if ($request->filled('experience_years') && ! $request->filled('experience_text')) {
                $data['experience_text'] = $request->integer('experience_years').' years';
            }
        }

        if ($user->isDeliveryAgent()) {
            $data = array_merge($data, $request->only(['license_number', 'vehicle_number']));
        }

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        if ($user->isPainter() && $request->hasFile('portfolio_images')) {
            foreach ($request->file('portfolio_images') as $index => $image) {
                PainterPortfolio::query()->create([
                    'painter_id' => $user->id,
                    'title' => $request->input("portfolio_titles.{$index}"),
                    'image_path' => $image->store('portfolios', 'public'),
                ]);
            }
            $user->load('portfolios');
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => new UserResource($user->fresh()),
        ]);
    }
}
