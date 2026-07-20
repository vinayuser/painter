<?php

namespace App\Http\Controllers\Web;

use App\Enums\FcmTopic;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function __construct(
        protected FcmService $fcm,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        $user = $request->user();

        if (! in_array($user->role, [UserRole::Admin, UserRole::Vendor], true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->fcm->saveToken($user, $data['fcm_token']);

        return response()->json([
            'message' => 'Browser notifications enabled.',
            'topic' => FcmTopic::forRole($user->role)->value,
        ]);
    }
}
