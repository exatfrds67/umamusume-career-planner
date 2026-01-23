<?php

namespace App\Http\Controllers;

use App\Models\Character;
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
            ->with(['aptitudes', 'skillAcquisitions.skill', 'supportCards'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Get selected character from request or default to first active character
        $selectedCharacterId = $request->query('character');
        $selectedCharacter = null;

        if ($selectedCharacterId) {
            $selectedCharacter = $characters->firstWhere('id', $selectedCharacterId);
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

        return [
            'metrics' => $this->getMetrics($character),
            'stats' => $this->getStats($character),
            'goals' => $this->getGoals($character),
            'races' => $this->getUpcomingRaces($character),
            'trainingSuggestions' => $this->getTrainingSuggestions($character),
            'recentResults' => $this->getRecentResults($character),
            'moodEnergy' => $this->getMoodEnergy($character),
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
                'maxTurns' => 70,
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
        $stats = $character->current_stats ?? [];
        // Handle case where stats might be a JSON string from old data
        if (is_string($stats)) {
            $decodedStats = json_decode($stats, true);
            $stats = is_array($decodedStats) ? $decodedStats : [];
        }
        if (! is_array($stats)) {
            $stats = [];
        }

        $goals = $character->goals ?? [];
        // Handle case where goals might be a JSON string from old data
        if (is_string($goals)) {
            $decodedGoals = json_decode($goals, true);
            $goals = is_array($decodedGoals) ? $decodedGoals : [];
        }
        if (! is_array($goals)) {
            $goals = [];
        }

        // Calculate overall grade based on average stats
        $statValues = array_values(array_filter($stats, fn ($v) => is_numeric($v)));
        $avgStat = count($statValues) > 0 ? array_sum($statValues) / count($statValues) : 0;
        $overallGrade = $character->getStatGrade((int) $avgStat);

        // Get target grade from goals
        $targetGrade = isset($goals['target_grade']) && is_string($goals['target_grade'])
            ? $goals['target_grade']
            : 'A+';

        // Get skills count
        $skillsAcquired = $character->skillAcquisitions()->count();
        $targetSkills = isset($goals['target_skills']) && is_numeric($goals['target_skills'])
            ? (int) $goals['target_skills']
            : 12;

        // Calculate skill points (sum of all skill costs or from character data)
        $skillPoints = $character->skillAcquisitions()->sum('final_sp_cost') ?? 0;

        // Get next race info
        $raceSchedule = $character->race_schedule ?? [];
        // Handle case where race_schedule might be a JSON string from old data
        if (is_string($raceSchedule)) {
            $decodedSchedule = json_decode($raceSchedule, true);
            $raceSchedule = is_array($decodedSchedule) ? $decodedSchedule : [];
        }
        // Ensure it's an array
        if (! is_array($raceSchedule)) {
            $raceSchedule = [];
        }

        $nextRace = null;
        $turnsUntilRace = null;

        if (! empty($raceSchedule)) {
            $upcomingRaces = array_filter($raceSchedule, function ($race) use ($character) {
                return is_array($race) && isset($race['turn']) && is_numeric($race['turn']) && $race['turn'] > $character->current_turn;
            });
            if (! empty($upcomingRaces)) {
                $nextRaceData = reset($upcomingRaces);
                if (is_array($nextRaceData)) {
                    $nextRace = isset($nextRaceData['name']) && is_string($nextRaceData['name'])
                        ? $nextRaceData['name']
                        : 'Unknown Race';
                    $raceTurn = isset($nextRaceData['turn']) && is_numeric($nextRaceData['turn'])
                        ? (int) $nextRaceData['turn']
                        : $character->current_turn;
                    $turnsUntilRace = $raceTurn - $character->current_turn;
                }
            }
        }

        // Determine track status
        $progress = $character->getProgressPercentage();
        $expectedProgress = ($character->current_turn / 70) * 100;
        $trackStatus = match (true) {
            $progress >= $expectedProgress + 10 => 'Ahead',
            $progress >= $expectedProgress - 10 => 'On Track',
            default => 'Behind',
        };

        return [
            'currentTurn' => $character->current_turn ?? 1,
            'maxTurns' => 70,
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
        $stats = $character->current_stats ?? [];

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
        $goals = $character->goals ?? [];
        // Handle case where goals might be a JSON string from old data
        if (is_string($goals)) {
            $decodedGoals = json_decode($goals, true);
            $goals = is_array($decodedGoals) ? $decodedGoals : [];
        }
        if (! is_array($goals)) {
            $goals = [];
        }

        $shortTermGoal = isset($goals['short_term']) && is_string($goals['short_term'])
            ? $goals['short_term']
            : 'No short-term goal set';
        $longTermGoal = isset($goals['long_term']) && is_string($goals['long_term'])
            ? $goals['long_term']
            : 'No long-term goal set';

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
        $goals = $character->goals ?? [];
        // Handle case where goals might be a JSON string from old data
        if (is_string($goals)) {
            $decodedGoals = json_decode($goals, true);
            $goals = is_array($decodedGoals) ? $decodedGoals : [];
        }
        if (! is_array($goals)) {
            $goals = [];
        }

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
            return min(100, ($character->current_turn / 70) * 100);
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
        $raceSchedule = $character->race_schedule ?? [];
        // Handle case where race_schedule might be a JSON string from old data
        if (is_string($raceSchedule)) {
            $raceSchedule = json_decode($raceSchedule, true) ?? [];
        }

        // Ensure it's an array
        if (! is_array($raceSchedule)) {
            $raceSchedule = [];
        }

        $currentTurn = $character->current_turn ?? 1;
        $stats = $character->current_stats ?? [];
        // Handle case where stats might be a JSON string from old data
        if (is_string($stats)) {
            $decodedStats = json_decode($stats, true);
            $stats = is_array($decodedStats) ? $decodedStats : [];
        }
        if (! is_array($stats)) {
            $stats = [];
        }

        $upcomingRaces = [];

        foreach ($raceSchedule as $race) {
            if (! is_array($race)) {
                continue;
            }

            $raceTurn = isset($race['turn']) && is_numeric($race['turn']) ? (int) $race['turn'] : 0;
            if ($raceTurn > $currentTurn) {
                // Calculate readiness based on stats vs race requirements
                $readiness = $this->calculateRaceReadiness($stats, $race);

                $upcomingRaces[] = [
                    'name' => isset($race['name']) && is_string($race['name']) ? $race['name'] : 'Unknown Race',
                    'grade' => isset($race['grade']) && is_string($race['grade']) ? $race['grade'] : 'G3',
                    'date' => isset($race['date']) && is_string($race['date'])
                        ? $race['date']
                        : now()->addDays($raceTurn - $currentTurn)->format('Y-m-d'),
                    'turn' => $raceTurn,
                    'turnsAway' => $raceTurn - $currentTurn,
                    'readiness' => $readiness,
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
            $totalReadiness = ($totalReadiness ?? 0) + $readiness;
            $count = ($count ?? 0) + 1;
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
        $stats = $character->current_stats ?? [];
        $priorities = $character->stat_priorities ?? [];
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

            $index = ($index ?? 0) + 1;
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
        $stats = $character->current_stats ?? [];
        // Handle case where stats might be a JSON string from old data
        if (is_string($stats)) {
            $decodedStats = json_decode($stats, true);
            $stats = is_array($decodedStats) ? $decodedStats : [];
        }
        if (! is_array($stats)) {
            $stats = [];
        }

        $goals = $character->goals ?? [];
        // Handle case where goals might be a JSON string from old data
        if (is_string($goals)) {
            $decodedGoals = json_decode($goals, true);
            $goals = is_array($decodedGoals) ? $decodedGoals : [];
        }
        if (! is_array($goals)) {
            $goals = [];
        }

        $targets = isset($goals['target_stats']) && is_array($goals['target_stats'])
            ? $goals['target_stats']
            : [
                'speed' => 1000,
                'stamina' => 800,
                'power' => 800,
                'guts' => 600,
                'wit' => 600,
            ];

        $deficits = [];
        foreach ($targets as $stat => $target) {
            if (! is_string($stat) || ! is_numeric($target)) {
                continue;
            }

            $current = isset($stats[$stat]) && is_numeric($stats[$stat]) ? (int) $stats[$stat] : 0;
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
        // Handle case where growth_rates might be a JSON string from old data
        if (is_string($growthRates)) {
            $decodedRates = json_decode($growthRates, true);
            $growthRates = is_array($decodedRates) ? $decodedRates : [];
        }
        if (! is_array($growthRates)) {
            $growthRates = [];
        }

        $baseGain = 12;
        $growthBonus = isset($growthRates[$stat]) && is_numeric($growthRates[$stat])
            ? (float) $growthRates[$stat] / 100
            : 0;

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
        // For now, return placeholder data
        // In a full implementation, this would query training_sessions, race_results, skill_acquisitions
        $results = [];

        // Get recent skill acquisitions
        $recentAcquisitions = $character->skillAcquisitions()
            ->with('skill')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentAcquisitions as $acquisition) {
            $skillName = $acquisition->skill->name ?? 'Unknown Skill';
            $results[] = [
                'type' => 'skill',
                'description' => "Skill acquired: {$skillName}",
                'highlight' => $skillName,
                'timestamp' => $acquisition->created_at,
                'icon' => 'star',
                'color' => 'secondary',
            ];
        }

        // If no real data, show placeholder
        // Placeholder handling moved to view
        if (empty($results)) {
            $results = [];
        }

        return array_slice($results, 0, 3);
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
}
