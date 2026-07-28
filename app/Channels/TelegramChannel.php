<?php

namespace App\Channels;

use App\Contracts\NotificationChannelInterface;
use App\DTO\NotificationData;
use Illuminate\Support\Facades\Log;

/**
 * @description только отправляем уведомление в телеграмм
 */
class TelegramChannel implements NotificationChannelInterface
{
    public function send(NotificationData $data): bool
    {
        // try {
        //  тут отправляем запрос к api телеграм и обрабатываем ответ
        Log::info('Отправлено в telegram  '.$data->destination);
        $result = true;

        // } catch (\Exception $e) {
        //    Log::error('Ошибка отправки уведомления в телеграмм'.$e->getMessage());
        //    $result = false;
        // }
        return $result;
    }
}
