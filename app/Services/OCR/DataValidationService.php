<?php

declare(strict_types=1);

namespace App\Services\OCR;

/**
 * Data Validation Service
 *
 * Comprehensive validation rules for all extracted OCR data types.
 * Validates character stats, training data, race results, and skills.
 *
 * Requirements: Task 5.1.4, Requirement 23.4
 */
class DataValidationService
{
    /**
     * Stat value range (0-1200)
     */
    private const STAT_MIN = 0;

    private const STAT_MAX = 1200;

    /**
     * Percentage range (0-100)
     */
    private const PERCENTAGE_MIN = 0;

    private const PERCENTAGE_MAX = 100;

    /**
     * Valid stat names
     */
    private const VALID_STATS = ['speed', 'stamina', 'power', 'guts', 'wit'];

    /**
     * Valid training types
     */
    private const VALID_TRAINING_TYPES = ['speed', 'stamina', 'power', 'guts', 'wit', 'rest', 'infirmary', 'outing'];

    /**
     * Valid mood statuses
     */
    private const VALID_MOODS = ['great', 'good', 'normal', 'bad', 'awful'];

    /**
     * Valid race grades
     */
    private const VALID_RACE_GRADES = ['G1', 'G2', 'G3', 'OP', 'Pre-OP'];

    /**
     * Valid surfaces
     */
    private const VALID_SURFACES = ['turf', 'dirt'];

    /**
     * Valid distance categories
     */
    private const VALID_DISTANCE_CATEGORIES = ['sprint', 'mile', 'medium', 'long'];

    /**
     * Validate extracted data based on screen type
     *
     * @param  array<string, mixed>  $data
     * @return array{valid: bool, errors: array<string>, warnings: array<string>}
     */
    public function validate(string $screenType, array $data): array
    {
        return match ($screenType) {
            'character_stats' => $this->validateCharacterStats($data),
            'training_session' => $this->validateTrainingSession($data),
            'race_result' => $this->validateRaceResult($data),
            'skill_list' => $this->validateSkillList($data),
            default => [
                'valid' => false,
                'errors' => ["Unknown screen type: {$screenType}"],
                'warnings' => [],
            ],
        };
    }

