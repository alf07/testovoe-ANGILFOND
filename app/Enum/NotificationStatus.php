<?php

namespace App\Enum;

enum NotificationStatus: string
{
    case Processing = 'processing';
    case Sent = 'sent';
    case Error = 'error';
}
