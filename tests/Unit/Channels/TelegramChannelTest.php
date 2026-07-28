<?php

namespace Tests\Unit\Channels;

use App\Channels\TelegramChannel;
use App\DTO\NotificationData;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TelegramChannelTest extends TestCase
{
    public function test_send_returns_true_and_logs()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn ($msg) => str_contains($msg, 'Отправлено в telegram'));

        $channel = new TelegramChannel;
        $data = new NotificationData(
            user_id: 1,
            message: 'Test message',
            destination: '12345'
        );

        $result = $channel->send($data);

        $this->assertTrue($result);
    }
}
