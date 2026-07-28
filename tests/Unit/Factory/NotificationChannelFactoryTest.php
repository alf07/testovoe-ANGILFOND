<?php

namespace Tests\Unit\Factory;

use App\Contracts\NotificationChannelInterface;
use App\Factory\NotificationChannelFactory;
use App\Models\NotificationChannels;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationChannelFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_make_returns_channel_instance()
    {
        $config = NotificationChannels::factory()->create([
            'code' => 'email',
            'handler' => 'App\Channels\EmailChannel',
        ]);

        $factory = new NotificationChannelFactory;
        $channel = $factory->make('email');

        $this->assertInstanceOf(NotificationChannelInterface::class, $channel);
        $this->assertInstanceOf('App\Channels\EmailChannel', $channel);
    }

    public function test_make_throws_exception_if_channel_not_found()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Канал not_found не найден');

        $factory = new NotificationChannelFactory;
        $factory->make('not_found');
    }

    public function test_make_throws_exception_if_handler_invalid()
    {
        NotificationChannels::factory()->create([
            'code' => 'invalid',
            'handler' => 'Some\Invalid\Class',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Неверный обработчик канала Some\Invalid\Class');

        $factory = new NotificationChannelFactory;
        $factory->make('invalid');
    }
}
