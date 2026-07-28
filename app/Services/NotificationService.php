<?php

namespace App\Services;

use App\DTO\NotificationData;
use App\Enum\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\History;
use App\Models\Users;

/**
 * @todo все что связано с историей можно вынести в отдельный сервис
 */
class NotificationService
{
    // public function __construct(
    //    private NotificationDispatcher $dispatcher
    // ) {}
    /**
     * @return mixed
     */
    public function findUser(int $user_id)
    {
        return Users::findOrFail($user_id);
    }

    public function saveHistory(int $user_id, string $message, string $channel, string $destination): History
    {
        $history = new History;
        $history->user_id = $user_id;
        $history->channel = $channel;
        $history->status = NotificationStatus::Processing;
        $history->message = $message;
        $history->destination = $destination;
        $history->attempts = 0;

        $history->save();

        return $history;
    }

    public function getStatusNotificationById(int $status_id)
    {
        return History::findOrFail($status_id);
    }

    public function getHistoryNotificationByUserId(
        int $user_id,
        ?string $status = null,
        ?string $channel = null,
    ) {
        $query = History::query()->where('user_id', $user_id);
        if ($status) {
            $query->where('status', $status);
        }
        if ($channel) {
            $query->where('channel', $channel);
        }

        return $query->get();
    }

    /**
     * @return void
     */
    public function send(NotificationData $data)
    {
        /**
         * @description получаем пользователя по id из БД
         */
        $user = $this->findUser($data->user_id);
        $channels = $user->channels;

        foreach ($channels as $channelName) {

            $history = $this->saveHistory(
                $user->id,
                $data->message,
                $channelName->channel,
                $channelName->destination
            );

            // Отправляем задачу в очередь для этого конкретного канала
            SendNotificationJob::dispatch($history);
        }

    }
}
