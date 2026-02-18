<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Enums\CareerPhase;
use App\Enums\Mood;

/**
 * Training Context Value Object
 *
 * Encapsulates complete character state for AI-powered training analysis.
 * This immutable value object provides all necessary context for generating
 * training recommendations, skill advice, and race strategies.
 *
 * @see \App\Services\TrainingAdvisoryService
 * @see \App\Services\RuleBasedAdvisor
 */
final readonly class TrainingContext
{
    /**
     * Create a new Training Context
     *
     * @param  int  $turnNumber  Current turn number (1-72)
     * @param  CareerPhase  $phase  Current career phase
     * @param  CharacterStats  $stats  Current character stats
     * @param  int  $spAvailable  Available Skill Points for purchases
     * @param  int  $energy  Current energy level (0-100)
     * @param  Mood  $mood  Current mood state
     * @param  array<int>  $acquiredSkills  Array of acquired skill IDs
     * @param  array<array{skill_id: int, level: int}>  $skillHints  Array of skill hints with levels
     * @param  SupportCardDeck  $deck  Support card deck configuration
     * @param  array<string, int>  $facilityLevels  Facility levels (1-5) keyed by facility name
     * @param  array<array{id: int, distance: string, turn: int}>  $upcomingRaces  Array of upcoming race data
     * @param  string|null  $scenario  Active scenario (e.g., 'ura_finale', 'unity_cup')
     * @param  string  $storageMode  Storage mode ('local' or 'account')
     * @param  string|int  $careerRunId  Career run identifier (UUID for local, int for account)
     */
    public function __construct(
        public int $turnNumber,
        public CareerPhase $phase,
        public CharacterStats $stats,
        public int $spAvailable,
        public int $energy,
        public Mood $mood,
        public array $acquiredSkills,
        public array $skillHints,
        public SupportCardDeck $deck,
        public array $facilityLevels,
        public array $upcomingRaces,
        public ?string $scenario = null,
        public string $storageMode = 'account',
        public string|int $careerRunId = 0,
    ) {}

    /**
     * Convert context to a prompt string for AI inference
     *
     * Generates a structured text representation suitable for LLM prompts.
     */
    public function toPromptContext(): string
    {
        $prompt = "# Training Context\n\n";
        $prompt .= "## Career Progress\n";
        $prompt .= "- Turn: {$this->turnNumber}/72\n";
        $prompt .= "- Phase: {$this->phase->label()}\n";
        $prompt .= '- Scenario: '.($this->scenario ?? 'URA Finale')."\n\n";

        $prompt .= "## Character Stats\n";
        $prompt .= "- Speed: {$this->stats->speed}\n";
        $prompt .= "- Stamina: {$this->stats->stamina}\n";
        $prompt .= "- Power: {$this->stats->power}\n";
        $prompt .= "- Guts: {$this->stats->guts}\n";
        $prompt .= "- Wisdom: {$this->stats->wisdom}\n\n";

        $prompt .= "## Resources\n";
        $prompt .= "- SP Available: {$this->spAvailable}\n";
        $prompt .= "- Energy: {$this->energy}/100\n";
        $prompt .= "- Mood: {$this->mood->label()} ({$this->mood->effectivenessMultiplier()}x effectiveness)\n\n";

        $prompt .= "## Skills\n";
        $prompt .= '- Acquired: '.count($this->acquiredSkills)." skills\n";
        $prompt .= '- Hints Available: '.count($this->skillHints)." hints\n\n";

        $prompt .= "## Support Deck\n";
        $prompt .= '- Cards: '.count($this->deck->cards)."\n";
        $prompt .= '- Friendship Training Ready: '.($this->hasFriendshipTrainingAvailable() ? 'Yes' : 'No')."\n\n";

        $prompt .= "## Facility Levels\n";
        foreach ($this->facilityLevels as $facility => $level) {
            $prompt .= '- '.ucfirst($facility).": Level {$level}\n";
        }
        $prompt .= "\n";

        $prompt .= "## Upcoming Races\n";
        if (empty($this->upcomingRaces)) {
            $prompt .= "- No upcoming races\n";
        } else {
            foreach ($this->upcomingRaces as $race) {
                $prompt .= "- Turn {$race['turn']}: {$race['distance']} distance\n";
            }
        }

        return $prompt;
    }

    /**
     * Check if energy is low (below 50)
     *
     * Low energy increases training failure rates and should trigger
     * rest or Wisdom training recommendations.
     */
    public function isEnergyLow(): bool
    {
        return $this->energy < 50;
    }

    /**
     * Check if energy is critical (below 40)
     *
     * Critical energy requires immediate rest to avoid high failure rates.
     */
    public function isEnergyCritical(): bool
    {
        return $this->energy < 40;
    }

    /**
     * Check if mood is poor (Bad or Very Bad)
     *
     * Poor mood reduces training effectiveness and should trigger
     * recreation recommendations.
     */
    public function isMoodPoor(): bool
    {
        return $this->mood->isPoor();
    }

    /**
     * Check if Friendship Training is available
     *
     * Friendship Training is available when at least one support card
     * has bond level ≥80 (orange gauge).
     */
    public function hasFriendshipTrainingAvailable(): bool
    {
        return $this->deck->hasFriendshipTrainingReady();
    }

    /**
     * Get the number of support cards ready for Friendship Training
     */
    public function getFriendshipReadyCount(): int
    {
        return $this->deck->getFriendshipReadyCount();
    }

    /**
     * Check if this is a local storage mode context
     */
    public function isLocalMode(): bool
    {
        return $this->storageMode === 'local';
    }

    /**
     * Check if this is an account storage mode context
     */
    public function isAccountMode(): bool
    {
        return $this->storageMode === 'account';
    }

    /**
     * Get the phase-specific stat targets
     *
     * @return array<string, int>
     */
    public function getPhaseStatTargets(): array
    {
        return $this->phase->statTargets();
    }

    /**
     * Calculate stat progress toward phase targets
     *
     * @return array<string, float> Percentage progress (0.0-1.0+) for each stat
     */
    public function getStatProgress(): array
    {
        $targets = $this->getPhaseStatTargets();

        return [
            'speed' => $targets['speed'] > 0 ? $this->stats->speed / $targets['speed'] : 0,
            'stamina' => $targets['stamina'] > 0 ? $this->stats->stamina / $targets['stamina'] : 0,
            'power' => $targets['power'] > 0 ? $this->stats->power / $targets['power'] : 0,
            'guts' => $targets['guts'] > 0 ? $this->stats->guts / $targets['guts'] : 0,
            'wisdom' => $targets['wisdom'] > 0 ? $this->stats->wisdom / $targets['wisdom'] : 0,
        ];
    }

    /**
     * Get stats that are behind phase targets
     *
     * @return array<string> Array of stat names that are below target
     */
    public function getStatsBehindTarget(): array
    {
        $progress = $this->getStatProgress();

        return array_keys(array_filter($progress, fn ($p) => $p < 1.0));
    }

    /**
     * Check if any stats are significantly behind target (< 70%)
     */
    public function hasStatsCriticallyBehind(): bool
    {
        $progress = $this->getStatProgress();

        return count(array_filter($progress, fn ($p) => $p < 0.7)) > 0;
    }

    /**
     * Get the facility with the lowest level
     *
     * @return array{facility: string, level: int}
     */
    public function getLowestFacility(): array
    {
        $minLevel = empty($this->facilityLevels) ? 1 : min($this->facilityLevels);
        $facility = array_search($minLevel, $this->facilityLevels, true);

        return [
            'facility' => $facility !== false ? $facility : 'speed',
            'level' => $minLevel,
        ];
    }

    /**
     * Get the facility with the highest level
     *
     * @return array{facility: string, level: int}
     */
    public function getHighestFacility(): array
    {
        $maxLevel = empty($this->facilityLevels) ? 1 : max($this->facilityLevels);
        $facility = array_search($maxLevel, $this->facilityLevels, true);

        return [
            'facility' => $facility !== false ? $facility : 'speed',
            'level' => $maxLevel,
        ];
    }

    /**
     * Check if facility levels are imbalanced (difference > 2 levels)
     */
    public function hasFacilityImbalance(): bool
    {
        if (empty($this->facilityLevels)) {
            return false;
        }

        $min = min($this->facilityLevels);
        $max = max($this->facilityLevels);

        return ($max - $min) > 2;
    }

    /**
     * Get the next upcoming race
     *
     * @return array{id: int, distance: string, turn: int}|null
     */
    public function getNextRace(): ?array
    {
        if (empty($this->upcomingRaces)) {
            return null;
        }

        // Sort by turn and return the first one
        $races = $this->upcomingRaces;
        usort($races, fn ($a, $b) => $a['turn'] <=> $b['turn']);

        return $races[0];
    }

    /**
     * Get turns until next race
     */
    public function getTurnsUntilNextRace(): ?int
    {
        $nextRace = $this->getNextRace();

        return $nextRace ? $nextRace['turn'] - $this->turnNumber : null;
    }

    /**
     * Check if a race is approaching (within 5 turns)
     */
    public function isRaceApproaching(): bool
    {
        $turnsUntil = $this->getTurnsUntilNextRace();

        return $turnsUntil !== null && $turnsUntil <= 5;
    }

    /**
     * Get skill hint level for a specific skill
     */
    public function getSkillHintLevel(int $skillId): int
    {
        foreach ($this->skillHints as $hint) {
            if ($hint['skill_id'] === $skillId) {
                return $hint['level'];
            }
        }

        return 0;
    }

    /**
     * Check if a skill is already acquired
     */
    public function hasSkill(int $skillId): bool
    {
        return in_array($skillId, $this->acquiredSkills, true);
    }

    /**
     * Get the number of acquired skills
     */
    public function getAcquiredSkillCount(): int
    {
        return count($this->acquiredSkills);
    }

    /**
     * Create a context from an array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        // Validate and cast acquiredSkills to array<int>
        $acquiredSkills = [];
        if (isset($data['acquired_skills']) && is_array($data['acquired_skills'])) {
            foreach ($data['acquired_skills'] as $skillId) {
                if (is_int($skillId) || is_numeric($skillId)) {
                    $acquiredSkills[] = (int) $skillId;
                }
            }
        }

        // Validate and cast skillHints to array<array{skill_id: int, level: int}>
        $skillHints = [];
        if (isset($data['skill_hints']) && is_array($data['skill_hints'])) {
            foreach ($data['skill_hints'] as $hint) {
                if (is_array($hint)) {
                    $skillId = $hint['skill_id'] ?? 0;
                    $level = $hint['level'] ?? 0;
                    $skillHints[] = [
                        'skill_id' => is_numeric($skillId) ? (int) $skillId : 0,
                        'level' => is_numeric($level) ? (int) $level : 0,
                    ];
                }
            }
        }

        // Validate and cast facilityLevels to array<string, int>
        $facilityLevels = [
            'speed' => 1,
            'stamina' => 1,
            'power' => 1,
            'guts' => 1,
            'wisdom' => 1,
        ];
        if (isset($data['facility_levels']) && is_array($data['facility_levels'])) {
            foreach ($data['facility_levels'] as $facility => $level) {
                $facilityStr = is_string($facility) ? $facility : (string) $facility;
                if (is_numeric($level)) {
                    $facilityLevels[$facilityStr] = (int) $level;
                }
            }
        }

        // Validate and cast upcomingRaces to array<array{id: int, distance: string, turn: int}>
        $upcomingRaces = [];
        if (isset($data['upcoming_races']) && is_array($data['upcoming_races'])) {
            foreach ($data['upcoming_races'] as $race) {
                if (is_array($race)) {
                    $id = $race['id'] ?? 0;
                    $distance = $race['distance'] ?? 'short';
                    $turn = $race['turn'] ?? 0;
                    $upcomingRaces[] = [
                        'id' => is_numeric($id) ? (int) $id : 0,
                        'distance' => is_string($distance) ? $distance : 'short',
                        'turn' => is_numeric($turn) ? (int) $turn : 0,
                    ];
                }
            }
        }

        // Validate stats array
        $statsData = [];
        if (isset($data['stats']) && is_array($data['stats'])) {
            foreach ($data['stats'] as $key => $value) {
                $keyStr = is_string($key) ? $key : (string) $key;
                if (is_numeric($value)) {
                    $statsData[$keyStr] = (int) $value;
                }
            }
        }

        $turnNumber = $data['turn_number'] ?? 1;
        $phase = $data['phase'] ?? 'junior_year';
        $spAvailable = $data['sp_available'] ?? 0;
        $energy = $data['energy'] ?? 100;
        $mood = $data['mood'] ?? 'normal';
        $scenario = $data['scenario'] ?? null;
        $storageMode = $data['storage_mode'] ?? 'account';
        $careerRunId = $data['career_run_id'] ?? 0;

        return new self(
            turnNumber: is_numeric($turnNumber) ? (int) $turnNumber : 1,
            phase: CareerPhase::from(is_string($phase) ? $phase : 'junior_year'),
            stats: CharacterStats::fromArray($statsData),
            spAvailable: is_numeric($spAvailable) ? (int) $spAvailable : 0,
            energy: is_numeric($energy) ? (int) $energy : 100,
            mood: Mood::from(is_string($mood) ? $mood : 'normal'),
            acquiredSkills: $acquiredSkills,
            skillHints: $skillHints,
            deck: SupportCardDeck::fromArray(is_array($data['support_deck'] ?? null) ? $data['support_deck'] : []),
            facilityLevels: $facilityLevels,
            upcomingRaces: $upcomingRaces,
            scenario: is_string($scenario) ? $scenario : null,
            storageMode: is_string($storageMode) ? $storageMode : 'account',
            careerRunId: (is_int($careerRunId) || is_string($careerRunId)) ? $careerRunId : 0,
        );
    }

    /**
     * Convert context to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'turn_number' => $this->turnNumber,
            'phase' => $this->phase->value,
            'stats' => $this->stats->toArray(),
            'sp_available' => $this->spAvailable,
            'energy' => $this->energy,
            'mood' => $this->mood->value,
            'acquired_skills' => $this->acquiredSkills,
            'skill_hints' => $this->skillHints,
            'support_deck' => $this->deck->toArray(),
            'facility_levels' => $this->facilityLevels,
            'upcoming_races' => $this->upcomingRaces,
            'scenario' => $this->scenario,
            'storage_mode' => $this->storageMode,
            'career_run_id' => $this->careerRunId,
        ];
    }
}
