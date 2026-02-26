<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Enums\DeletionStatus;
use App\Models\DeletionRequest;
use App\Models\User;
use App\Notifications\DeletionConfirmationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Data Deletion Service
 *
 * Handles account deletion requests with a 30-day grace period.
 * Supports request creation, cancellation, and permanent data deletion.
 *
 * @see \App\Models\DeletionRequest
 */
class DataDeletionService
{
    private const GRACE_PERIOD_DAYS = 30;

    /**
     * Create a new deletion request for the user.
     * Sends a confirmation notification.
     */
    public function requestDeletion(User $user, ?string $reason = null): DeletionRequest
    {
        $existingPending = DeletionRequest::query()
            ->where('user_id', $user->id)
            ->where('status', DeletionStatus::Pending)
            ->first();

        if ($existingPending) {
            return $existingPending;
        }

        $deletionRequest = DeletionRequest::query()->create([
            'user_id' => $user->id,
            'status' => DeletionStatus::Pending,
            'reason' => $reason,
            'grace_period_ends_at' => now()->addDays(self::GRACE_PERIOD_DAYS),
        ]);

        $user->notify(new DeletionConfirmationNotification($deletionRequest));

        return $deletionRequest;
    }

    /**
     * Cancel a pending deletion request.
     */
    public function cancelDeletion(DeletionRequest $deletionRequest): DeletionRequest
    {
        if ($deletionRequest->status !== DeletionStatus::Pending) {
            throw new \RuntimeException('Only pending deletion requests can be cancelled.');
        }

        $deletionRequest->update([
            'status' => DeletionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        return $deletionRequest->fresh() ?? $deletionRequest;
    }

    /**
     * Process expired deletion requests.
     * This should be called by a scheduled command.
     *
     * @return array{processed: int, failed: int}
     */
    public function processExpiredRequests(): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, DeletionRequest> $expiredRequests */
        $expiredRequests = DeletionRequest::query()
            ->where('status', DeletionStatus::Pending)
            ->where('grace_period_ends_at', '<=', now())
            ->with('user')
            ->get();

        $processed = 0;
        $failed = 0;

        foreach ($expiredRequests as $request) {
            try {
                $this->executeDeletion($request);
                $processed++;
            } catch (\Throwable $e) {
                Log::error('Failed to process deletion request', [
                    'deletion_request_id' => $request->id,
                    'user_id' => $request->user_id,
                    'error' => $e->getMessage(),
                ]);
                $request->update([
                    'status' => DeletionStatus::Failed,
                    'deletion_log' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        return ['processed' => $processed, 'failed' => $failed];
    }

    /**
     * Execute the actual data deletion for a request.
     */
    public function executeDeletion(DeletionRequest $deletionRequest): void
    {
        $user = $deletionRequest->user;

        if (! $user) {
            throw new \RuntimeException('User not found for deletion request.');
        }

        DB::transaction(function () use ($user, $deletionRequest): void {
            $log = [];

            $log[] = 'Deleting consent records: '.$user->consentRecords()->count();
            $user->consentRecords()->delete();

            $log[] = 'Deleting AI conversations: '.$user->aiConversations()->count();
            $user->aiConversations()->delete();

            $log[] = 'Deleting user preferences: '.$user->userPreferences()->count();
            $user->userPreferences()->delete();

            $user->load('characters.careers');

            foreach ($user->characters as $character) {
                foreach ($character->careers as $career) {
                    $career->trainingSessions()->delete();
                    $career->races()->delete();
                }
                $character->careers()->delete();
            }
            $log[] = 'Deleting characters: '.$user->characters()->count();
            $user->characters()->delete();

            $log[] = 'Revoking API tokens';
            $user->tokens()->delete();

            $deletionRequest->update([
                'status' => DeletionStatus::Completed,
                'completed_at' => now(),
                'deletion_log' => implode("\n", $log),
            ]);

            $user->delete();
        });
    }

    /**
     * Check if a user has a pending deletion request.
     */
    public function hasPendingDeletion(User $user): bool
    {
        return DeletionRequest::query()
            ->where('user_id', $user->id)
            ->where('status', DeletionStatus::Pending)
            ->exists();
    }

    /**
     * Get the active deletion request for a user.
     */
    public function getActiveDeletionRequest(User $user): ?DeletionRequest
    {
        return DeletionRequest::query()
            ->where('user_id', $user->id)
            ->where('status', DeletionStatus::Pending)
            ->first();
    }
}
