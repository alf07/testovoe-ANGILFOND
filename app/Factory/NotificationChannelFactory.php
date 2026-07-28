<?php

namespace App\Factory;

use App\Contracts\NotificationChannelInterface;
use App\Models\NotificationChannels;
use Exception;

class NotificationChannelFactory
{
    public function make(
        string $channel
    ): NotificationChannelInterface {

        $config = NotificationChannels::query()
            ->where('code', $channel)
            ->first();

        if (! $config) {
            throw new Exception(
                "Канал {$channel} не найден"
            );
        }

        $class = $config->handler;

        if (! is_subclass_of(
            $class,
            NotificationChannelInterface::class
        )) {
            throw new Exception(
                "Неверный обработчик канала {$class}"
            );
        }

        return app($class);
    }
}
