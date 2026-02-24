<?php

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\Notifications\PushNotificationService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = new PushNotificationService;
});

it('subscribes a user to push notifications', function () {
    $result = $this->service->subscribe($this->user, [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'keys' => [
            'p256dh' => 'test-public-key-value',
            'auth' => 'test-auth-token-value',
        ],
    ]);

    expect($result['success'])->toBeTrue();
    expect($result['subscription_id'])->toBeInt();
    expect($result['message'])->toBe('Subscription created successfully.');

    $this->assertDatabaseHas('push_subscriptions', [
        'user_id' => $this->user->id,
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'public_key' => 'test-public-key-value',
        'auth_token' => 'test-auth-token-value',
        'content_encoding' => 'aesgcm',
    ]);
});

it('updates existing subscription on duplicate endpoint', function () {
    $this->service->subscribe($this->user, [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'keys' => [
            'p256dh' => 'old-key',
            'auth' => 'old-auth',
        ],
    ]);

    $this->service->subscribe($this->user, [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'keys' => [
            'p256dh' => 'new-key',
            'auth' => 'new-auth',
        ],
    ]);

    expect(PushSubscription::query()->where('endpoint', 'https://fcm.googleapis.com/fcm/send/test-endpoint')->count())->toBe(1);

    $subscription = PushSubscription::query()->where('endpoint', 'https://fcm.googleapis.com/fcm/send/test-endpoint')->first();
    expect($subscription->public_key)->toBe('new-key');
    expect($subscription->auth_token)->toBe('new-auth');
});

it('subscribes with custom content encoding', function () {
    $this->service->subscribe($this->user, [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'keys' => [
            'p256dh' => 'test-key',
            'auth' => 'test-auth',
        ],
        'contentEncoding' => 'aes128gcm',
    ]);

    $this->assertDatabaseHas('push_subscriptions', [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'content_encoding' => 'aes128gcm',
    ]);
});

it('unsubscribes a user from push notifications', function () {
    $this->service->subscribe($this->user, [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'keys' => ['p256dh' => 'key', 'auth' => 'auth'],
    ]);

    $result = $this->service->unsubscribe($this->user, 'https://fcm.googleapis.com/fcm/send/test-endpoint');

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('push_subscriptions', [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
    ]);
});

it('returns false when unsubscribing non-existent endpoint', function () {
    $result = $this->service->unsubscribe($this->user, 'https://fcm.googleapis.com/non-existent');

    expect($result)->toBeFalse();
});

it('prevents unsubscribing another users endpoint', function () {
    $otherUser = User::factory()->create();

    $this->service->subscribe($otherUser, [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/other-endpoint',
        'keys' => ['p256dh' => 'key', 'auth' => 'auth'],
    ]);

    $result = $this->service->unsubscribe($this->user, 'https://fcm.googleapis.com/fcm/send/other-endpoint');

    expect($result)->toBeFalse();
    $this->assertDatabaseHas('push_subscriptions', [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/other-endpoint',
    ]);
});

it('gets all subscriptions for a user', function () {
    $this->service->subscribe($this->user, [
        'endpoint' => 'https://fcm.googleapis.com/endpoint-1',
        'keys' => ['p256dh' => 'key1', 'auth' => 'auth1'],
    ]);
    $this->service->subscribe($this->user, [
        'endpoint' => 'https://fcm.googleapis.com/endpoint-2',
        'keys' => ['p256dh' => 'key2', 'auth' => 'auth2'],
    ]);

    $subscriptions = $this->service->getUserSubscriptions($this->user);

    expect($subscriptions)->toHaveCount(2);
});

it('does not return other users subscriptions', function () {
    $otherUser = User::factory()->create();

    $this->service->subscribe($otherUser, [
        'endpoint' => 'https://fcm.googleapis.com/other-endpoint',
        'keys' => ['p256dh' => 'key', 'auth' => 'auth'],
    ]);

    $subscriptions = $this->service->getUserSubscriptions($this->user);

    expect($subscriptions)->toHaveCount(0);
});

