<?php

namespace App\Jobs;

use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendFcmNotifications implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(
        private readonly array  $tokens,
        private readonly string $title,
        private readonly string $body,
        private readonly array  $data = [],
    ) {}

    public function handle(): void
    {
        FcmService::sendToTokens($this->tokens, $this->title, $this->body, $this->data);
    }
}
