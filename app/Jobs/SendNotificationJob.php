<?php

namespace App\Jobs;

use App\DTO\NotificationData;
use App\Enum\NotificationStatus;
use App\Factory\NotificationChannelFactory;
use App\Models\History;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @description Максимальное количество попыток.
     */
    public int $tries = 3;

    /**
     * @description Запись уведомления из таблицы histories.
     */
    protected History $notification;

    /**
     * @description Создать экземпляр задачи.
     */
    public function __construct(History $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @description Выполнить задачу.
     */
    public function handle(NotificationChannelFactory $factory): void
    {
        try {
            // 1. Получаем обработчик канала через фабрику
            $channel = $factory->make($this->notification->channel);

            // 2. Отправляем уведомление
            $data = new NotificationData(
                user_id: $this->notification->user_id,
                message: $this->notification->message,
                destination: $this->notification->destination
            );

            $success = $channel->send($data);
            // $success = $channel->send(
            //    $this->notification->message,
            //    $this->notification->destination
            // );

            // 3. Обрабатываем результат
            if ($success) {
                $this->markAsSent();
            } else {
                $this->handleFailure('Канал вернул false');
            }
        } catch (Exception $e) {
            // Ловим любые исключения (ошибки соединения, таймауты, неверный канал и т.д.)
            Log::error("Ошибка отправки уведомления #{$this->notification->id}: ".$e->getMessage());
            $this->handleFailure($e->getMessage());
        }
    }

    /**
     * @description Пометить уведомление как успешно отправленное.
     */
    protected function markAsSent(): void
    {
        $this->notification->update([
            'status' => NotificationStatus::Sent,
            'sent_at' => now(),
        ]);
        Log::info("Уведомление #{$this->notification->id} успешно отправлено.");
    }

    /**
     * @description Пометить уведомление как ошибочное (если превышено число попыток).
     */
    protected function markAsError(string $errorMessage): void
    {
        $this->notification->update([
            'status' => NotificationStatus::Error,
            'error_message' => $errorMessage,
        ]);
        Log::error("Уведомление #{$this->notification->id} помечено как ошибка: {$errorMessage}");
    }

    /**
     *@description Обработка сбоя отправки.
     */
    protected function handleFailure(string $errorMessage): void
    {
        // Увеличиваем счётчик попыток
        $this->notification->increment('attempts');

        // Если попытки исчерпаны – помечаем как ошибку
        if ($this->notification->attempts >= $this->tries) {
            $this->markAsError($errorMessage);

            return;
        }

        // Иначе выбрасываем исключение, чтобы Job попал в повторную попытку
        Log::warning("Повторная попытка #{$this->notification->attempts} для уведомления #{$this->notification->id}");
        throw new Exception("Повторная отправка (попытка {$this->notification->attempts}): {$errorMessage}");
    }

    /**
     * @description Задержки между попытками в секундах.
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }
}
