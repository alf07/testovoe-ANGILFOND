<?php

namespace App\Contracts;

use App\DTO\NotificationData;

interface NotificationChannelInterface
{
    public function send(NotificationData $data): bool;
}
