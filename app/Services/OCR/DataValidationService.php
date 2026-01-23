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
    public function validate(): array
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
    public function validateCharacterStats(): array
        $errors = [];
        $warnings = [];

        // Validate stats exist
        if (! isset((is_array($data) && isset($data['stats']) ? $data['stats'] : null)) || ! \is_array((is_array($data) && isset($data['stats']) ? $data['stats'] : null))) {
            $errors[] = 'Stats data is missing or invalid';

            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Validate each stat
        $statsFound = 0;
        foreach (self::VALID_STATS as $statName) {
            if (isset((is_array($data) && isset($data['stats']) ? $data['stats'] : null)[$statName])) {
                $value = (is_array($data) && isset($data['stats']) ? $data['stats'] : null)[$statName];

                if ($value === null) {
                    $warnings[] = "Stat '{$statName}' is null";

                    continue;
                }

                if (! $this->validateStatValue($value)) {
                    $errors[] = "Invalid {$statName} value: {$value} (must be ".self::STAT_MIN.'-'.self::STAT_MAX.')';
                } else {
                    $statsFound = ($statsFound ?? 0) + 1;

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
        if (isset((is_array($data) && isset($data['energy_level']) ? $data['energy_level'] : null))) {
            if (! $this->validatePercentage((is_array($data) && isset($data['energy_level']) ? $data['energy_level'] : null))) {
                $errors[] = "Invalid energy level: {(is_array($data) && isset($data['energy_level']) ? $data['energy_level'] : null)} (must be ".self::PERCENTAGE_MIN.'-'.self::PERCENTAGE_MAX.')';
            } elseif ((is_array($data) && isset($data['energy_level']) ? $data['energy_level'] : null) < 30) {
                $warnings[] = "Low energy level: {(is_array($data) && isset($data['energy_level']) ? $data['energy_level'] : null)}%";
            }
        }

        // Validate mood status if present
        if (isset((is_array($data) && isset($data['mood_status']) ? $data['mood_status'] : null))) {
            if (! \in_array((is_array($data) && isset($data['mood_status']) ? $data['mood_status'] : null), self::VALID_MOODS, true)) {
                $errors[] = "Invalid mood status: {(is_array($data) && isset($data['mood_status']) ? $data['mood_status'] : null)} (must be one of: ".\implode(', ', self::VALID_MOODS).')';
            }
        }

        // Validate turn numbers if present
        if (isset((is_array($data) && isset($data['current_turn']) ? $data['current_turn'] : null))) {
            if ((is_array($data) && isset($data['current_turn']) ? $data['current_turn'] : null) < 1 || (is_array($data) && isset($data['current_turn']) ? $data['current_turn'] : null) > 78) {
                $errors[] = "Invalid current turn: {(is_array($data) && isset($data['current_turn']) ? $data['current_turn'] : null)} (must be 1-78)";
            }
        }

        if (isset((is_array($data) && isset($data['total_turns']) ? $data['total_turns'] : null))) {
            if ((is_array($data) && isset($data['total_turns']) ? $data['total_turns'] : null) < 1 || (is_array($data) && isset($data['total_turns']) ? $data['total_turns'] : null) > 78) {
                $errors[] = "Invalid total turns: {(is_array($data) && isset($data['total_turns']) ? $data['total_turns'] : null)} (must be 1-78)";
            }
        }

        if (isset((is_array($data) && isset($data['current_turn']) ? $data['current_turn'] : null), (is_array($data) && isset($data['total_turns']) ? $data['total_turns'] : null)) && (is_array($data) && isset($data['current_turn']) ? $data['current_turn'] : null) > (is_array($data) && isset($data['total_turns']) ? $data['total_turns'] : null)) {
            $errors[] = "Current turn ({(is_array($data) && isset($data['current_turn']) ? $data['current_turn'] : null)}) exceeds total turns ({(is_array($data) && isset($data['total_turns']) ? $data['total_turns'] : null)})";
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
    public function validateTrainingSession(): array
        $errors = [];
        $warnings = [];

        // Validate training type
        if (isset((is_array($data) && isset($data['training_type']) ? $data['training_type'] : null))) {
            if (! \in_array((is_array($data) && isset($data['training_type']) ? $data['training_type'] : null), self::VALID_TRAINING_TYPES, true)) {
                $errors[] = "Invalid training type: {(is_array($data) && isset($data['training_type']) ? $data['training_type'] : null)} (must be one of: ".\implode(', ', self::VALID_TRAINING_TYPES).')';
            }
        } else {
            $warnings[] = 'Training type not extracted';
        }

        // Validate stat gains
        if (isset((is_array($data) && isset($data['stat_gains']) ? $data['stat_gains'] : null))) {
            if (! \is_array((is_array($data) && isset($data['stat_gains']) ? $data['stat_gains'] : null))) {
                $errors[] = 'Stat gains must be an array';
            } else {
                foreach ((is_array($data) && isset($data['stat_gains']) ? $data['stat_gains'] : null) as $stat => $gain) {
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

                if (empty((is_array($data) && isset($data['stat_gains']) ? $data['stat_gains'] : null))) {
                    $warnings[] = 'No stat gains extracted';
                }
            }
        } else {
            $warnings[] = 'Stat gains not extracted';
        }

        // Validate energy cost
        if (isset((is_array($data) && isset($data['energy_cost']) ? $data['energy_cost'] : null))) {
            if (! $this->validatePercentage((is_array($data) && isset($data['energy_cost']) ? $data['energy_cost'] : null))) {
                $errors[] = "Invalid energy cost: {(is_array($data) && isset($data['energy_cost']) ? $data['energy_cost'] : null)} (must be ".self::PERCENTAGE_MIN.'-'.self::PERCENTAGE_MAX.')';
            }

            // Warning for high energy cost
            if ((is_array($data) && isset($data['energy_cost']) ? $data['energy_cost'] : null) > 70) {
                $warnings[] = "High energy cost: {(is_array($data) && isset($data['energy_cost']) ? $data['energy_cost'] : null)}%";
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
    public function validateRaceResult(): array
        $errors = [];
        $warnings = [];

        // Validate position
        if (isset((is_array($data) && isset($data['position']) ? $data['position'] : null))) {
            if ((is_array($data) && isset($data['position']) ? $data['position'] : null) < 1 || (is_array($data) && isset($data['position']) ? $data['position'] : null) > 18) {
                $errors[] = "Invalid position: {(is_array($data) && isset($data['position']) ? $data['position'] : null)} (must be 1-18)";
            }
        } else {
            $warnings[] = 'Race position not extracted';
        }

        // Validate race name
        if (! isset((is_array($data) && isset($data['race_name']) ? $data['race_name'] : null)) || empty((is_array($data) && isset($data['race_name']) ? $data['race_name'] : null))) {
            $warnings[] = 'Race name not extracted';
        }

        // Validate race grade
        if (isset((is_array($data) && isset($data['race_grade']) ? $data['race_grade'] : null))) {
            if (! \in_array((is_array($data) && isset($data['race_grade']) ? $data['race_grade'] : null), self::VALID_RACE_GRADES, true)) {
                $errors[] = "Invalid race grade: {(is_array($data) && isset($data['race_grade']) ? $data['race_grade'] : null)} (must be one of: ".\implode(', ', self::VALID_RACE_GRADES).')';
            }
        }

        // Validate distance
        if (isset((is_array($data) && isset($data['distance']) ? $data['distance'] : null))) {
            if ((is_array($data) && isset($data['distance']) ? $data['distance'] : null) < 1000 || (is_array($data) && isset($data['distance']) ? $data['distance'] : null) > 3600) {
                $errors[] = "Invalid distance: {(is_array($data) && isset($data['distance']) ? $data['distance'] : null)} (must be 1000-3600m)";
            }
        }

        // Validate distance category
        if (isset((is_array($data) && isset($data['distance_category']) ? $data['distance_category'] : null))) {
            if (! \in_array((is_array($data) && isset($data['distance_category']) ? $data['distance_category'] : null), self::VALID_DISTANCE_CATEGORIES, true)) {
                $errors[] = "Invalid distance category: {(is_array($data) && isset($data['distance_category']) ? $data['distance_category'] : null)} (must be one of: ".\implode(', ', self::VALID_DISTANCE_CATEGORIES).')';
            }
        }

        // Validate surface
        if (isset((is_array($data) && isset($data['surface']) ? $data['surface'] : null))) {
            if (! \in_array((is_array($data) && isset($data['surface']) ? $data['surface'] : null), self::VALID_SURFACES, true)) {
                $errors[] = "Invalid surface: {(is_array($data) && isset($data['surface']) ? $data['surface'] : null)} (must be one of: ".\implode(', ', self::VALID_SURFACES).')';
            }
        }

        // Validate fans gained
        if (isset((is_array($data) && isset($data['fans_gained']) ? $data['fans_gained'] : null))) {
            if ((is_array($data) && isset($data['fans_gained']) ? $data['fans_gained'] : null) < 0 || (is_array($data) && isset($data['fans_gained']) ? $data['fans_gained'] : null) > 999999) {
                $errors[] = "Invalid fans gained: {(is_array($data) && isset($data['fans_gained']) ? $data['fans_gained'] : null)} (must be 0-999999)";
            }
        }

        // Validate skill points gained
        if (isset((is_array($data) && isset($data['skill_points_gained']) ? $data['skill_points_gained'] : null))) {
            if ((is_array($data) && isset($data['skill_points_gained']) ? $data['skill_points_gained'] : null) < 0 || (is_array($data) && isset($data['skill_points_gained']) ? $data['skill_points_gained'] : null) > 9999) {
                $errors[] = "Invalid skill points gained: {(is_array($data) && isset($data['skill_points_gained']) ? $data['skill_points_gained'] : null)} (must be 0-9999)";
            }
        }

        // Validate outcome
        if (isset((is_array($data) && isset($data['outcome']) ? $data['outcome'] : null))) {
            $validOutcomes = ['victory', 'podium', 'top_5', 'defeat'];
            if (! \in_array((is_array($data) && isset($data['outcome']) ? $data['outcome'] : null), $validOutcomes, true)) {
                $errors[] = "Invalid outcome: {(is_array($data) && isset($data['outcome']) ? $data['outcome'] : null)} (must be one of: ".\implode(', ', $validOutcomes).')';
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
    public function validateSkillList(): array
        $errors = [];
        $warnings = [];

        // Validate total SP
        if (isset((is_array($data) && isset($data['total_sp']) ? $data['total_sp'] : null))) {
            if ((is_array($data) && isset($data['total_sp']) ? $data['total_sp'] : null) < 0 || (is_array($data) && isset($data['total_sp']) ? $data['total_sp'] : null) > 99999) {
                $errors[] = "Invalid total SP: {(is_array($data) && isset($data['total_sp']) ? $data['total_sp'] : null)} (must be 0-99999)";
            }
        }

        // Validate skills array
        if (isset((is_array($data) && isset($data['skills']) ? $data['skills'] : null))) {
            if (! \is_array((is_array($data) && isset($data['skills']) ? $data['skills'] : null))) {
                $errors[] = 'Skills must be an array';
            } else {
                foreach ((is_array($data) && isset($data['skills']) ? $data['skills'] : null) as $index => $skill) {
                    $skillErrors = $this->validateSkill($skill, $index);
                    $errors = [...$errors, ...$skillErrors];
                }

                if (empty((is_array($data) && isset($data['skills']) ? $data['skills'] : null))) {
                    $warnings[] = 'No skills extracted';
                }
            }
        } else {
            $warnings[] = 'Skills not extracted';
        }

        // Validate skill count
        if (isset((is_array($data) && isset($data['skill_count']) ? $data['skill_count'] : null))) {
            if ((is_array($data) && isset($data['skill_count']) ? $data['skill_count'] : null) < 0 || (is_array($data) && isset($data['skill_count']) ? $data['skill_count'] : null) > 100) {
                $errors[] = "Invalid skill count: {(is_array($data) && isset($data['skill_count']) ? $data['skill_count'] : null)} (must be 0-100)";
            }

            if (isset((is_array($data) && isset($data['skills']) ? $data['skills'] : null)) && (is_array($data) && isset($data['skill_count']) ? $data['skill_count'] : null) !== \count((is_array($data) && isset($data['skills']) ? $data['skills'] : null))) {
                $warnings[] = "Skill count mismatch: reported {(is_array($data) && isset($data['skill_count']) ? $data['skill_count'] : null)}, found ".\count((is_array($data) && isset($data['skills']) ? $data['skills'] : null));
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
    private function validateSkill(): array
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