it('updates notification preferences for a subscription', function () {
    $this->service->subscribe($this->user, [
        'endpoint' => 'https://fcm.googleapis.com/endpoint',
        'keys' => ['p256dh' => 'key', 'auth' => 'auth'],
    ]);

    $result = $this->service->updatePreferences($this->user, 'https://fcm.googleapis.com/endpoint', [
        'race_reminders' => false,
        'training_alerts' => true,
        'quiet_hours_start' => '23:00',
        'quiet_hours_end' => '07:00',
    ]);

    expect($result)->toBeTrue();

    $subscription = PushSubscription::query()->where('endpoint', 'https://fcm.googleapis.com/endpoint')->first();
    expect($subscription->notification_preferences['race_reminders'])->toBeFalse();
    expect($subscription->notification_preferences['training_alerts'])->toBeTrue();
    expect($subscription->notification_preferences['quiet_hours_start'])->toBe('23:00');
});

it('returns false when updating preferences for non-existent subscription', function () {
    $result = $this->service->updatePreferences($this->user, 'https://non-existent.endpoint', [
        'race_reminders' => false,
    ]);

    expect($result)->toBeFalse();
});

it('detects quiet hours correctly for overnight period', function () {
    $subscription = PushSubscription::query()->create([
        'user_id' => $this->user->id,
        'endpoint' => 'https://test.endpoint',
        'public_key' => 'key',
        'auth_token' => 'auth',
        'notification_preferences' => [
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '08:00',
        ],
    ]);

    $this->travelTo(now()->setTime(23, 0));
    expect($this->service->isWithinQuietHours($subscription))->toBeTrue();

    $this->travelTo(now()->setTime(3, 0));
    expect($this->service->isWithinQuietHours($subscription))->toBeTrue();

    $this->travelTo(now()->setTime(12, 0));
    expect($this->service->isWithinQuietHours($subscription))->toBeFalse();

    $this->travelTo(now()->setTime(15, 0));
    expect($this->service->isWithinQuietHours($subscription))->toBeFalse();
});

it('detects quiet hours correctly for same-day period', function () {
    $subscription = PushSubscription::query()->create([
        'user_id' => $this->user->id,
        'endpoint' => 'https://test.endpoint/same-day',
        'public_key' => 'key',
        'auth_token' => 'auth',
        'notification_preferences' => [
            'quiet_hours_start' => '13:00',
            'quiet_hours_end' => '15:00',
        ],
    ]);

    $this->travelTo(now()->setTime(14, 0));
    expect($this->service->isWithinQuietHours($subscription))->toBeTrue();

    $this->travelTo(now()->setTime(16, 0));
    expect($this->service->isWithinQuietHours($subscription))->toBeFalse();
});

it('uses default quiet hours when not configured', function () {
    $subscription = PushSubscription::query()->create([
        'user_id' => $this->user->id,
        'endpoint' => 'https://test.endpoint/defaults',
        'public_key' => 'key',
        'auth_token' => 'auth',
        'notification_preferences' => [],
    ]);

    $this->travelTo(now()->setTime(23, 30));
    expect($this->service->isWithinQuietHours($subscription))->toBeTrue();

    $this->travelTo(now()->setTime(10, 0));
    expect($this->service->isWithinQuietHours($subscription))->toBeFalse();
});

it('checks if notification type is enabled', function () {
    $subscription = PushSubscription::query()->create([
        'user_id' => $this->user->id,
        'endpoint' => 'https://test.endpoint/types',
        'public_key' => 'key',
        'auth_token' => 'auth',
        'notification_preferences' => [
            'race_reminders' => true,
            'training_alerts' => false,
        ],
    ]);

    expect($this->service->isNotificationTypeEnabled($subscription, 'race_reminders'))->toBeTrue();
    expect($this->service->isNotificationTypeEnabled($subscription, 'training_alerts'))->toBeFalse();
});

it('defaults to enabled for unconfigured notification types', function () {
    $subscription = PushSubscription::query()->create([
        'user_id' => $this->user->id,
        'endpoint' => 'https://test.endpoint/defaults-type',
        'public_key' => 'key',
        'auth_token' => 'auth',
        'notification_preferences' => [],
    ]);

    expect($this->service->isNotificationTypeEnabled($subscription, 'some_new_type'))->toBeTrue();
});

