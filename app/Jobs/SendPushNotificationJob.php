<?php

namespace App\Jobs;

use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array{title: string, body: string, token?: string|null, topic?: string|null, data?: array<string, mixed>}  $payload
     */
    public function __construct(
        public array $payload,
    ) {}

    public function handle(FcmService $fcm): void
    {
        $title = $this->payload['title'] ?? '';
        $body = $this->payload['body'] ?? '';
        $data = $this->payload['data'] ?? [];

        if (! empty($this->payload['token'])) {
            $fcm->sendToToken($this->payload['token'], $title, $body, $data);

            return;
        }

        if (! empty($this->payload['topic'])) {
            $fcm->sendToTopic($this->payload['topic'], $title, $body, $data);
        }
    }
}
