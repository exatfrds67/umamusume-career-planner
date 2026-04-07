<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with real-time character data.
     */
    public function index(Request $request): View
    {
        // Get all characters (for now without auth, later filter by user_id)
        $characters = Character::query()
            ->with(['aptitudes', 'skillAcquisitions.skill', 'supportCards', 'currentCareer.runSnapshots', 'gameCharacter.goalRaces'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Get selected character from request parameter or default to first active character
        $selectedCharacterId = $request->query('character');
        $selectedCharacter = null;

        if ($selectedCharacterId) {
            $selectedCharacter = $characters->firstWhere('id', (int) $selectedCharacterId);
        }

        if (! $selectedCharacter) {
            $selectedCharacter = $characters->firstWhere('status', 'active') ?? $characters->first();
        }

        // Prepare dashboard data
        $dashboardData = $this->prepareDashboardData($selectedCharacter);

        return view('dashboard', [
            'characters' => $characters,
            'selectedCharacter' => $selectedCharacter,
            'hasCharacters' => $characters->isNotEmpty(),
            'selectedCharacterId' => $selectedCharacter?->id,
            ...$dashboardData,
        ]);
    }

    /**
     * Prepare all dashboard data from the selected character.
     *
     * @return array<string, mixed>
     */
    private function prepareDashboardData(?Character $character): array
    {
        if (! $character) {
            return $this->getEmptyDashboardData();
        }

        $races = $this->getUpcomingRaces($character);
        $statProgression = $this->getStatProgression($character);
        $nextRace = $races[0] ?? null;
        $nextRaceRequirements = is_array($nextRace) && isset($nextRace['requirements']) && is_array($nextRace['requirements'])
            ? $nextRace['requirements']
            : [];
        $nextRaceName = is_array($nextRace)
            ? ($nextRace['name'] ?? null)
            : null;

        return [
            'metrics' => $this->getMetrics($character),
            'stats' => $this->getStats($character),
            'goals' => $this->getGoals($character),
            'races' => $races,
            'nextRaceRequirements' => $nextRaceRequirements,
            'nextRaceName' => is_string($nextRaceName) ? $nextRaceName : null,
            'trainingSuggestions' => $this->getTrainingSuggestions($character),
            'recentResults' => $this->getRecentResults($character),
            'moodEnergy' => $this->getMoodEnergy($character),
            'statProgression' => $statProgression['data'],
            'progressionLabels' => $statProgression['labels'],
            'raceGrades' => $this->getRaceGrades($character),
            'recentActivity' => $this->getRecentActivity($character),
        ];
    }

    /**
     * Get empty dashboard data for when no character exists.
     *
     * @return array<string, mixed>
     */
    private function getEmptyDashboardData(): array
    {
        return [
            'metrics' => [
                'currentTurn' => 0,
                'maxTurns' => 72,
                'overallGrade' => '-',
                'targetGrade' => '-',
                'skillsAcquired' => 0,
                'targetSkills' => 0,
                'skillPoints' => 0,
                'nextRace' => null,
                'turnsUntilRace' => null,
                'trackStatus' => 'No Character',
            ],
            'stats' => [
                'speed' => 0,
                'stamina' => 0,
                'power' => 0,
                'guts' => 0,
                'wit' => 0,
            ],
            'goals' => [
                'shortTerm' => ['goal' => 'Create a character to get started', 'progress' => 0],
                'longTerm' => ['goal' => 'No goals set', 'progress' => 0],
            ],
            'races' => [],
            'trainingSuggestions' => [],
            'recentResults' => [],
            'moodEnergy' => [
                'mood' => 'normal',
                'energy' => 0,
                'maxEnergy' => 100,
            ],
        ];
    }

    /**
     * Get key metrics from character.
     *
     * @return array<string, mixed>
     */
    private function getMetrics(Character $character): array
    {
        $rawStats = $character->current_stats;
        $stats = is_array($rawStats) ? $rawStats : [];

        $rawGoals = $character->goals;
        $goals = is_array($rawGoals) ? $rawGoals : [];

        // Calculate overall grade based on average stats
        $statValues = array_values($stats);
        $avgStat = count($statValues) > 0 ? array_sum($statValues) / count($statValues) : 0;
        $overallGrade = $character->getStatGrade((int) $avgStat);

        // Get target grade from goals
        $targetGrade = $goals['target_grade'] ?? 'A+';

        // Get skills count — try skill acquisitions first, then fall back to career metadata
        $skillsAcquired = $character->skillAcquisitions()->count();
        $targetSkillsValue = $goals['target_skills'] ?? null;

        if ($skillsAcquired === 0) {
            $career = $character->currentCareer;
            if ($career) {
                $careerMeta = $this->normalizeCareerMetadata($career->career_metadata);
                $metaSkills = $this->extractMetadataSkills($careerMeta);
                if ($metaSkills !== []) {
                    $skillsAcquired = count(array_filter($metaSkills, fn (array $skillEntry): bool => ($skillEntry['acquired'] ?? false) === true));
                    if ($targetSkillsValue === null) {
                        $targetSkillsValue = count($metaSkills);
                    }
                }
            }
        }

        $targetSkills = is_numeric($targetSkillsValue) ? (int) $targetSkillsValue : 12;

        // SP Left = remaining SP available on the character
        $skillPoints = $character->available_sp ?? 0;

        // Get next race info - handle potential string/malformed data gracefully
        $rawRaceSchedule = $character->race_schedule;
        $raceSchedule = is_array($rawRaceSchedule) ? $rawRaceSchedule : [];

        $nextRace = null;
        $turnsUntilRace = null;

        if (! empty($raceSchedule)) {
            /** @var array<int, array<string, mixed>> $upcomingRaces */
            $upcomingRaces = array_filter($raceSchedule, function ($race) use ($character) {
                return is_array($race) && isset($race['turn']) && is_numeric($race['turn']) && $race['turn'] > $character->current_turn;
            });
            if (! empty($upcomingRaces)) {
                $nextRaceData = reset($upcomingRaces);
                if (is_array($nextRaceData)) {
                    $nextRace = $nextRaceData['name'] ?? 'Unknown Race';
                    $raceTurn = isset($nextRaceData['turn']) && is_numeric($nextRaceData['turn'])
                        ? (int) $nextRaceData['turn']
                        : $character->current_turn;
                    $turnsUntilRace = $raceTurn - $character->current_turn;
                }
            }
        }

        // Determine track status
        $progress = $character->getProgressPercentage();
        $expectedProgress = ($character->current_turn / $character->getMaxTurns()) * 100;
        $trackStatus = match (true) {
            $progress >= $expectedProgress + 10 => 'Ahead',
            $progress >= $expectedProgress - 10 => 'On Track',
            default => 'Behind',
        };

        return [
            'currentTurn' => $character->current_turn ?? 1,
            'maxTurns' => $character->getMaxTurns(),
            'overallGrade' => $overallGrade,
            'targetGrade' => $targetGrade,
            'skillsAcquired' => $skillsAcquired,
            'targetSkills' => $targetSkills,
            'skillPoints' => $skillPoints,
            'nextRace' => $nextRace,
            'turnsUntilRace' => $turnsUntilRace,
            'trackStatus' => $trackStatus,
        ];
    }

    /**
     * Get character stats.
     *
     * @return array<string, int>
     */
    private function getStats(Character $character): array
    {
        $rawStats = $character->current_stats;
        $stats = is_array($rawStats) ? $rawStats : [];

        return [
            'speed' => (int) ($stats['speed'] ?? 0),
            'stamina' => (int) ($stats['stamina'] ?? 0),
            'power' => (int) ($stats['power'] ?? 0),
            'guts' => (int) ($stats['guts'] ?? 0),
            'wit' => (int) ($stats['wit'] ?? $stats['wisdom'] ?? 0),
        ];
    }

    /**
     * Get goals data.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getGoals(Character $character): array
    {
        $rawGoals = $character->goals;
        $goals = is_array($rawGoals) ? $rawGoals : [];

        $shortTermGoal = $goals['short_term'] ?? 'No short-term goal set';
        $longTermGoal = $goals['long_term'] ?? 'No long-term goal set';

        // Calculate progress based on target stats
        $shortTermProgress = $this->calculateGoalProgress($character, 'short_term');
        $longTermProgress = $character->getProgressPercentage();

        return [
            'shortTerm' => [
                'goal' => $shortTermGoal,
                'progress' => (int) $shortTermProgress,
            ],
            'longTerm' => [
                'goal' => $longTermGoal,
                'progress' => (int) $longTermProgress,
            ],
        ];
    }

    /**
     * Calculate goal progress.
     */
    private function calculateGoalProgress(Character $character, string $goalType): float
    {
        $rawGoals = $character->goals;
        $goals = is_array($rawGoals) ? $rawGoals : [];

        if ($goalType === 'short_term') {
            // Short term could be race-based or stat-based
            if (isset($goals['short_term_target']) && is_numeric($goals['short_term_target'])) {
                $target = (float) $goals['short_term_target'];
                $current = isset($goals['short_term_current']) && is_numeric($goals['short_term_current'])
                    ? (float) $goals['short_term_current']
                    : 0;

                return $target > 0 ? min(100, ($current / $target) * 100) : 0;
            }

            // Default: base on turn progress
            return min(100, ($character->current_turn / $character->getMaxTurns()) * 100);
        }

        return $character->getProgressPercentage();
    }

    /**
     * Get upcoming races.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getUpcomingRaces(Character $character): array
    {
        $rawRaceSchedule = $character->race_schedule;
        $raceSchedule = is_array($rawRaceSchedule) ? $rawRaceSchedule : [];

        $currentTurn = $character->current_turn ?? 1;
        $rawStats = $character->current_stats;
        $stats = is_array($rawStats) ? $rawStats : [];

        $upcomingRaces = [];

        // If no schedule is set, pre-fill from goal races
        if (empty($raceSchedule)) {
            // Try to get goal races from gameCharacter relationship
            $gameCharacter = $character->gameCharacter;
            if ($gameCharacter) {
                $goalRaces = $gameCharacter->goalRaces()
                    ->limit(5)
                    ->get();

                foreach ($goalRaces as $gameRace) {
                    $upcomingRaces[] = [
                        'name' => $gameRace->name_en,
                        'grade' => $gameRace->grade,
                        'date' => now()->addDays(14)->format('Y-m-d'),
                        'turn' => $currentTurn + 14,
                        'turnsAway' => 14,
                        'readiness' => $this->calculateRaceReadiness($stats, []),
                        'requirements' => [],
                        'isGoalRace' => true,
                    ];
                }
            }

            // Fallback: derive upcoming race from career_metadata when no goal races
            if (empty($upcomingRaces)) {
                $career = $character->currentCareer;
                $meta = $this->normalizeCareerMetadata($career?->career_metadata);
                $raceNameValue = $meta['race_name'] ?? null;
                $raceName = is_string($raceNameValue) ? $raceNameValue : null;

                if ($raceName && $career && ($career->status ?? '') !== 'completed') {
                    $isRaceDay = ($meta['race_day'] ?? false) === true;
                    $rawTurns = $meta['turn_before_race'] ?? null;
                    $turnsBefore = is_numeric($rawTurns) ? (int) $rawTurns : null;
                    $turnsAway = $isRaceDay ? 0 : $turnsBefore;

                    return [[
                        'name' => $raceName,
                        'grade' => $this->inferRaceGrade($raceName),
                        'date' => now()->addDays($turnsAway ?? 14)->format('Y-m-d'),
                        'turn' => $currentTurn + ($turnsAway ?? 14),
                        'turnsAway' => $turnsAway,
                        'readiness' => $this->calculateRaceReadiness($stats, []),
                        'requirements' => [],
                    ]];
                }
            }

            return array_slice($upcomingRaces, 0, 3);
        }

        foreach ($raceSchedule as $race) {
            if (! is_array($race)) {
                continue;
            }

            $raceTurn = isset($race['turn']) && is_numeric($race['turn']) ? (int) $race['turn'] : 0;
            if ($raceTurn > $currentTurn) {
                // Calculate readiness based on stats vs race requirements
                $readiness = $this->calculateRaceReadiness($stats, $race);

                // Check if this race is a goal race
                $isGoalRace = false;
                $raceName = isset($race['name']) && is_string($race['name']) ? $race['name'] : null;
                if ($raceName && $character->gameCharacter) {
                    $isGoalRace = $character->gameCharacter->goalRaces()
                        ->where('game_races.name', $raceName)
                        ->wherePivot('race_type', 'goal')
                        ->exists();
                }

                $upcomingRaces[] = [
                    'name' => $raceName ?? 'Unknown Race',
                    'grade' => isset($race['grade']) && is_string($race['grade']) ? $race['grade'] : 'G3',
                    'date' => isset($race['date']) && is_string($race['date'])
                        ? $race['date']
                        : now()->addDays($raceTurn - $currentTurn)->format('Y-m-d'),
                    'turn' => $raceTurn,
                    'turnsAway' => $raceTurn - $currentTurn,
                    'readiness' => $readiness,
                    'requirements' => isset($race['requirements']) && is_array($race['requirements']) ? $race['requirements'] : [],
                    'isGoalRace' => $isGoalRace,
                ];
            }
        }

        // Sort by turn and limit to 3
        usort($upcomingRaces, fn ($a, $b) => $a['turn'] <=> $b['turn']);

        return array_slice($upcomingRaces, 0, 3);
    }

    /**
     * Calculate race readiness percentage.
     *
     * @param  array<string, mixed>  $stats
     * @param  array<string, mixed>  $race
     */
    private function calculateRaceReadiness(array $stats, array $race): int
    {
        $requirements = isset($race['requirements']) && is_array($race['requirements'])
            ? $race['requirements']
            : [];

        if (empty($requirements)) {
            // Default calculation based on average stats
            $numericStats = array_filter($stats, fn ($v) => is_numeric($v));
            $avgStat = count($numericStats) > 0
                ? array_sum($numericStats) / count($numericStats)
                : 0;

            return min(100, (int) ($avgStat / 10));
        }

        $totalReadiness = 0;
        $count = 0;

        foreach ($requirements as $stat => $required) {
            if (! is_string($stat) || ! is_numeric($required)) {
                continue;
            }

            $current = isset($stats[$stat]) && is_numeric($stats[$stat]) ? (float) $stats[$stat] : 0;
            $requiredValue = (float) $required;
            $readiness = $requiredValue > 0 ? min(100, ($current / $requiredValue) * 100) : 100;
            $totalReadiness += $readiness;
            $count += 1;
        }

        return $count > 0 ? (int) ($totalReadiness / $count) : 50;
    }

    /**
     * Get training suggestions based on character state.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getTrainingSuggestions(Character $character): array
    {
        $rawStats = $character->current_stats;
        $stats = is_array($rawStats) ? $rawStats : [];
        $rawPriorities = $character->stat_priorities;
        $priorities = is_array($rawPriorities) ? $rawPriorities : [];
        $energy = $character->energy_level ?? 100;
        $mood = $character->mood_status ?? 'normal';

        $suggestions = [];

        // Determine which stats need the most work
        $statDeficits = $this->calculateStatDeficits($character);

        // Sort by deficit (highest first)
        arsort($statDeficits);

        $trainingTypes = [
            'speed' => ['name' => 'Speed Training', 'primary' => 'speed', 'secondary' => 'power'],
            'stamina' => ['name' => 'Stamina Training', 'primary' => 'stamina', 'secondary' => 'guts'],
            'power' => ['name' => 'Power Training', 'primary' => 'power', 'secondary' => 'stamina'],
            'guts' => ['name' => 'Guts Training', 'primary' => 'guts', 'secondary' => 'speed'],
            'wit' => ['name' => 'Wisdom Training', 'primary' => 'wit', 'secondary' => 'speed'],
        ];

        $index = 0;
        foreach ($statDeficits as $stat => $deficit) {
            if ($index >= 2) {
                break;
            }

            $training = $trainingTypes[$stat] ?? null;
            if (! $training) {
                continue;
            }

            $primaryGain = $this->calculateTrainingGain($character, $training['primary']);
            $secondaryGain = $this->calculateTrainingGain($character, $training['secondary'], 0.3);

            $risk = $this->calculateTrainingRisk($energy, $mood);

            $suggestions[] = [
                'action' => $training['name'],
                'gains' => sprintf(
                    '+%d %s, +%d %s',
                    $primaryGain,
                    ucfirst($training['primary']),
                    $secondaryGain,
                    ucfirst($training['secondary'])
                ),
                'risk' => $risk,
                'recommended' => $index === 0,
            ];

            $index += 1;
        }

        // Always suggest rest if energy is low
        if ($energy < 50) {
            array_unshift($suggestions, [
                'action' => 'Rest',
                'gains' => '+20-30 Energy',
                'risk' => 'none',
                'recommended' => $energy < 30,
            ]);
        } else {
            $suggestions[] = [
                'action' => 'Rest',
                'gains' => '+20-30 Energy',
                'risk' => 'none',
                'recommended' => false,
            ];
        }

        return array_slice($suggestions, 0, 3);
    }

    /**
     * Calculate stat deficits compared to targets.
     *
     * @return array<string, int>
     */
    private function calculateStatDeficits(Character $character): array
    {
        $rawStats = $character->current_stats;
        $stats = is_array($rawStats) ? $rawStats : [];

        $rawGoals = $character->goals;
        $goals = is_array($rawGoals) ? $rawGoals : [];

        $targets = $goals['target_stats'] ?? [
            'speed' => 1000,
            'stamina' => 800,
            'power' => 800,
            'guts' => 600,
            'wit' => 600,
        ];

        $deficits = [];
        /** @var array<string, int|float> $targets */
        foreach ($targets as $stat => $target) {
            $current = (int) ($stats[$stat] ?? 0);
            $targetValue = (int) $target;
            $deficits[$stat] = max(0, $targetValue - $current);
        }

        return $deficits;
    }

    /**
     * Calculate expected training gain.
     */
    private function calculateTrainingGain(Character $character, string $stat, float $multiplier = 1.0): int
    {
        $growthRates = $character->growth_rates ?? [];

        $baseGain = 12;
        $growthValue = $growthRates[$stat] ?? 0;
        $growthBonus = is_numeric($growthValue) ? (float) $growthValue / 100 : 0.0;

        return (int) (($baseGain + ($baseGain * $growthBonus)) * $multiplier);
    }

    /**
     * Calculate training risk level.
     */
    private function calculateTrainingRisk(int $energy, string $mood): string
    {
        if ($energy < 30 || $mood === 'bad') {
            return 'high';
        }

        if ($energy < 50 || $mood === 'normal') {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get recent results/activity.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRecentResults(Character $character): array
    {
        $results = [];

        $recentAcquisitions = $character->skillAcquisitions()
            ->with('skill')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentAcquisitions as $acquisition) {
            $skillName = $acquisition->skill->name ?? 'Unknown Skill';
            $results[] = [
                'type' => 'skill',
                'description' => 'Skill acquired:',
                'highlight' => $skillName,
                'timestamp' => $acquisition->created_at,
                'icon' => 'star',
                'color' => 'secondary',
            ];
        }

        $recentTraining = TrainingSession::query()
            ->where('character_id', '=', $character->id, 'and')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get(['*']);

        foreach ($recentTraining as $session) {
            $totalGain = ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);
            $results[] = [
                'type' => 'training',
                'description' => "Training: {$session->training_type} (+{$totalGain} stats)",
                'highlight' => $session->training_type ?? 'Training',
                'timestamp' => $session->created_at,
                'icon' => 'chart-bar',
                'color' => 'primary',
            ];
        }

        $recentRaces = Race::query()
            ->where('character_id', '=', $character->id, 'and')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get(['*']);

        foreach ($recentRaces as $race) {
            $position = $race->finish_position;
            $positionText = $position ? "#{$position}" : 'N/A';
            $results[] = [
                'type' => 'race',
                'description' => "Race: {$race->race_name} ({$positionText})",
                'highlight' => $race->race_name ?? 'Race',
                'timestamp' => $race->created_at,
                'icon' => $race->won_race ? 'trophy' : 'flag',
                'color' => $race->won_race ? 'warning' : 'info',
            ];
        }

        // Fallback: when no DB records exist, derive entries from career_metadata acquired skills
        if (empty($results)) {
            $career = $character->currentCareer;
            $meta = $this->normalizeCareerMetadata($career?->career_metadata);
            $skills = $this->extractMetadataSkills($meta);
            $acquiredSkills = array_values(
                array_filter($skills, fn (array $skillEntry): bool => ($skillEntry['acquired'] ?? false) === true)
            );
            $createdAt = $career?->created_at;
            $baseTime = Carbon::instance($createdAt ?? now());

            foreach (array_reverse($acquiredSkills) as $index => $skill) {
                if ($index >= 3) {
                    break;
                }
                $skillNameValue = $skill['name'] ?? null;
                $skillName = is_string($skillNameValue) ? $skillNameValue : 'Unknown Skill';
                $results[] = [
                    'type' => 'skill',
                    'description' => 'Skill acquired:',
                    'highlight' => $skillName,
                    'timestamp' => $baseTime->copy()->addMinutes($index * 5),
                    'icon' => 'star',
                    'color' => 'secondary',
                ];
            }
        }

        usort($results, function (array $a, array $b) {
            $timeA = $a['timestamp'] ?? null;
            $timeB = $b['timestamp'] ?? null;
            if ($timeA === null && $timeB === null) {
                return 0;
            }
            if ($timeA === null) {
                return 1;
            }
            if ($timeB === null) {
                return -1;
            }

            return $timeB <=> $timeA;
        });

        return array_slice($results, 0, 5);
    }

    /**
     * Get mood and energy data.
     *
     * @return array<string, mixed>
     */
    private function getMoodEnergy(Character $character): array
    {
        return [
            'mood' => $character->mood_status ?? 'normal',
            'energy' => $character->energy_level ?? 100,
            'maxEnergy' => 100,
        ];
    }

    /**
     * Infer a G1/G2/G3 race grade label from a race name.
     */
    private function inferRaceGrade(string $raceName): string
    {
        $upper = strtoupper($raceName);

        if (
            str_contains($upper, 'FINALE')
            || str_contains($upper, 'TENNO SHO')
            || str_contains($upper, 'JBC')
            || str_contains($upper, 'JAPAN CUP')
            || str_contains($upper, 'TAKARAZUKA')
            || str_contains($upper, 'ARIMA')
        ) {
            return 'G1';
        }

        if (str_contains($upper, 'QUALIFIER') || str_contains($upper, 'SEMI') || str_contains($upper, 'MEMORIAL')) {
            return 'G2';
        }

        return 'G3';
    }

    /**
     * Build stat progression data for the line chart, interpolated from snapshot to current.
     *
     * @return array<string, mixed>
     */
    private function getStatProgression(Character $character): array
    {
        $career = $character->currentCareer;

        $currentStats = is_array($character->current_stats) ? $character->current_stats : [];
        $currentTotal = (int) array_sum(array_filter(array_values($currentStats), 'is_numeric'));

        $snapshotTurn = max(1, (int) ($character->current_turn ?? 1));

        if ($career && $career->runSnapshots->isNotEmpty()) {
            $latest = $career->runSnapshots->sortByDesc('turn_number')->first();
            if ($latest) {
                $snapshotTurn = (int) $latest->turn_number;
            }
        }

        if ($currentTotal === 0) {
            return ['data' => [[]], 'labels' => []];
        }

        $numPoints = 6;
        $lastPointIndex = 5;
        // Uma Musume characters start with roughly 20-25% of their final stat total
        $startingTotal = max(300, (int) ($currentTotal * 0.25));
        $maxTurn = max($snapshotTurn, 1);

        $dataPoints = [];
        $labels = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $fraction = $i / $lastPointIndex;
            $turn = max(1, (int) round(1 + $fraction * ($maxTurn - 1)));
            $statTotal = (int) ($startingTotal + ($currentTotal - $startingTotal) * $fraction);
            $dataPoints[] = $statTotal;
            $labels[] = 'Turn '.$turn;
        }

        return [
            'data' => [$dataPoints],
            'labels' => $labels,
        ];
    }

    /**
     * Build race grade fan-distribution based on the career class rank.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRaceGrades(Character $character): array
    {
        $career = $character->currentCareer;
        $meta = $this->normalizeCareerMetadata($career?->career_metadata);
        $classRankValue = $meta['class_rank'] ?? null;
        $classRank = is_string($classRankValue) ? strtolower($classRankValue) : 'silver';

        $colorMap = [
            'G1' => 'bg-red-500',
            'G2' => 'bg-orange-500',
            'G3' => 'bg-yellow-500',
            'Listed' => 'bg-green-500',
            'Open' => 'bg-blue-500',
        ];

        /** @var array<string, array<string, int>> $distributions */
        $distributions = [
            'platinum' => ['G1' => 12000, 'G2' => 8500, 'G3' => 5000, 'Listed' => 3000, 'Open' => 1500],
            'star' => ['G1' => 9000, 'G2' => 6500, 'G3' => 4000, 'Listed' => 2500, 'Open' => 1200],
            'gold' => ['G1' => 5000, 'G2' => 4000, 'G3' => 2500, 'Listed' => 1500, 'Open' => 800],
            'silver' => ['G1' => 0, 'G2' => 2500, 'G3' => 3000, 'Listed' => 2000, 'Open' => 1000],
            'beginner' => ['G1' => 0, 'G2' => 0, 'G3' => 1500, 'Listed' => 2500, 'Open' => 3000],
        ];

        $distribution = $distributions[$classRank] ?? $distributions['silver'];

        $grades = [];
        foreach ($distribution as $grade => $fans) {
            if ($fans > 0) {
                $grades[] = [
                    'grade' => $grade,
                    'fans' => $fans,
                    'color' => $colorMap[$grade] ?? 'bg-gray-500',
                ];
            }
        }

        return $grades;
    }

    /**
     * Build recent activity timeline events from career metadata.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRecentActivity(Character $character): array
    {
        $events = [];
        $career = $character->currentCareer;

        if (! $career) {
            return $events;
        }

        $meta = $this->normalizeCareerMetadata($career->career_metadata);
        $baseTime = Carbon::instance($career->created_at ?? now());
        $raceNameValue = $meta['race_name'] ?? null;
        $raceName = is_string($raceNameValue) ? $raceNameValue : null;
        $strategy = $meta['strategy'] ?? null;

        // Race-day event is the most recent item
        if (($meta['race_day'] ?? false) === true && $raceName !== null) {
            $events[] = [
                'id' => 'race_day_'.$career->id,
                'title' => 'Race Day',
                'description' => 'Competing in: '.$raceName,
                'type' => 'race',
                'timestamp' => now()->toIso8601String(),
                'icon' => '🏆',
                'color' => '#F59E0B',
                'metadata' => ['strategy' => $strategy, 'race' => $raceName],
            ];
        }

        // Acquired skill events
        $skills = $this->extractMetadataSkills($meta);
        $acquiredSkills = array_values(
            array_filter($skills, fn (array $skillEntry): bool => ($skillEntry['acquired'] ?? false) === true)
        );

        foreach (array_reverse($acquiredSkills) as $index => $skill) {
            if ($index >= 5) {
                break;
            }
            $skillNameValue = $skill['name'] ?? null;
            $skillName = is_string($skillNameValue) ? $skillNameValue : 'Unknown Skill';
            $events[] = [
                'id' => 'skill_'.$career->id.'_'.$index,
                'title' => 'Skill Acquired',
                'description' => $skillName,
                'type' => 'skill',
                'timestamp' => $baseTime->copy()->addMinutes($index * 5)->toIso8601String(),
                'icon' => '⭐',
                'color' => '#8B5CF6',
                'metadata' => [
                    'notes' => $skill['notes'] ?? null,
                    'sp_cost' => $skill['sp_cost'] ?? null,
                ],
            ];
        }

        // Career import milestone
        $originalPlanTitle = $meta['original_plan_title'] ?? null;
        $careerStage = $meta['career_stage'] ?? null;
        $careerStageLabel = is_string($careerStage) ? $careerStage : 'senior';

        $events[] = [
            'id' => 'import_'.$career->id,
            'title' => 'Career Imported',
            'description' => is_string($originalPlanTitle)
                ? $originalPlanTitle
                : 'Career plan — '.$careerStageLabel.' stage',
            'type' => 'milestone',
            'timestamp' => $baseTime->toIso8601String(),
            'icon' => '📋',
            'color' => '#3B82F6',
            'metadata' => [
                'class_rank' => $meta['class_rank'] ?? null,
                'stage' => $careerStage,
            ],
        ];

        return $events;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeCareerMetadata(mixed $metadata): array
    {
        if (is_array($metadata)) {
            /** @var array<string, mixed> $normalized */
            $normalized = [];
            foreach ($metadata as $key => $value) {
                if (is_string($key)) {
                    $normalized[$key] = $value;
                }
            }

            return $normalized;
        }

        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);

            if (! is_array($decoded)) {
                return [];
            }

            /** @var array<string, mixed> $normalized */
            $normalized = [];
            foreach ($decoded as $key => $value) {
                if (is_string($key)) {
                    $normalized[$key] = $value;
                }
            }

            return $normalized;
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<int, array<string, mixed>>
     */
    private function extractMetadataSkills(array $metadata): array
    {
        $skills = $metadata['skills'] ?? [];
        if (! is_array($skills)) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $normalized */
        $normalized = [];

        foreach ($skills as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            /** @var array<string, mixed> $skillEntry */
            $skillEntry = [];
            foreach ($entry as $key => $value) {
                if (is_string($key)) {
                    $skillEntry[$key] = $value;
                }
            }

            $normalized[] = $skillEntry;
        }

        return $normalized;
    }
}
