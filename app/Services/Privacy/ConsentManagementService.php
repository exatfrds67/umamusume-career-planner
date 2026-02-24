<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Enums\ConsentType;
use App\Models\ConsentRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Consent Management Service
 *
 * Manages granular user consent for data sharing and processing activities.
 * Tracks consent history with IP and user agent for audit compliance.
 *
 * @see \App\Models\ConsentRecord
 * @see \App\Enums\ConsentType
 */
class ConsentManagementService
{
    /**
     * Grant consent for a specific type.
     */
    public function grantConsent(User $user, ConsentType $type, ?Request $request = null): ConsentRecord
    {
        return ConsentRecord::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'consent_type' => $type->value,
            ],
            [
                'granted' => true,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'granted_at' => now(),
                'revoked_at' => null,
            ]
        );
    }

    /**
     * Revoke consent for a specific type.
     */
    public function revokeConsent(User $user, ConsentType $type, ?Request $request = null): ConsentRecord
    {
        return ConsentRecord::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'consent_type' => $type->value,
            ],
            [
                'granted' => false,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'revoked_at' => now(),
            ]
        );
    }

    /**
     * Check if a user has granted a specific consent.
     */
    public function hasConsent(User $user, ConsentType $type): bool
    {
        $record = ConsentRecord::query()
            ->where('user_id', $user->id)
            ->where('consent_type', $type->value)
            ->first();

        return $record !== null && $record->isActive();
    }

    /**
     * Get all consent records for a user.
     *
     * @return Collection<int, ConsentRecord>
     */
    public function getUserConsents(User $user): Collection
    {
        return $user->consentRecords()->get();
    }

    /**
     * Get the current consent status for all types.
     *
     * @return array<string, bool>
     */
    public function getConsentStatus(User $user): array
    {
        $records = $this->getUserConsents($user)->keyBy(fn ($r) => $r->consent_type->value);

        $status = [];
        foreach (ConsentType::cases() as $type) {
            $record = $records->get($type->value);
            $status[$type->value] = $record !== null && $record->isActive();
        }

        return $status;
    }

    /**
     * Bulk update consent settings.
     *
     * @param  array<string, bool>  $consents
     * @return array<string, ConsentRecord>
     */
    public function updateConsents(User $user, array $consents, ?Request $request = null): array
    {
        $results = [];

        foreach ($consents as $typeValue => $granted) {
            $type = ConsentType::tryFrom($typeValue);

            if ($type === null) {
                continue;
            }

            $results[$typeValue] = $granted
                ? $this->grantConsent($user, $type, $request)
                : $this->revokeConsent($user, $type, $request);
        }

        return $results;
    }
}
