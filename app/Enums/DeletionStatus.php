<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Deletion Status Enum
 *
 * Defines the lifecycle states of an account deletion request.
 * Supports a 30-day grace period before permanent deletion.
 *
 * @see \App\Models\DeletionRequest
 * @see \App\Services\Privacy\DataDeletionService
 */
enum DeletionStatus: string
{
    /**
     * Deletion has been requested but grace period is active.
     * User can still cancel during this period.
     */
    case Pending = 'pending';

    /**
     * User cancelled the deletion during the grace period.
     */
    case Cancelled = 'cancelled';

    /**
     * Grace period expired and data has been permanently deleted.
     */
    case Completed = 'completed';

    /**
     * Deletion processing failed due to a system error.
     */
    case Failed = 'failed';

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending (Grace Period)',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
        };
    }
}
