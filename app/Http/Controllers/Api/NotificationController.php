<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    /**
     * Get paginated notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $page = max($request->integer('page', 1), 1);

        $result = $this->notificationService->getUserNotifications(
            $user,
            $perPage,
            $page,
        );

        return response()->json($result);
    }

    /**
     * Get unread notifications for the header bell dropdown.
     */
    public function unread(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $result = $this->notificationService->getUnreadForBell(
            $user,
            limit: 10,
        );

        return response()->json($result);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $success = $this->notificationService->markAsRead($user, $id);

        if (! $success) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        return response()->json(['message' => 'Notification marked as read.']);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $count = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'message' => "{$count} notification(s) marked as read.",
            'count' => $count,
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $success = $this->notificationService->deleteNotification($user, $id);

        if (! $success) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        return response()->json(['message' => 'Notification deleted.']);
    }

    /**
     * Clean old read notifications.
     */
    public function cleanup(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $days = max($request->integer('older_than_days', 30), 1);
        $count = $this->notificationService->cleanOldNotifications($user, $days);

        return response()->json([
            'message' => "{$count} old notification(s) cleaned up.",
            'count' => $count,
        ]);
    }
}
