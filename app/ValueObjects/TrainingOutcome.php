<?php

declare(strict_types=1);

namespace App\ValueObjects;

/**
 * Training Outcome Value Object
 *
 * Represents the actual outcome of a training turn.
 * Used to compare against predicted outcomes for accuracy tracking.
 *
 * @see \App\Services\PredictionAccuracyTracker
 */
final readonly class TrainingOutcome
{
    /**
     * Create new Training Outcome
     *
     * @param  int  $turnNumber  Turn number when training occurred
     * @param  string  $facility  Training facility used (speed, stamina, power, guts, wisdom)
     * @param  array<string, int>  $statGains  Actual stat gains (e.g., ['speed' => 45, 'power' => 12])
     * @param  array<int, int>  $bondIncreases  Bond increases per support card (card_id => increase)
     * @param  array<int, int>  $skillHints  Skill hints obtained (skill_id => hint_level)
     * @param  bool  $wasFailure  Whether the training failed
     * @param  bool  $wasInjury  Whether an injury occurred
     * @param  int  $energyChange  Energy change from training
     * @param  array<string, mixed>  $additionalData  Any additional outcome data
     */
    public function __construct(
        public int $turnNumber,
        public string $facility,
        public array $statGains,
        public array $bondIncreases = [],
        public array $skillHints = [],
        public bool $wasFailure = false,
        public bool $wasInjury = false,
        public int $energyChange = 0,
        public array $additionalData = [],
    ) {}

    /**
     * Check if training was successful
     */
    public function wasSuccessful(): bool
    {
        return ! $this->wasFailure && ! $this->wasInjury;
    }

    /**
     * Get total stat gain across all stats
     */
    public function getTotalStatGain(): int
    {
        return array_sum($this->statGains);
    }

    /**
     * Get stat gain for a specific stat
     */
    public function getStatGain(string $stat): int
    {
        return $this->statGains[$stat] ?? 0;
    }

    /**
     * Get total bond increase across all cards
     */
    public function getTotalBondIncrease(): int
    {
        return array_sum($this->bondIncreases);
    }

    /**
     * Get number of skill hints obtained
     */
    public function getSkillHintCount(): int
    {
        return count($this->skillHints);
    }

    /**
     * Convert outcome to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'turn_number' => $this->turnNumber,
            'facility' => $this->facility,
            'stat_gains' => $this->statGains,
            'bond_increases' => $this->bondIncreases,
            'skill_hints' => $this->skillHints,
            'was_failure' => $this->wasFailure,
            'was_injury' => $this->wasInjury,
            'energy_change' => $this->energyChange,
            'total_stat_gain' => $this->getTotalStatGain(),
            'total_bond_increase' => $this->getTotalBondIncrease(),
            'skill_hint_count' => $this->getSkillHintCount(),
            'was_successful' => $this->wasSuccessful(),
            'additional_data' => $this->additionalData,
        ];
    }

    /**
     * Create from an array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        // Validate and cast statGains to array<string, int>
        $statGains = [];
        if (isset($data['stat_gains']) && is_array($data['stat_gains'])) {
            foreach ($data['stat_gains'] as $stat => $gain) {
                $statStr = is_string($stat) ? $stat : (string) $stat;
                if (is_numeric($gain)) {
                    $statGains[$statStr] = (int) $gain;
                }
            }
        }

        // Validate and cast bondIncreases to array<int, int>
        $bondIncreases = [];
        if (isset($data['bond_increases']) && is_array($data['bond_increases'])) {
            foreach ($data['bond_increases'] as $cardId => $increase) {
                $cardIdInt = is_int($cardId) ? $cardId : (is_numeric($cardId) ? (int) $cardId : 0);
                if (is_numeric($increase)) {
                    $bondIncreases[$cardIdInt] = (int) $increase;
                }
            }
        }

        // Validate and cast skillHints to array<int, int>
        $skillHints = [];
        if (isset($data['skill_hints']) && is_array($data['skill_hints'])) {
            foreach ($data['skill_hints'] as $skillId => $level) {
                $skillIdInt = is_int($skillId) ? $skillId : (is_numeric($skillId) ? (int) $skillId : 0);
                if (is_numeric($level)) {
                    $skillHints[$skillIdInt] = (int) $level;
                }
            }
        }

        $turnNumber = $data['turn_number'] ?? 0;
        $facility = $data['facility'] ?? 'speed';
        $wasFailure = $data['was_failure'] ?? false;
        $wasInjury = $data['was_injury'] ?? false;
        $energyChange = $data['energy_change'] ?? 0;
        $additionalData = $data['additional_data'] ?? [];

        return new self(
            turnNumber: is_numeric($turnNumber) ? (int) $turnNumber : 0,
            facility: is_string($facility) ? $facility : 'speed',
            statGains: $statGains,
            bondIncreases: $bondIncreases,
            skillHints: $skillHints,
            wasFailure: is_bool($wasFailure) ? $wasFailure : false,
            wasInjury: is_bool($wasInjury) ? $wasInjury : false,
            energyChange: is_numeric($energyChange) ? (int) $energyChange : 0,
            additionalData: is_array($additionalData) ? $additionalData : [],
        );
    }
}
