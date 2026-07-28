<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function () {});

Route::post('notifications', [NotificationController::class, 'sendNotification']);
Route::get('/notifications/{status_id}', [NotificationController::class, 'getStatusNotificationById']);
Route::get('/users/{user_id}/notifications', [NotificationController::class, 'getHistoryNotificationByUserId']);
