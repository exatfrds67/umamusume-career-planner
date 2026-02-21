<?php

namespace App\Services;

use App\Models\Career;
use App\Models\Race;
use App\Models\User;
use App\Notifications\CriticalAlertNotification;
use App\Notifications\RaceReadyNotification;
use App\Notifications\TrainingReminderNotification;
use Illuminate\Notifications\DatabaseNotification;

class NotificationService
{
    /**
     * Get paginated notifications for a user.
     *
     * @return array{notifications: \Illuminate\Support\Collection<int, array{id: string, type: mixed, icon: mixed, title: mixed, message: mixed, detail: mixed, action_url: mixed, action_label: mixed, priority: mixed, read: bool, read_at: string|null, created_at: string|null, time_ago: string|null}>, unread_count: int<0, max>, total: int<0, max>}
     */
    public function getUserNotifications(User $user, int $perPage = 20, int $page = 1): array
    {
        $query = $user->notifications();
        $total = $query->count();
        $unreadCount = $user->unreadNotifications()->count();

        $notifications = $query
            ->orderByDesc('created_at')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn (DatabaseNotification $n) => [
                'id' => $n->id,
                'type' => $n->data['type'] ?? 'unknown',
                'icon' => $n->data['icon'] ?? '🔔',
                'title' => $n->data['title'] ?? 'Notification',
                'message' => $n->data['message'] ?? '',
                'detail' => $n->data['detail'] ?? '',
                'action_url' => $n->data['action_url'] ?? '',
                'action_label' => $n->data['action_label'] ?? 'View',
                'priority' => $n->data['priority'] ?? 'normal',
                'read' => $n->read_at !== null,
                'read_at' => $n->read_at?->toISOString(),
                'created_at' => $n->created_at?->toISOString(),
                'time_ago' => $n->created_at?->diffForHumans(),
            ]);

        return [ // @phpstan-ignore return.type (Collection template invariance)
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'total' => $total,
        ];
    }

    /**
     * Get only unread notifications for header bell.
     *
     * @return array{notifications: \Illuminate\Support\Collection<int, array{id: string, icon: mixed, title: mixed, message: mixed, action_url: mixed, priority: mixed, time_ago: string|null}>, unread_count: int<0, max>}
     */
    public function getUnreadForBell(User $user, int $limit = 10): array
    {
        $unread = $user->unreadNotifications()
            ->take($limit)
            ->get()
            ->map(fn (DatabaseNotification $n) => [
                'id' => $n->id,
                'icon' => $n->data['icon'] ?? '🔔',
                'title' => $n->data['title'] ?? 'Notification',
                'message' => $n->data['message'] ?? '',
                'action_url' => $n->data['action_url'] ?? '',
                'priority' => $n->data['priority'] ?? 'normal',
                'time_ago' => $n->created_at?->diffForHumans(),
            ]);

        return [ // @phpstan-ignore return.type (Collection template invariance)
            'notifications' => $unread,
            'unread_count' => $user->unreadNotifications()->count(),
        ];
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if (! $notification) {
            return false;
        }

        $notification->markAsRead();

        return true;
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(User $user): int
    {
        $count = $user->unreadNotifications()->count();
        $user->unreadNotifications->markAsRead();

        return $count;
    }

    /**
     * Delete a specific notification.
     */
    public function deleteNotification(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if (! $notification) {
            return false;
        }

        $notification->delete();

        return true;
    }

    /**
     * Delete all read notifications older than the given days.
     */
    public function cleanOldNotifications(User $user, int $olderThanDays = 30): int
    {
        /** @var int $deleted */
        $deleted = $user->notifications()
            ->whereNotNull('read_at')
            ->where('created_at', '<', now()->subDays($olderThanDays))
            ->delete();

        return $deleted;
    }

    /**
     * Send a training reminder notification.
     */
    public function sendTrainingReminder(
        User $user,
        Career $career,
        int $currentTurn,
        string $suggestedTraining = '',
    ): void {
        $user->notify(new TrainingReminderNotification($career, $currentTurn, $suggestedTraining));
    }

    /**
     * Send a race-ready notification.
     */
    public function sendRaceReady(User $user, Career $career, Race $race): void
    {
        $user->notify(new RaceReadyNotification($career, $race));
    }

    /**
     * Send a critical alert notification.
     */
    public function sendCriticalAlert(
        User $user,
        string $title,
        string $message,
        string $severity = 'critical',
        string $actionUrl = '',
        string $actionLabel = 'View Details',
    ): void {
        $user->notify(new CriticalAlertNotification($title, $message, $severity, $actionUrl, $actionLabel));
    }

    /**
     * Send a critical alert to all admin users.
     */
    public function broadcastCriticalAlert(
        string $title,
        string $message,
        string $severity = 'critical',
    ): int {
        $admins = User::where('is_admin', true)->get();
        $count = 0;

        foreach ($admins as $admin) {
            $this->sendCriticalAlert($admin, $title, $message, $severity);
            $count++;
        }

        return $count;
    }
}
