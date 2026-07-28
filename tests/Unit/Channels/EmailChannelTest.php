<?php

namespace Tests\Unit\Channels;

use App\Channels\EmailChannel;
use App\DTO\NotificationData;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class EmailChannelTest extends TestCase
{
    public function test_send_returns_true_and_logs()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn ($msg) => str_contains($msg, 'Отправил email'));

        $channel = new EmailChannel;
        $data = new NotificationData(
            user_id: 1,
            message: 'Test message',
            destination: 'test@example.com'
        );

        $result = $channel->send($data);

        $this->assertTrue($result);
    }
}
