<?php

namespace App\Models;

use App\Enum\NotificationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $channel
 * @property NotificationStatus $status
 * @property string $message
 * @property string $destination
 * @property int $attempts
 * @property string|null $error_message
 * @property Carbon|null $sent_at
 */
class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'channel',
        'status',
        'message',
        'destination',
        'attempts',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'status' => NotificationStatus::class,
    ];
}
