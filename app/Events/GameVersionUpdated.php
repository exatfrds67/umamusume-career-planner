<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Game Version Updated Event
 *
 * Fired when the game version is updated, triggering automatic cache invalidation.
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.1.3
 */
class GameVersionUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly ?string $previousVersion,
        public readonly string $newVersion,
        public readonly array $invalidatedTypes = []
    ) {}
}
