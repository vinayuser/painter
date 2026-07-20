<?php

namespace App\Services;

use App\Enums\FcmTopic;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;
use Throwable;

class FcmService
{
    protected ?Messaging $messaging = null;

    protected bool $resolved = false;

    public function isConfigured(): bool
    {
        $credentials = $this->credentialsPath();

        return $credentials !== null && is_file($credentials);
    }

    /**
     * Resolve FIREBASE_CREDENTIALS to an absolute path (Hostinger-friendly relative paths work too).
     */
    public function credentialsPath(): ?string
    {
        $credentials = config('firebase.projects.app.credentials');

        if (! is_string($credentials) || trim($credentials) === '') {
            return null;
        }

        $credentials = trim($credentials);

        // Absolute Linux/Windows path, or raw JSON string — use as-is.
        if (
            str_starts_with($credentials, '/')
            || str_contains($credentials, ':\\')
            || str_starts_with($credentials, '{')
        ) {
            return $credentials;
        }

        return base_path($credentials);
    }

    protected function messaging(): ?Messaging
    {
        if ($this->resolved) {
            return $this->messaging;
        }

        $this->resolved = true;

        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $this->messaging = app('firebase.messaging');
        } catch (Throwable $e) {
            Log::warning('Firebase messaging unavailable', ['error' => $e->getMessage()]);
            $this->messaging = null;
        }

        return $this->messaging;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function sendToToken(?string $token, string $title, string $body, array $data = []): bool
    {
        $messaging = $this->messaging();

        if (! $messaging || blank($token)) {
            return false;
        }

        try {
            $message = CloudMessage::new()
                ->toToken($token)
                ->withNotification(Notification::create($title, $body))
                ->withData($this->stringifyData($data))
                ->withWebPushConfig($this->webPushConfig($title, $body, $data))
                ->withDefaultSounds()
                ->withHighestPossiblePriority();

            $messaging->send($message);

            return true;
        } catch (MessagingException|FirebaseException $e) {
            Log::warning('FCM send to token failed', [
                'error' => $e->getMessage(),
                'token_prefix' => substr((string) $token, 0, 12),
            ]);

            if ($this->isInvalidTokenError($e->getMessage())) {
                User::query()->where('fcm_token', $token)->update([
                    'fcm_token' => null,
                    'fcm_topics_subscribed' => false,
                ]);
            }

            return false;
        } catch (Throwable $e) {
            Log::error('FCM unexpected error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function sendToTopic(FcmTopic|string $topic, string $title, string $body, array $data = []): bool
    {
        $messaging = $this->messaging();

        if (! $messaging) {
            return false;
        }

        $topicName = $topic instanceof FcmTopic ? $topic->value : $topic;

        try {
            $message = CloudMessage::new()
                ->toTopic($topicName)
                ->withNotification(Notification::create($title, $body))
                ->withData($this->stringifyData($data))
                ->withWebPushConfig($this->webPushConfig($title, $body, $data))
                ->withDefaultSounds()
                ->withHighestPossiblePriority();

            $messaging->send($message);

            return true;
        } catch (Throwable $e) {
            Log::warning('FCM send to topic failed', [
                'topic' => $topicName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Ensures Chrome shows the alert via the service worker even when no admin tab is focused.
     *
     * @param  array<string, mixed>  $data
     */
    protected function webPushConfig(string $title, string $body, array $data = []): WebPushConfig
    {
        $link = $this->clickLinkFromData($data);

        return WebPushConfig::fromArray([
            'headers' => [
                'Urgency' => 'high',
                'TTL' => '86400',
            ],
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => url('/favicon.ico'),
                'requireInteraction' => true,
            ],
            'fcm_options' => [
                'link' => $link,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function clickLinkFromData(array $data): string
    {
        $orderId = $data['order_id'] ?? null;

        if ($orderId) {
            return url('/admin/orders/'.$orderId);
        }

        return url('/admin/notifications');
    }

    public function subscribeUser(User $user): void
    {
        $messaging = $this->messaging();

        if (! $messaging || blank($user->fcm_token) || ! $user->role) {
            return;
        }

        $topic = FcmTopic::forRole($user->role);

        try {
            $messaging->subscribeToTopic($topic->value, [$user->fcm_token]);
            $user->forceFill(['fcm_topics_subscribed' => true])->save();
        } catch (Throwable $e) {
            Log::warning('FCM topic subscribe failed', [
                'user_id' => $user->id,
                'topic' => $topic->value,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function unsubscribeUser(User $user, ?string $oldToken = null): void
    {
        $messaging = $this->messaging();
        $token = $oldToken ?: $user->fcm_token;

        if (! $messaging || blank($token) || ! $user->role) {
            return;
        }

        try {
            $topic = FcmTopic::forRole($user->role);
            $messaging->unsubscribeFromTopic($topic->value, [$token]);
        } catch (Throwable $e) {
            Log::warning('FCM topic unsubscribe failed', ['error' => $e->getMessage()]);
        }
    }

    public function saveToken(User $user, string $token): User
    {
        $token = trim($token);
        $oldToken = $user->fcm_token;

        if ($oldToken && $oldToken !== $token) {
            $this->unsubscribeUser($user, $oldToken);
        }

        $user->forceFill([
            'fcm_token' => $token,
            'fcm_topics_subscribed' => false,
        ])->save();

        $this->subscribeUser($user->fresh());

        return $user->fresh();
    }

    public function topicForRole(UserRole $role): FcmTopic
    {
        return FcmTopic::forRole($role);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    protected function stringifyData(array $data): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $out[(string) $key] = is_scalar($value)
                ? (string) $value
                : json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $out;
    }

    protected function isInvalidTokenError(string $message): bool
    {
        $message = strtolower($message);

        return str_contains($message, 'not-registered')
            || str_contains($message, 'registration-token-not-registered')
            || str_contains($message, 'invalid-registration-token')
            || str_contains($message, 'requested entity was not found');
    }
}
