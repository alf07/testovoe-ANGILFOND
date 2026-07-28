<?php

namespace App\Http\Controllers;

use App\DTO\NotificationData;
use App\Http\Requests\HistoryRequest;
use App\Http\Requests\SendNotificationRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function sendNotification(SendNotificationRequest $request): JsonResponse
    {
        $validation = $request->validated();
        $dto = new NotificationData(
            user_id: $validation['user_id'],
            message: $validation['message'],
        );
        $this->notificationService->send($dto);

        return response()->json([
            'success' => true,
        ]);
    }

    public function getStatusNotificationById(HistoryRequest $request)
    {
        $statusId = $request->route('status_id');
        if (! $statusId) {
            abort(404);
        }

        $response = $this->notificationService->getStatusNotificationById((int) $statusId);

        return response()->json(['status' => $response->status]);

    }

    public function getHistoryNotificationByUserId(HistoryRequest $request)
    {
        $validation = $request->validated();

        $response = $this->notificationService->getHistoryNotificationByUserId(
            (int) $request->route('user_id'),
            $validation['status'] ?? null,
            $validation['channel'] ?? null
        );

        return response()->json($response);
    }
}