    /**
     * Validate character stats data
     *
     * @param  array<string, mixed>  $data
     * @return array{valid: bool, errors: array<string>, warnings: array<string>}
     */
    public function validateCharacterStats(array $data): array
    {
        $errors = [];
        $warnings = [];

        // Validate stats exist
        if (! isset($data['stats']) || ! \is_array($data['stats'])) {
            $errors[] = 'Stats data is missing or invalid';

            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Validate each stat
        $statsFound = 0;
        foreach (self::VALID_STATS as $statName) {
            if (isset($data['stats'][$statName])) {
                $value = $data['stats'][$statName];

                if ($value === null) {
                    $warnings[] = "Stat '{$statName}' is null";

                    continue;
                }

                if (! $this->validateStatValue($value)) {
                    $errors[] = "Invalid {$statName} value: {$value} (must be ".self::STAT_MIN.'-'.self::STAT_MAX.')';
                } else {
                    $statsFound++;

                    // Warning for unusually low stats
                    if ($value < 100) {
                        $warnings[] = "Unusually low {$statName} value: {$value}";
                    }

                    // Warning for stats near max
                    if ($value > 1100) {
                        $warnings[] = "Near-maximum {$statName} value: {$value}";
                    }
                }
            }
        }

        // Warning if less than 3 stats found
        if ($statsFound < 3) {
            $warnings[] = "Only {$statsFound} stats extracted (expected 5)";
        }

        // Validate energy level if present
        if (isset($data['energy_level'])) {
            if (! $this->validatePercentage($data['energy_level'])) {
                $errors[] = "Invalid energy level: {$data['energy_level']} (must be ".self::PERCENTAGE_MIN.'-'.self::PERCENTAGE_MAX.')';
            } elseif ($data['energy_level'] < 30) {
                $warnings[] = "Low energy level: {$data['energy_level']}%";
            }
        }

        // Validate mood status if present
        if (isset($data['mood_status'])) {
            if (! \in_array($data['mood_status'], self::VALID_MOODS, true)) {
                $errors[] = "Invalid mood status: {$data['mood_status']} (must be one of: ".\implode(', ', self::VALID_MOODS).')';
            }
        }

        // Validate turn numbers if present
        if (isset($data['current_turn'])) {
            if ($data['current_turn'] < 1 || $data['current_turn'] > 78) {
                $errors[] = "Invalid current turn: {$data['current_turn']} (must be 1-78)";
            }
        }

        if (isset($data['total_turns'])) {
            if ($data['total_turns'] < 1 || $data['total_turns'] > 78) {
                $errors[] = "Invalid total turns: {$data['total_turns']} (must be 1-78)";
            }
        }

        if (isset($data['current_turn'], $data['total_turns']) && $data['current_turn'] > $data['total_turns']) {
            $errors[] = "Current turn ({$data['current_turn']}) exceeds total turns ({$data['total_turns']})";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate training session data
     *
     * @param  array<string, mixed>  $data
     * @return array{valid: bool, errors: array<string>, warnings: array<string>}
     */
    public function validateTrainingSession(array $data): array
    {
        $errors = [];
        $warnings = [];

        // Validate training type
        if (isset($data['training_type'])) {
            if (! \in_array($data['training_type'], self::VALID_TRAINING_TYPES, true)) {
                $errors[] = "Invalid training type: {$data['training_type']} (must be one of: ".\implode(', ', self::VALID_TRAINING_TYPES).')';
            }
        } else {
            $warnings[] = 'Training type not extracted';
        }

        // Validate stat gains
        if (isset($data['stat_gains'])) {
            if (! \is_array($data['stat_gains'])) {
                $errors[] = 'Stat gains must be an array';
            } else {
                foreach ($data['stat_gains'] as $stat => $gain) {
                    if (! \in_array($stat, self::VALID_STATS, true)) {
                        $errors[] = "Invalid stat name in gains: {$stat}";
                    }

                    if ($gain < 0 || $gain > 200) {
                        $errors[] = "Invalid stat gain for {$stat}: {$gain} (must be 0-200)";
                    }

                    // Warning for unusually high gains
                    if ($gain > 100) {
                        $warnings[] = "Unusually high {$stat} gain: {$gain}";
                    }
                }

                if (empty($data['stat_gains'])) {
                    $warnings[] = 'No stat gains extracted';
                }
            }
        } else {
            $warnings[] = 'Stat gains not extracted';
        }

        // Validate energy cost
        if (isset($data['energy_cost'])) {
            if (! $this->validatePercentage($data['energy_cost'])) {
                $errors[] = "Invalid energy cost: {$data['energy_cost']} (must be ".self::PERCENTAGE_MIN.'-'.self::PERCENTAGE_MAX.')';
            }

            // Warning for high energy cost
            if ($data['energy_cost'] > 70) {
                $warnings[] = "High energy cost: {$data['energy_cost']}%";
            }
        }

        // Validate boolean flags
        foreach (['has_skill_hint', 'has_friendship', 'has_spirit_burst'] as $flag) {
            if (isset($data[$flag]) && ! \is_bool($data[$flag])) {
                $errors[] = "Field '{$flag}' must be boolean";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate race result data
     *
     * @param  array<string, mixed>  $data
     * @return array{valid: bool, errors: array<string>, warnings: array<string>}
     */
    public function validateRaceResult(array $data): array
    {
        $errors = [];
        $warnings = [];

        // Validate position
        if (isset($data['position'])) {
            if ($data['position'] < 1 || $data['position'] > 18) {
                $errors[] = "Invalid position: {$data['position']} (must be 1-18)";
            }
        } else {
            $warnings[] = 'Race position not extracted';
        }

        // Validate race name
        if (! isset($data['race_name']) || empty($data['race_name'])) {
            $warnings[] = 'Race name not extracted';
        }

        // Validate race grade
        if (isset($data['race_grade'])) {
            if (! \in_array($data['race_grade'], self::VALID_RACE_GRADES, true)) {
                $errors[] = "Invalid race grade: {$data['race_grade']} (must be one of: ".\implode(', ', self::VALID_RACE_GRADES).')';
            }
        }

        // Validate distance
        if (isset($data['distance'])) {
            if ($data['distance'] < 1000 || $data['distance'] > 3600) {
                $errors[] = "Invalid distance: {$data['distance']} (must be 1000-3600m)";
            }
        }

        // Validate distance category
        if (isset($data['distance_category'])) {
            if (! \in_array($data['distance_category'], self::VALID_DISTANCE_CATEGORIES, true)) {
                $errors[] = "Invalid distance category: {$data['distance_category']} (must be one of: ".\implode(', ', self::VALID_DISTANCE_CATEGORIES).')';
            }
        }

        // Validate surface
        if (isset($data['surface'])) {
            if (! \in_array($data['surface'], self::VALID_SURFACES, true)) {
                $errors[] = "Invalid surface: {$data['surface']} (must be one of: ".\implode(', ', self::VALID_SURFACES).')';
            }
        }

        // Validate fans gained
        if (isset($data['fans_gained'])) {
            if ($data['fans_gained'] < 0 || $data['fans_gained'] > 999999) {
                $errors[] = "Invalid fans gained: {$data['fans_gained']} (must be 0-999999)";
            }
        }

        // Validate skill points gained
        if (isset($data['skill_points_gained'])) {
            if ($data['skill_points_gained'] < 0 || $data['skill_points_gained'] > 9999) {
                $errors[] = "Invalid skill points gained: {$data['skill_points_gained']} (must be 0-9999)";
            }
        }

        // Validate outcome
        if (isset($data['outcome'])) {
            $validOutcomes = ['victory', 'podium', 'top_5', 'defeat'];
            if (! \in_array($data['outcome'], $validOutcomes, true)) {
                $errors[] = "Invalid outcome: {$data['outcome']} (must be one of: ".\implode(', ', $validOutcomes).')';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate skill list data
     *
     * @param  array<string, mixed>  $data
     * @return array{valid: bool, errors: array<string>, warnings: array<string>}
     */
    public function validateSkillList(array $data): array
    {
        $errors = [];
        $warnings = [];

        // Validate total SP
        if (isset($data['total_sp'])) {
            if ($data['total_sp'] < 0 || $data['total_sp'] > 99999) {
                $errors[] = "Invalid total SP: {$data['total_sp']} (must be 0-99999)";
            }
        }

        // Validate skills array
        if (isset($data['skills'])) {
            if (! \is_array($data['skills'])) {
                $errors[] = 'Skills must be an array';
            } else {
                foreach ($data['skills'] as $index => $skill) {
                    $skillErrors = $this->validateSkill($skill, $index);
                    $errors = [...$errors, ...$skillErrors];
                }

                if (empty($data['skills'])) {
                    $warnings[] = 'No skills extracted';
                }
            }
        } else {
            $warnings[] = 'Skills not extracted';
        }

        // Validate skill count
        if (isset($data['skill_count'])) {
            if ($data['skill_count'] < 0 || $data['skill_count'] > 100) {
                $errors[] = "Invalid skill count: {$data['skill_count']} (must be 0-100)";
            }

            if (isset($data['skills']) && $data['skill_count'] !== \count($data['skills'])) {
                $warnings[] = "Skill count mismatch: reported {$data['skill_count']}, found ".\count($data['skills']);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate individual skill data
     *
     * @param  array<string, mixed>  $skill
     * @return array<string>
     */
    private function validateSkill(array $skill, int $index): array
    {
        $errors = [];

        // Validate skill name
        if (! isset($skill['name']) || empty($skill['name'])) {
            $errors[] = "Skill #{$index}: Missing skill name";
        }

        // Validate SP cost
        if (isset($skill['sp_cost'])) {
            if ($skill['sp_cost'] < 0 || $skill['sp_cost'] > 500) {
                $errors[] = "Skill #{$index}: Invalid SP cost {$skill['sp_cost']} (must be 0-500)";
            }
        }

        // Validate hint level
        if (isset($skill['hint_level'])) {
            if ($skill['hint_level'] < 0 || $skill['hint_level'] > 5) {
                $errors[] = "Skill #{$index}: Invalid hint level {$skill['hint_level']} (must be 0-5)";
            }
        }

        // Validate is_acquired flag
        if (isset($skill['is_acquired']) && ! \is_bool($skill['is_acquired'])) {
            $errors[] = "Skill #{$index}: 'is_acquired' must be boolean";
        }

        return $errors;
    }

    /**
     * Validate stat value is within valid range (0-1200)
     */
    private function validateStatValue(int $value): bool
    {
        return $value >= self::STAT_MIN && $value <= self::STAT_MAX;
    }

    /**
     * Validate percentage value (0-100)
     */
    private function validatePercentage(int $value): bool
    {
        return $value >= self::PERCENTAGE_MIN && $value <= self::PERCENTAGE_MAX;
    }
}
