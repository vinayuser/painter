<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpLoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\StaffRegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\FcmService;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AuthController extends Controller
{
    public function __construct(
        private readonly TwoFactorService $twoFactor,
        private readonly FcmService $fcm,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $phone = $data['phone'];

        $user = User::query()->create([
            'name' => $data['name'],
            'phone' => $phone,
            'email' => $data['email'] ?? $phone.'@customer.local',
            'address' => $data['address'] ?? null,
            'role' => UserRole::Customer,
            'is_active' => true,
            'is_verified' => false,
            'password' => Hash::make(Str::random(32)),
        ]);

        try {
            $sessionId = $this->twoFactor->sendOtp($phone);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json([
            'message' => 'Registration successful. OTP sent to your mobile.',
            'otp_sent' => true,
            'mobile' => $phone,
            'session_id' => $sessionId,
            'user' => new UserResource($user),
        ], 201);
    }

    public function staffRegister(StaffRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $phone = $data['phone'];
        $experienceYears = (int) $data['experience_years'];

        $user = User::query()->create([
            'name' => $data['name'],
            'phone' => $phone,
            'email' => $data['email'] ?? $phone.'@painter.local',
            'experience_years' => $experienceYears,
            'experience_text' => $experienceYears.' years',
            'cost_per_hour' => $data['cost_per_hour'],
            'aadhar_number' => $data['aadhar_number'],
            'specialization' => $data['specialization'] ?? null,
            'role' => UserRole::Painter,
            'is_active' => true,
            'is_verified' => false,
            'password' => Hash::make(Str::random(32)),
        ]);

        try {
            $sessionId = $this->twoFactor->sendOtp($phone);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json([
            'message' => 'Registration successful. OTP sent to your mobile.',
            'otp_sent' => true,
            'mobile' => $phone,
            'session_id' => $sessionId,
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(OtpLoginRequest $request): JsonResponse
    {
        return $this->sendLoginOtp($request->validated()['phone'], UserRole::Customer);
    }

    public function staffLogin(OtpLoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $role = UserRole::from($data['role'] ?? UserRole::Painter->value);

        if (! in_array($role, [UserRole::Painter, UserRole::DeliveryAgent], true)) {
            return response()->json(['message' => 'Invalid role for staff login.'], 422);
        }

        return $this->sendLoginOtp($data['phone'], $role);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $role = UserRole::from($data['role'] ?? UserRole::Customer->value);

        if (! $this->twoFactor->verifyOtp($data['session_id'], $data['otp'])) {
            return response()->json(['message' => 'Invalid or expired OTP. Please try again or resend OTP.'], 401);
        }

        $user = User::query()
            ->where('phone', $data['phone'])
            ->where('role', $role)
            ->first();

        if (! $user) {
            return response()->json(['message' => 'Account not found.'], 404);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Account is inactive.'], 403);
        }

        $user->update(['is_verified' => true]);

        if (! empty($data['fcm_token'])) {
            $this->fcm->saveToken($user, $data['fcm_token']);
        }

        $token = auth('api')->login($user->fresh());

        return $this->respondWithToken($token, $user->fresh());
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        /** @var User $user */
        $user = auth('api')->user();
        $this->fcm->saveToken($user, $data['fcm_token']);

        return response()->json([
            'message' => 'FCM token saved and subscribed to '.$this->fcm->topicForRole($user->role)->value,
            'topic' => $this->fcm->topicForRole($user->role)->value,
        ]);
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $phone = $data['phone'];
        $purpose = $data['purpose'] ?? 'login';
        $role = UserRole::from($data['role'] ?? UserRole::Customer->value);

        if ($purpose === 'login') {
            $user = User::query()
                ->where('phone', $phone)
                ->where('role', $role)
                ->first();

            if (! $user) {
                return response()->json(['message' => 'Mobile number not registered.'], 404);
            }
        }

        try {
            $sessionId = $this->twoFactor->sendOtp($phone);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json([
            'message' => 'OTP resent successfully.',
            'otp_sent' => true,
            'mobile' => $phone,
            'session_id' => $sessionId,
        ]);
    }

    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        if ($user->isPainter()) {
            $user->load('portfolios');
        }

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out.']);
    }

    public function refresh(): JsonResponse
    {
        try {
            $newToken = auth('api')->refresh();
            $user = auth('api')->setToken($newToken)->user();

            if (! $user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            return $this->respondWithToken($newToken, $user);
        } catch (TokenExpiredException) {
            return response()->json([
                'message' => 'Session expired. Please login again with OTP.',
                'error' => 'session_expired',
            ], 401);
        } catch (JWTException) {
            return response()->json([
                'message' => 'Invalid token. Please login again.',
                'error' => 'token_invalid',
            ], 401);
        }
    }

    protected function sendLoginOtp(string $phone, UserRole $role): JsonResponse
    {
        $user = User::query()
            ->where('phone', $phone)
            ->where('role', $role)
            ->first();

        if (! $user) {
            $message = $role === UserRole::Customer
                ? 'Mobile number not registered. Please create an account first.'
                : 'Mobile number not registered. Contact admin to create your account.';

            return response()->json(['message' => $message], 404);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Account is inactive.'], 403);
        }

        try {
            $sessionId = $this->twoFactor->sendOtp($phone);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json([
            'message' => 'OTP sent successfully.',
            'otp_sent' => true,
            'mobile' => $phone,
            'session_id' => $sessionId,
        ]);
    }

    protected function respondWithToken(string $token, User $user): JsonResponse
    {
        if ($user->isPainter()) {
            $user->load('portfolios');
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => new UserResource($user),
        ]);
    }
}
