<?php

namespace App\Services;

use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * 2Factor.in OTP SMS — same flow as Mejoric:
 *   SEND:   GET https://2factor.in/API/V1/{apiKey}/SMS/{91xxxxxxxxxx}/AUTOGEN
 *   VERIFY: GET https://2factor.in/API/V1/{apiKey}/SMS/VERIFY/{sessionId}/{otp}
 *
 * Without TWOFACTOR_API_KEY, runs in dev mode (OTP: 123456).
 */
class TwoFactorService
{
    private const BASE_URL = 'https://2factor.in/API/V1';

    public function sendOtp(string $mobile): string
    {
        $apiKey = config('services.twofactor.api_key');

        if (empty($apiKey)) {
            Log::warning('TWOFACTOR_API_KEY not set — using dev OTP mode (123456).');

            return 'dev-session-'.PhoneNumber::normalize($mobile);
        }

        $formattedMobile = $this->formatForSms($mobile);

        $url = self::BASE_URL."/{$apiKey}/SMS/{$formattedMobile}/AUTOGEN";

        if ($template = config('services.twofactor.template')) {
            $url .= '/'.rawurlencode($template);
        }

        $response = $this->request($url);

        if (($response['Status'] ?? '') !== 'Success') {
            Log::error('2Factor send OTP failed', ['mobile' => $formattedMobile, 'response' => $response]);

            throw new RuntimeException('Failed to send OTP: '.($response['Details'] ?? 'Unknown error'));
        }

        $sessionId = (string) ($response['Details'] ?? '');

        if ($sessionId === '') {
            throw new RuntimeException('Invalid session ID from 2Factor API');
        }

        Log::info('OTP sent via 2Factor', ['mobile' => $formattedMobile]);

        return $sessionId;
    }

    public function verifyOtp(string $sessionId, string $otp): bool
    {
        $otp = preg_replace('/\D/', '', $otp) ?? '';
        $apiKey = config('services.twofactor.api_key');

        if (empty($apiKey)) {
            return $otp === '123456';
        }

        if ($sessionId === '' || $otp === '') {
            return false;
        }

        $url = self::BASE_URL."/{$apiKey}/SMS/VERIFY/{$sessionId}/{$otp}";
        $response = $this->request($url);

        return ($response['Status'] ?? '') === 'Success';
    }

    /**
     * Match Mejoric formatMobileNumber: 10-digit → 91xxxxxxxxxx.
     */
    protected function formatForSms(string $mobile): string
    {
        $digits = preg_replace('/\D/', '', $mobile) ?? '';

        if ($digits === '') {
            throw new RuntimeException('Invalid mobile number.');
        }

        if (strlen($digits) === 10) {
            return '91'.$digits;
        }

        return $digits;
    }

    private function request(string $url): array
    {
        $response = Http::timeout(30)
            ->connectTimeout(10)
            ->get($url);

        if ($response->failed()) {
            Log::error('2Factor HTTP error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('OTP service unavailable. Please try again.');
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Invalid response from OTP service.');
        }

        return $data;
    }
}