it('builds notification payload correctly', function () {
    $payload = $this->service->buildPayload('Test Title', 'Test Body', '/test-url', ['custom' => 'data']);

    expect($payload['title'])->toBe('Test Title');
    expect($payload['body'])->toBe('Test Body');
    expect($payload['icon'])->toContain('logo');
    expect($payload['badge'])->toContain('logo');
    expect($payload['data']['url'])->toBe('/test-url');
    expect($payload['data']['custom'])->toBe('data');
});

it('builds payload with default url', function () {
    $payload = $this->service->buildPayload('Title', 'Body');

    expect($payload['data']['url'])->toBe('/');
});

it('sends notification to user respecting preferences', function () {
    PushSubscription::query()->create([
        'user_id' => $this->user->id,
        'endpoint' => 'https://test.endpoint/send',
        'public_key' => 'key',
        'auth_token' => 'auth',
        'notification_preferences' => [
            'race_reminders' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '08:00',
        ],
    ]);

    $this->travelTo(now()->setTime(12, 0));

    $results = $this->service->sendToUser($this->user, 'Race Reminder', 'Your race starts soon!', '/races', 'race_reminders');

    expect($results['sent'])->toBe(1);
    expect($results['skipped'])->toBe(0);
    expect($results['failed'])->toBe(0);
});

it('skips notification during quiet hours', function () {
    PushSubscription::query()->create([
        'user_id' => $this->user->id,
        'endpoint' => 'https://test.endpoint/quiet',
        'public_key' => 'key',
        'auth_token' => 'auth',
        'notification_preferences' => [
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '08:00',
        ],
    ]);

    $this->travelTo(now()->setTime(23, 0));

    $results = $this->service->sendToUser($this->user, 'Test', 'Test Body');

    expect($results['sent'])->toBe(0);
    expect($results['skipped'])->toBe(1);
});

it('skips notification when type is disabled', function () {
    PushSubscription::query()->create([
        'user_id' => $this->user->id,
        'endpoint' => 'https://test.endpoint/disabled',
        'public_key' => 'key',
        'auth_token' => 'auth',
        'notification_preferences' => [
            'training_alerts' => false,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '08:00',
        ],
    ]);

    $this->travelTo(now()->setTime(12, 0));

    $results = $this->service->sendToUser($this->user, 'Training Alert', 'Time to train!', '/training', 'training_alerts');

    expect($results['sent'])->toBe(0);
    expect($results['skipped'])->toBe(1);
});

it('updates last_notified_at when sending notification', function () {
    PushSubscription::query()->create([
        'user_id' => $this->user->id,
        'endpoint' => 'https://test.endpoint/timestamp',
        'public_key' => 'key',
        'auth_token' => 'auth',
        'notification_preferences' => [
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '08:00',
        ],
    ]);

    $this->travelTo(now()->setTime(12, 0));

    $this->service->sendToUser($this->user, 'Test', 'Body');

    $subscription = PushSubscription::query()->where('endpoint', 'https://test.endpoint/timestamp')->first();
    expect($subscription->last_notified_at)->not->toBeNull();
});

it('returns vapid public key from config', function () {
    config(['services.vapid.public_key' => 'test-vapid-key-value']);

    expect($this->service->getVapidPublicKey())->toBe('test-vapid-key-value');
});

it('returns empty string when vapid key not configured', function () {
    config(['services.vapid' => []]);

    expect($this->service->getVapidPublicKey())->toBe('');
});

it('sends to multiple subscriptions for one user', function () {
    PushSubscription::query()->create([
        'user_id' => $this->user->id,
        'endpoint' => 'https://test.endpoint/device-1',
        'public_key' => 'key1',
        'auth_token' => 'auth1',
        'notification_preferences' => ['quiet_hours_start' => '22:00', 'quiet_hours_end' => '08:00'],
    ]);
    PushSubscription::query()->create([
        'user_id' => $this->user->id,
        'endpoint' => 'https://test.endpoint/device-2',
        'public_key' => 'key2',
        'auth_token' => 'auth2',
        'notification_preferences' => ['quiet_hours_start' => '22:00', 'quiet_hours_end' => '08:00'],
    ]);

    $this->travelTo(now()->setTime(12, 0));

    $results = $this->service->sendToUser($this->user, 'Test', 'Body');

    expect($results['sent'])->toBe(2);
});
