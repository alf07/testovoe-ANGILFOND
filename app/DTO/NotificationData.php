<?php

namespace App\DTO;

/**
 * @description DTO для данных для отправки
 *
 * @todo разкоментировать email и telegram когда дойду о получения из бд пользователя этих данных
 */
final readonly class NotificationData
{
    public function __construct(
        public int $user_id,
        public string $message,
        public ?string $destination = null,
    ) {}
}
