<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Storage Mode Enum
 *
 * Defines the two storage modes for the application:
 * - Local: Data stored in browser localStorage, UUID-based routing, offline-capable
 * - Account: Data stored in database, numeric ID routing, full sync
 *
 * @see \App\Services\LocalStorageService
 */
enum StorageMode: string
{
    /**
     * Local Storage Mode
     *
     * Data is stored in the browser's localStorage.
     * Characters and plans use UUID-based identifiers.
     * Supports offline operation.
     */
    case LOCAL = 'local';

    /**
     * Account Storage Mode
     *
     * Data is stored in the database.
     * Characters and plans use numeric ID routing.
     * Requires authentication.
     */
    case ACCOUNT = 'account';

    /**
     * Get a human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::LOCAL => 'Local',
            self::ACCOUNT => 'Account',
        };
    }

    /**
     * Get a description for this storage mode.
     */
    public function description(): string
    {
        return match ($this) {
            self::LOCAL => 'Data stored in your browser. Works offline.',
            self::ACCOUNT => 'Data synced to your account. Accessible anywhere.',
        };
    }

    /**
     * Get the route prefix for this storage mode.
     */
    public function routePrefix(): string
    {
        return match ($this) {
            self::LOCAL => 'local',
            self::ACCOUNT => '',
        };
    }

    /**
     * Get the CSS badge color for display.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::LOCAL => 'amber',
            self::ACCOUNT => 'emerald',
        };
    }

    /**
     * Get the icon name for this storage mode.
     */
    public function icon(): string
    {
        return match ($this) {
            self::LOCAL => 'device-mobile',
            self::ACCOUNT => 'cloud',
        };
    }

    /**
     * Whether this mode supports offline operation.
     */
    public function supportsOffline(): bool
    {
        return $this === self::LOCAL;
    }

    /**
     * Whether this mode requires authentication.
     */
    public function requiresAuth(): bool
    {
        return $this === self::ACCOUNT;
    }

    /**
     * Determine storage mode from a request or session context.
     */
    public static function fromRequest(?\Illuminate\Http\Request $request = null): self
    {
        $request ??= request();

        // Check session first
        $sessionMode = $request->session()->get('storage_mode');
        if ($sessionMode instanceof self) {
            return $sessionMode;
        }
        if (is_string($sessionMode)) {
            return self::tryFrom($sessionMode) ?? self::ACCOUNT;
        }

        // If user is authenticated, default to account mode
        if ($request->user()) {
            return self::ACCOUNT;
        }

        // Default to local for guests
        return self::LOCAL;
    }

    /**
     * Check if a given string is a valid UUID (used for local mode routing).
     */
    public static function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
    }
}
