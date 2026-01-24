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
    public function validate(array $data, string $screenType): array
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

                if (! is_int($value)) {
                    $errors[] = "Invalid {$statName} value type: expected integer";

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
            $energyLevel = $data['energy_level'];
            if (! is_int($energyLevel)) {
                $errors[] = 'Invalid energy level type: expected integer';
            } elseif (! $this->validatePercentage($energyLevel)) {
                $errors[] = "Invalid energy level: {$energyLevel} (must be ".self::PERCENTAGE_MIN.'-'.self::PERCENTAGE_MAX.')';
            } elseif ($energyLevel < 30) {
                $warnings[] = "Low energy level: {$energyLevel}%";
            }
        }

        // Validate mood status if present
        if (isset($data['mood_status'])) {
            $moodStatus = is_string($data['mood_status']) ? $data['mood_status'] : '';
            if (! \in_array($moodStatus, self::VALID_MOODS, true)) {
                $errors[] = "Invalid mood status: {$moodStatus} (must be one of: ".\implode(', ', self::VALID_MOODS).')';
            }
        }

        // Validate turn numbers if present
        if (isset($data['current_turn'])) {
            $currentTurn = is_int($data['current_turn']) ? $data['current_turn'] : 0;
            if ($currentTurn < 1 || $currentTurn > 78) {
                $errors[] = "Invalid current turn: {$currentTurn} (must be 1-78)";
            }
        }

        if (isset($data['total_turns'])) {
            $totalTurns = is_int($data['total_turns']) ? $data['total_turns'] : 0;
            if ($totalTurns < 1 || $totalTurns > 78) {
                $errors[] = "Invalid total turns: {$totalTurns} (must be 1-78)";
            }
        }

        if (isset($data['current_turn'], $data['total_turns'])) {
            $currentTurn = is_int($data['current_turn']) ? $data['current_turn'] : 0;
            $totalTurns = is_int($data['total_turns']) ? $data['total_turns'] : 0;
            if ($currentTurn > $totalTurns) {
                $errors[] = "Current turn ({$currentTurn}) exceeds total turns ({$totalTurns})";
            }
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
            $trainingType = is_string($data['training_type']) ? $data['training_type'] : '';
            if (! \in_array($trainingType, self::VALID_TRAINING_TYPES, true)) {
                $errors[] = "Invalid training type: {$trainingType} (must be one of: ".\implode(', ', self::VALID_TRAINING_TYPES).')';
            }
        } else {
            $warnings[] = 'Training type not extracted';
        }

        // Validate stat gains
        if (isset($data['stat_gains'])) {
            if (! \is_array($data['stat_gains'])) {
                $errors[] = 'Stat gains must be an array';
            } else {
                /** @var array<string, mixed> $statGains */
                $statGains = $data['stat_gains'];
                foreach ($statGains as $stat => $gain) {
                    $statName = is_string($stat) ? $stat : '';
                    $gainValue = is_int($gain) ? $gain : 0;

                    if (! \in_array($statName, self::VALID_STATS, true)) {
                        $errors[] = "Invalid stat name in gains: {$statName}";
                    }

                    if ($gainValue < 0 || $gainValue > 200) {
                        $errors[] = "Invalid stat gain for {$statName}: {$gainValue} (must be 0-200)";
                    }

                    // Warning for unusually high gains
                    if ($gainValue > 100) {
                        $warnings[] = "Unusually high {$statName} gain: {$gainValue}";
                    }
                }

                if (empty($statGains)) {
                    $warnings[] = 'No stat gains extracted';
                }
            }
        } else {
            $warnings[] = 'Stat gains not extracted';
        }

        // Validate energy cost
        if (isset($data['energy_cost'])) {
            $energyCost = is_int($data['energy_cost']) ? $data['energy_cost'] : 0;
            if (! $this->validatePercentage($energyCost)) {
                $errors[] = "Invalid energy cost: {$energyCost} (must be ".self::PERCENTAGE_MIN.'-'.self::PERCENTAGE_MAX.')';
            }

            // Warning for high energy cost
            if ($energyCost > 70) {
                $warnings[] = "High energy cost: {$energyCost}%";
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
            $position = is_int($data['position']) ? $data['position'] : 0;
            if ($position < 1 || $position > 18) {
                $errors[] = "Invalid position: {$position} (must be 1-18)";
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
            $raceGrade = is_string($data['race_grade']) ? $data['race_grade'] : '';
            if (! \in_array($raceGrade, self::VALID_RACE_GRADES, true)) {
                $errors[] = "Invalid race grade: {$raceGrade} (must be one of: ".\implode(', ', self::VALID_RACE_GRADES).')';
            }
        }

        // Validate distance
        if (isset($data['distance'])) {
            $distance = is_int($data['distance']) ? $data['distance'] : 0;
            if ($distance < 1000 || $distance > 3600) {
                $errors[] = "Invalid distance: {$distance} (must be 1000-3600m)";
            }
        }

        // Validate distance category
        if (isset($data['distance_category'])) {
            $distanceCategory = is_string($data['distance_category']) ? $data['distance_category'] : '';
            if (! \in_array($distanceCategory, self::VALID_DISTANCE_CATEGORIES, true)) {
                $errors[] = "Invalid distance category: {$distanceCategory} (must be one of: ".\implode(', ', self::VALID_DISTANCE_CATEGORIES).')';
            }
        }

        // Validate surface
        if (isset($data['surface'])) {
            $surface = is_string($data['surface']) ? $data['surface'] : '';
            if (! \in_array($surface, self::VALID_SURFACES, true)) {
                $errors[] = "Invalid surface: {$surface} (must be one of: ".\implode(', ', self::VALID_SURFACES).')';
            }
        }

        // Validate fans gained
        if (isset($data['fans_gained'])) {
            $fansGained = is_int($data['fans_gained']) ? $data['fans_gained'] : 0;
            if ($fansGained < 0 || $fansGained > 999999) {
                $errors[] = "Invalid fans gained: {$fansGained} (must be 0-999999)";
            }
        }

        // Validate skill points gained
        if (isset($data['skill_points_gained'])) {
            $skillPointsGained = is_int($data['skill_points_gained']) ? $data['skill_points_gained'] : 0;
            if ($skillPointsGained < 0 || $skillPointsGained > 9999) {
                $errors[] = "Invalid skill points gained: {$skillPointsGained} (must be 0-9999)";
            }
        }

        // Validate outcome
        if (isset($data['outcome'])) {
            $validOutcomes = ['victory', 'podium', 'top_5', 'defeat'];
            $outcome = is_string($data['outcome']) ? $data['outcome'] : '';
            if (! \in_array($outcome, $validOutcomes, true)) {
                $errors[] = "Invalid outcome: {$outcome} (must be one of: ".\implode(', ', $validOutcomes).')';
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
            $totalSp = is_int($data['total_sp']) ? $data['total_sp'] : 0;
            if ($totalSp < 0 || $totalSp > 99999) {
                $errors[] = "Invalid total SP: {$totalSp} (must be 0-99999)";
            }
        }

        // Validate skills array
        if (isset($data['skills'])) {
            if (! \is_array($data['skills'])) {
                $errors[] = 'Skills must be an array';
            } else {
                /** @var array<int, mixed> $skills */
                $skills = $data['skills'];
                foreach ($skills as $index => $skill) {
                    if (! is_array($skill)) {
                        $errors[] = "Skill #{$index}: Invalid skill data type";

                        continue;
                    }
                    /** @var array<string, mixed> $skillData */
                    $skillData = $skill;
                    $skillErrors = $this->validateSkill($skillData, (int) $index);
                    $errors = [...$errors, ...$skillErrors];
                }

                if (empty($skills)) {
                    $warnings[] = 'No skills extracted';
                }
            }
        } else {
            $warnings[] = 'Skills not extracted';
        }

        // Validate skill count
        if (isset($data['skill_count'])) {
            $skillCount = is_int($data['skill_count']) ? $data['skill_count'] : 0;
            if ($skillCount < 0 || $skillCount > 100) {
                $errors[] = "Invalid skill count: {$skillCount} (must be 0-100)";
            }

            if (isset($data['skills']) && is_array($data['skills']) && $skillCount !== \count($data['skills'])) {
                $warnings[] = "Skill count mismatch: reported {$skillCount}, found ".\count($data['skills']);
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
            $spCost = is_int($skill['sp_cost']) ? $skill['sp_cost'] : 0;
            if ($spCost < 0 || $spCost > 500) {
                $errors[] = "Skill #{$index}: Invalid SP cost {$spCost} (must be 0-500)";
            }
        }

        // Validate hint level
        if (isset($skill['hint_level'])) {
            $hintLevel = is_int($skill['hint_level']) ? $skill['hint_level'] : 0;
            if ($hintLevel < 0 || $hintLevel > 5) {
                $errors[] = "Skill #{$index}: Invalid hint level {$hintLevel} (must be 0-5)";
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
