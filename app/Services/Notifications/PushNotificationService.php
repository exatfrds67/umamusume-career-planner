<?php

namespace App\Services\Notifications;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Collection;

class PushNotificationService
{
    /**
     * Subscribe a user to push notifications.
     *
     * @param  array{endpoint: string, keys: array{p256dh: string, auth: string}, contentEncoding?: string}  $subscriptionData
     * @return array{success: bool, subscription_id: int|null, message: string}
     */
    public function subscribe(User $user, array $subscriptionData): array
    {
        $subscription = PushSubscription::query()->updateOrCreate(
            ['endpoint' => $subscriptionData['endpoint']],
            [
                'user_id' => $user->id,
                'public_key' => $subscriptionData['keys']['p256dh'],
                'auth_token' => $subscriptionData['keys']['auth'],
                'content_encoding' => $subscriptionData['contentEncoding'] ?? 'aesgcm',
            ]
        );

        return [
            'success' => true,
            'subscription_id' => $subscription->id,
            'message' => 'Subscription created successfully.',
        ];
    }

    /**
     * Unsubscribe a user from push notifications.
     */
    public function unsubscribe(User $user, string $endpoint): bool
    {
        return PushSubscription::query()
            ->where('user_id', $user->id)
            ->where('endpoint', $endpoint)
            ->delete() > 0;
    }

    /**
     * Get all subscriptions for a user.
     *
     * @return Collection<int, PushSubscription>
     */
    public function getUserSubscriptions(User $user): Collection
    {
        return PushSubscription::query()
            ->where('user_id', $user->id)
            ->get();
    }

    /**
     * Update notification preferences for a subscription.
     *
     * @param  array{race_reminders?: bool, training_alerts?: bool, sync_notifications?: bool, quiet_hours_start?: string, quiet_hours_end?: string}  $preferences
     */
    public function updatePreferences(User $user, string $endpoint, array $preferences): bool
    {
        $subscription = PushSubscription::query()
            ->where('user_id', $user->id)
            ->where('endpoint', $endpoint)
            ->first();

        if (! $subscription) {
            return false;
        }

        $subscription->update([
            'notification_preferences' => $preferences,
        ]);

        return true;
    }

    /**
     * Check if a notification should be sent based on quiet hours.
     */
    public function isWithinQuietHours(PushSubscription $subscription): bool
    {
        $preferences = $subscription->notification_preferences ?? [];

        $start = $preferences['quiet_hours_start'] ?? '22:00';
        $end = $preferences['quiet_hours_end'] ?? '08:00';

        $now = now()->format('H:i');

        if ($start <= $end) {
            return $now >= $start && $now <= $end;
        }

        return $now >= $start || $now <= $end;
    }

    /**
     * Check if a notification type is enabled for a subscription.
     */
    public function isNotificationTypeEnabled(PushSubscription $subscription, string $type): bool
    {
        $preferences = $subscription->notification_preferences ?? [];

        return $preferences[$type] ?? true;
    }

    /**
     * Build the push notification payload.
     *
     * @param  array<string, mixed>  $data
     * @return array{title: string, body: string, icon: string, badge: string, data: array<string, mixed>}
     */
    public function buildPayload(string $title, string $body, string $url = '/', array $data = []): array
    {
        return [
            'title' => $title,
            'body' => $body,
            'icon' => '/images/app_logo/uma_musume_race_planner_logo_128.png',
            'badge' => '/images/app_logo/uma_musume_race_planner_logo_128.png',
            'data' => array_merge(['url' => $url], $data),
        ];
    }

    /**
     * Send a push notification to a specific user.
     *
     * @return array{sent: int, skipped: int, failed: int}
     */
    public function sendToUser(User $user, string $title, string $body, string $url = '/', string $notificationType = 'general'): array
    {
        $subscriptions = $this->getUserSubscriptions($user);
        $results = ['sent' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($subscriptions as $subscription) {
            if ($this->isWithinQuietHours($subscription)) {
                $results['skipped']++;

                continue;
            }

            if (! $this->isNotificationTypeEnabled($subscription, $notificationType)) {
                $results['skipped']++;

                continue;
            }

            $subscription->update(['last_notified_at' => now()]);
            $results['sent']++;
        }

        return $results;
    }

    /**
     * Get VAPID public key for client-side subscription.
     */
    public function getVapidPublicKey(): string
    {
        return (string) config('services.vapid.public_key', '');
    }
}
