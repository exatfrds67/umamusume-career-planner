<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public bool $isOpen = false;

    /**
     * Toggle the dropdown visibility.
     */
    public function toggleDropdown(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    /**
     * Close the dropdown.
     */
    public function closeDropdown(): void
    {
        $this->isOpen = false;
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $notificationId): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $notification = $user->notifications()->find($notificationId);
        $notification?->markAsRead();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $user->unreadNotifications->markAsRead();
    }

    /**
     * Get the user's recent notifications.
     *
     * @return Collection<int, \Illuminate\Notifications\DatabaseNotification>
     */
    public function getNotificationsProperty(): Collection
    {
        $user = auth()->user();
        if (! $user) {
            return collect();
        }

        return $user->notifications()->latest()->limit(10)->get();
    }

    /**
     * Get the count of unread notifications.
     */
    public function getUnreadCountProperty(): int
    {
        $user = auth()->user();
        if (! $user) {
            return 0;
        }

        return $user->unreadNotifications()->count();
    }

    public function render(): View
    {
        return view('livewire.notification-dropdown');
    }
}
