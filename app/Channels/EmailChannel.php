<?php

namespace App\Channels;

use App\Contracts\NotificationChannelInterface;
use App\DTO\NotificationData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * @description только отправляем уведомление на email
 *
 * @todo $data убрать от сюда а передавать только то что нужно
 */
class EmailChannel implements NotificationChannelInterface
{
    public function send(NotificationData $data): bool
    {
        // try {
        // Mail::to($data->email)->send($data->message);
        Log::info('Отправил email  '.$data->destination);
        $result = true;

        // } catch (\Exception $e){
        //   Log::error('Ошибка отправки email уведомления'.$e->getMessage());
        //    $result = false;
        // }
        return $result;
    }
}
