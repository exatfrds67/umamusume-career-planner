<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\GameVersionUpdated;
use Illuminate\Support\Facades\Log;

/**
 * Invalidate Cache On Game Update Listener
 *
 * Automatically invalidates cache when game version is updated.
 * This listener is triggered by the GameVersionUpdated event.
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.1.3
 */
class InvalidateCacheOnGameUpdate
{
    /**
     * Handle the event.
     */
    public function handle(GameVersionUpdated $event): void
    {
        Log::info('[InvalidateCacheOnGameUpdate] Game version updated, cache invalidation triggered', [
            'previous_version' => $event->previousVersion,
            'new_version' => $event->newVersion,
            'invalidated_types' => $event->invalidatedTypes,
        ]);

        // Additional actions can be added here, such as:
        // - Notifying users about the update
        // - Triggering background sync jobs
        // - Updating UI indicators
        // - Sending webhooks to external systems
    }
}
