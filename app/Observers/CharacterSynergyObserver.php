<?php

namespace App\Observers;

use App\Models\Character;

/**
 * Invalidates the cached synergy_snapshot when fields affecting synergy change.
 */
class CharacterSynergyObserver
{
    /**
     * Fields that, when changed, should invalidate the synergy cache.
     *
     * @var array<int, string>
     */
    private const SYNERGY_FIELDS = [
        'current_stats',
        'team_composition',
        'inherited_factors',
        'scenario_type',
    ];

    /**
     * Handle the Character "updated" event.
     */
    public function updated(Character $character): void
    {
        if ($this->synergyFieldsChanged($character)) {
            $character->updateQuietly([
                'synergy_snapshot' => null,
                'synergy_computed_at' => null,
            ]);
        }
    }

    /**
     * Check whether any synergy-relevant field was changed.
     */
    private function synergyFieldsChanged(Character $character): bool
    {
        foreach (self::SYNERGY_FIELDS as $field) {
            if ($character->wasChanged($field)) {
                return true;
            }
        }

        return false;
    }
}
