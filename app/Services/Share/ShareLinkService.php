<?php

namespace App\Services\Share;

use App\Models\Career;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ShareLinkService
{
    private const CACHE_PREFIX = 'share_link_';

    private const DEFAULT_EXPIRATION_DAYS = 30;

    private const VIEW_COUNT_PREFIX = 'share_views_';

    /**
     * Create a shareable link for a career.
     *
     * @param  array{privacy?: string, password?: string|null, expiration_days?: int}  $options
     * @return array{token: string, url: string, privacy: string, expires_at: string|null, created_at: string}
     */
    public function createShareLink(Career $career, array $options = []): array
    {
        $token = Str::uuid()->toString();
        $privacy = $options['privacy'] ?? 'unlisted';
        $password = $options['password'] ?? null;
        $expirationDays = $options['expiration_days'] ?? self::DEFAULT_EXPIRATION_DAYS;

        $expiresAt = now()->addDays($expirationDays);
        $ttlSeconds = $expirationDays * 86400;

        $shareData = [
            'token' => $token,
            'career_id' => $career->id,
            'user_id' => $career->character?->user_id,
            'privacy' => $privacy,
            'password_hash' => $password ? bcrypt($password) : null,
            'expires_at' => $expiresAt->toIso8601String(),
            'created_at' => now()->toIso8601String(),
            'career_snapshot' => $this->createCareerSnapshot($career),
        ];

        Cache::put(self::CACHE_PREFIX.$token, $shareData, $ttlSeconds);
        Cache::put(self::VIEW_COUNT_PREFIX.$token, 0, $ttlSeconds);

        return [
            'token' => $token,
            'url' => '/share/'.$token,
            'privacy' => $privacy,
            'expires_at' => $expiresAt->toIso8601String(),
            'created_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Access a shared career link.
     *
     * @return array{success: bool, data?: array<string, mixed>, error?: string}
     */
    public function accessShareLink(string $token, ?string $password = null): array
    {
        $shareData = Cache::get(self::CACHE_PREFIX.$token);

        if (! $shareData) {
            return ['success' => false, 'error' => 'Share link not found or expired.'];
        }

        if (isset($shareData['expires_at']) && now()->isAfter($shareData['expires_at'])) {
            Cache::forget(self::CACHE_PREFIX.$token);

            return ['success' => false, 'error' => 'Share link has expired.'];
        }

        if (! empty($shareData['password_hash'])) {
            if (! $password || ! password_verify($password, $shareData['password_hash'])) {
                return ['success' => false, 'error' => 'Invalid password.'];
            }
        }

        $this->incrementViewCount($token);

        return [
            'success' => true,
            'data' => $shareData['career_snapshot'],
        ];
    }

    /**
     * Revoke a share link.
     */
    public function revokeShareLink(string $token): bool
    {
        $exists = Cache::has(self::CACHE_PREFIX.$token);

        if ($exists) {
            Cache::forget(self::CACHE_PREFIX.$token);
            Cache::forget(self::VIEW_COUNT_PREFIX.$token);

            return true;
        }

        return false;
    }

    /**
     * Get view count for a share link.
     */
    public function getViewCount(string $token): int
    {
        return (int) Cache::get(self::VIEW_COUNT_PREFIX.$token, 0);
    }

    /**
     * Increment view count for a share link.
     */
    private function incrementViewCount(string $token): void
    {
        $count = $this->getViewCount($token);
        $shareData = Cache::get(self::CACHE_PREFIX.$token);

        if ($shareData && isset($shareData['expires_at'])) {
            $remainingSeconds = max(1, now()->diffInSeconds($shareData['expires_at']));
            Cache::put(self::VIEW_COUNT_PREFIX.$token, $count + 1, (int) $remainingSeconds);
        }
    }

    /**
     * Check if a share link exists and is valid.
     */
    public function isValidShareLink(string $token): bool
    {
        $shareData = Cache::get(self::CACHE_PREFIX.$token);

        if (! $shareData) {
            return false;
        }

        if (isset($shareData['expires_at']) && now()->isAfter($shareData['expires_at'])) {
            return false;
        }

        return true;
    }

    /**
     * Get share link metadata without accessing the content.
     *
     * @return array{exists: bool, privacy?: string, expires_at?: string, view_count?: int, requires_password?: bool}
     */
    public function getShareLinkInfo(string $token): array
    {
        $shareData = Cache::get(self::CACHE_PREFIX.$token);

        if (! $shareData) {
            return ['exists' => false];
        }

        return [
            'exists' => true,
            'privacy' => $shareData['privacy'] ?? 'unlisted',
            'expires_at' => $shareData['expires_at'] ?? null,
            'view_count' => $this->getViewCount($token),
            'requires_password' => ! empty($shareData['password_hash']),
        ];
    }

    /**
     * Create a snapshot of career data for the share.
     *
     * @return array<string, mixed>
     */
    private function createCareerSnapshot(Career $career): array
    {
        $career->loadMissing('character');

        return [
            'career_name' => $career->career_name ?? "Career #{$career->id}",
            'character_name' => $career->character?->name ?? 'Unknown',
            'scenario_type' => $career->scenario_type ?? 'Unknown',
            'status' => $career->status ?? 'Unknown',
            'total_turns' => $career->current_turn ?? 0,
            'stats' => [
                'speed' => $career->final_speed ?? 0,
                'stamina' => $career->final_stamina ?? 0,
                'power' => $career->final_power ?? 0,
                'guts' => $career->final_guts ?? 0,
                'wit' => $career->final_wit ?? 0,
                'sp' => $career->final_sp ?? 0,
            ],
            'total_stats' => ($career->final_speed ?? 0) + ($career->final_stamina ?? 0)
                + ($career->final_power ?? 0) + ($career->final_guts ?? 0) + ($career->final_wit ?? 0),
            'snapshot_at' => now()->toIso8601String(),
        ];
    }
}
