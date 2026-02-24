<?php

declare(strict_types=1);

use App\Livewire\NotificationDropdown;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the notification dropdown component', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(NotificationDropdown::class)
        ->assertStatus(200)
        ->assertSee('View notifications');
});

it('shows zero unread count when no notifications exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test(NotificationDropdown::class);

    expect($component->get('unreadCount'))->toBe(0);
});

it('toggles the dropdown open and closed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(NotificationDropdown::class)
        ->assertSet('isOpen', false)
        ->call('toggleDropdown')
        ->assertSet('isOpen', true)
        ->call('toggleDropdown')
        ->assertSet('isOpen', false);
});

it('closes the dropdown', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(NotificationDropdown::class)
        ->call('toggleDropdown')
        ->assertSet('isOpen', true)
        ->call('closeDropdown')
        ->assertSet('isOpen', false);
});

it('shows empty state when no notifications', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(NotificationDropdown::class)
        ->call('toggleDropdown')
        ->assertSee('No notifications yet');
});

it('displays notifications when they exist', function () {
    $user = User::factory()->create();

    $user->notify(new class extends \Illuminate\Notifications\Notification
    {
        /** @return array<int, string> */
        public function via(mixed $notifiable): array
        {
            return ['database'];
        }

        /** @return array<string, mixed> */
        public function toArray(mixed $notifiable): array
        {
            return [
                'type' => 'training',
                'message' => 'Training session completed successfully',
            ];
        }
    });

    $this->actingAs($user);

    Livewire::test(NotificationDropdown::class)
        ->call('toggleDropdown')
        ->assertSee('Training session completed successfully');
});

it('shows unread count badge', function () {
    $user = User::factory()->create();

    $user->notify(new class extends \Illuminate\Notifications\Notification
    {
        /** @return array<int, string> */
        public function via(mixed $notifiable): array
        {
            return ['database'];
        }

        /** @return array<string, mixed> */
        public function toArray(mixed $notifiable): array
        {
            return [
                'type' => 'race',
                'message' => 'Race reminder',
            ];
        }
    });

    $this->actingAs($user);

    $component = Livewire::test(NotificationDropdown::class);

    expect($component->get('unreadCount'))->toBe(1);
});

it('marks a notification as read', function () {
    $user = User::factory()->create();

    $user->notify(new class extends \Illuminate\Notifications\Notification
    {
        /** @return array<int, string> */
        public function via(mixed $notifiable): array
        {
            return ['database'];
        }

        /** @return array<string, mixed> */
        public function toArray(mixed $notifiable): array
        {
            return [
                'type' => 'info',
                'message' => 'Test notification',
            ];
        }
    });

    $this->actingAs($user);

    $notificationId = $user->notifications()->first()->id;

    $component = Livewire::test(NotificationDropdown::class)
        ->call('markAsRead', $notificationId);

    expect($component->get('unreadCount'))->toBe(0);
});

it('marks all notifications as read', function () {
    $user = User::factory()->create();

    $notification = new class extends \Illuminate\Notifications\Notification
    {
        /** @return array<int, string> */
        public function via(mixed $notifiable): array
        {
            return ['database'];
        }

        /** @return array<string, mixed> */
        public function toArray(mixed $notifiable): array
        {
            return [
                'type' => 'info',
                'message' => 'Test notification',
            ];
        }
    };

    $user->notify(clone $notification);
    $user->notify(clone $notification);

    $this->actingAs($user);

    $component = Livewire::test(NotificationDropdown::class);

    expect($component->get('unreadCount'))->toBe(2);

    $component->call('markAllAsRead');

    expect($component->get('unreadCount'))->toBe(0);
});
