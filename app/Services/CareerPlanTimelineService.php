<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Character;
use App\Models\Race;
use App\Services\Simulation\SimulationEngine;

class CareerPlanTimelineService
{
    public function __construct(
        private readonly SimulationEngine $simulationEngine,
        private readonly TrainingPredictionService $trainingPredictionService,
        private readonly SkillService $skillService,
        private readonly RaceConditionService $raceConditionService,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function build(Character $character, ?string $goal = null, array $options = []): array
    {
        $state = $this->initialState($character);
        $totalTurns = $character->scenario_type === 'unity_cup' ? 78 : 72;
        $timeline = [];
        $plannedRaces = $this->buildRaceLookup($character, $totalTurns);

        for ($turn = 1; $turn <= $totalTurns; $turn++) {
            $action = $this->determineAction($character, $state, $turn, $goal, $plannedRaces, $options);
            $state = $this->simulationEngine->applyAction($state, $action);

            $timeline[] = [
                'turn' => $turn,
                'action' => $action,
                'state_after' => [
                    'stats' => $state['stats'],
                    'energy' => $state['energy'],
                    'mood' => $state['mood'],
                    'sp' => $state['sp'],
                    'skills' => $state['skills'],
                ],
            ];
        }

        return [
            'plan_id' => '',
            'character_id' => $character->id,
            'created_at' => now()->toIso8601String(),
            'goal' => $goal ?? 'Complete the career',
            'status' => 'completed',
            'total_turns' => $totalTurns,
            'timeline' => $timeline,
            'summary' => [
                'final_predicted_stats' => $state['stats'],
                'total_sp_earned' => $state['total_sp_earned'],
                'races_won' => $state['races_won'],
                'confidence' => 0.78,
            ],
            'metadata' => [
                'options' => $options,
                'scenario_type' => $character->scenario_type,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function initialState(Character $character): array
    {
        $stats = $character->current_stats;
        if (! is_array($stats)) {
            $stats = [];
        }

        $normalizedStats = [];
        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $normalizedStats[$stat] = $this->intValue($stats[$stat] ?? 0);
        }

        $skills = $character->relationLoaded('skillAcquisitions')
            ? $character->skillAcquisitions
                ->pluck('skill_id')
                ->map(static fn (mixed $id): string => is_scalar($id) ? (string) $id : '')
                ->filter(static fn (string $id): bool => $id !== '')
                ->values()
                ->all()
            : [];

        return [
            'stats' => $normalizedStats,
            'energy' => (int) ($character->energy_level ?? 100),
            'mood' => (string) ($character->mood_status ?? 'normal'),
            'sp' => (int) ($character->available_sp ?? 0),
            'skills' => $skills,
            'total_sp_earned' => (int) ($character->available_sp ?? 0),
            'races_won' => 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<int, array<string, mixed>>  $plannedRaces
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function determineAction(Character $character, array $state, int $turn, ?string $goal, array $plannedRaces, array $options): array
    {
        if (isset($plannedRaces[$turn])) {
            return $this->buildRaceAction($turn, $state, $plannedRaces[$turn]);
        }

        $currentEnergy = $this->intValue($state['energy'] ?? 100);
        if ($currentEnergy <= 25) {
            return $this->buildRestAction($currentEnergy);
        }

        if ($this->intValue($state['sp'] ?? 0) >= 120 && $turn % 8 === 0) {
            $skillAction = $this->buildSkillAction($character, $turn, $state, $goal);
            if ($skillAction !== null) {
                return $skillAction;
            }
        }

        return $this->buildTrainingAction($character, $state, $goal, $options);
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function buildTrainingAction(Character $character, array $state, ?string $goal, array $options): array
    {
        $simulationCharacter = $this->makeSimulationCharacter($character, $state, $goal, $options);
        $recommendedTraining = $this->trainingPredictionService->getRecommendedTraining($simulationCharacter);

        $facility = is_string($recommendedTraining['recommended_facility'] ?? null)
            ? $recommendedTraining['recommended_facility']
            : $this->resolveTrainingFacility($state, $goal, $options);

        $prediction = $this->trainingPredictionService->getPredictionForFacility($simulationCharacter, $facility);
        $finalGains = is_array($prediction['final_gains'] ?? null) ? $prediction['final_gains'] : [];

        $expectedGains = [
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wit' => 0,
        ];

        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $gain = $finalGains[$stat] ?? 0;
            $expectedGains[$stat] = $this->intValue($gain);
        }

        $expectedSpGain = $this->intValue($finalGains['sp'] ?? 0);
        $supportBonus = $this->floatValue($prediction['support_bonus'] ?? 0.0);
        $friendshipActive = (bool) ($prediction['is_friendship'] ?? false);
        $friendshipCards = $this->intValue($prediction['friendship_card_count'] ?? 0);

        $reasonParts = [
            ucfirst($facility).' training is currently the strongest projected option.',
            is_string($recommendedTraining['reason'] ?? null) ? $recommendedTraining['reason'] : null,
            $supportBonus > 0 ? 'Support bonus projection: '.round($supportBonus, 2).'x.' : null,
            $friendshipActive ? "Friendship training is active with {$friendshipCards} support card(s)." : null,
        ];

        if ($goal !== null && $goal !== '') {
            $reasonParts[] = 'This remains aligned with the goal: '.$goal.'.';
        }

        return [
            'type' => 'training',
            'facility' => $facility,
            'expected_gains' => $expectedGains,
            'expected_sp_gain' => $expectedSpGain,
            'energy_after' => max(0, $this->intValue($state['energy'] ?? 100) - 18),
            'risk_percentage' => $this->calculateTrainingRisk($this->intValue($state['energy'] ?? 100)),
            'reasoning' => implode(' ', array_values(array_filter($reasonParts, static fn (mixed $part): bool => is_string($part) && $part !== ''))),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRestAction(int $currentEnergy): array
    {
        return [
            'type' => 'rest',
            'energy_after' => min(100, $currentEnergy + 35),
            'reasoning' => 'Energy is low enough that resting now protects the next few turns and reduces failure risk.',
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $race
     * @return array<string, mixed>
     */
    private function buildRaceAction(int $turn, array $state, array $race): array
    {
        $surface = is_string($race['surface'] ?? null) ? $race['surface'] : RaceConditionService::SURFACE_TURF;
        $trackCondition = is_string($race['track_condition'] ?? null) ? $race['track_condition'] : RaceConditionService::CONDITION_FIRM;
        $stateStats = is_array($state['stats'] ?? null) ? $state['stats'] : [];
        $adjustedStats = $this->raceConditionService->applyConditionPenalties($this->intStats($stateStats), $trackCondition, $surface);

        $requirements = is_array($race['stat_requirements'] ?? null) ? $race['stat_requirements'] : $this->defaultRaceRequirements($race);
        $ratioTotal = 0.0;
        $count = 0;
        foreach ($requirements as $stat => $requiredValue) {
            if (! is_numeric($requiredValue)) {
                continue;
            }

            $ratioTotal += min(1.25, ($adjustedStats[$stat] ?? 0) / max(1, (int) $requiredValue));
            $count++;
        }

        $baseRatio = $count > 0 ? $ratioTotal / $count : 0.7;
        $staminaDrain = $this->raceConditionService->calculateStaminaDrain($trackCondition, $surface);
        $winProbability = (int) max(10, min(95, round(($baseRatio * 100) - ($staminaDrain * 3))));

        $resultVerb = $winProbability >= 55 ? 'likely win' : ($winProbability >= 35 ? 'contested result' : 'uphill race');

        return [
            'type' => 'race',
            'race_id' => $this->intValue($race['race_id'] ?? (1000 + $turn)),
            'expected_result' => "win_probability: {$winProbability}% ({$resultVerb})",
            'sp_reward' => $this->intValue($race['sp_reward'] ?? 45),
            'reasoning' => $this->stringValue($race['reasoning'] ?? 'This scheduled race fits the current progression curve and supports fan gain plus momentum.'),
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function buildSkillAction(Character $character, int $turn, array $state, ?string $goal): ?array
    {
        $simulationCharacter = $this->makeSimulationCharacter($character, $state, $goal, []);
        $recommendations = $this->skillService->getRecommendations($simulationCharacter, 8);
        $stateSkills = is_array($state['skills'] ?? null) ? $state['skills'] : [];
        $simulatedSkillIds = array_map(static fn (mixed $skillId): int => is_numeric($skillId) ? (int) $skillId : 0, $stateSkills);

        $recommendation = $recommendations->first(function (array $recommendation) use ($simulatedSkillIds): bool {
            $skill = $recommendation['skill'];

            return ! in_array($skill->id, $simulatedSkillIds, true);
        });

        if ($recommendation === null) {
            return null;
        }

        $skill = $recommendation['skill'];

        $hintCount = $skill->hints()->where('character_id', $character->id)->count();
        $spentSp = $this->skillService->calculateFinalCost($skill->base_sp_cost, $hintCount);

        if ($spentSp > $this->intValue($state['sp'] ?? 0)) {
            return null;
        }

        $reasonParts = [
            (string) ($recommendation['reason'] ?? 'Acquire a high-value skill power spike.'),
            $hintCount > 0 ? 'Hint discount applied at level '.$hintCount.'.' : null,
            $skill->canEvolve() ? 'This skill also preserves an evolution path.' : null,
            $goal !== null && $goal !== '' ? 'Chosen with the overall goal in mind: '.$goal.'.' : null,
        ];

        return [
            'type' => 'skill',
            'skill_id' => $skill->id,
            'skill_name' => $skill->name,
            'spent_sp' => $spentSp,
            'base_cost' => $skill->base_sp_cost,
            'discounted_cost' => $spentSp,
            'reasoning' => implode(' ', array_values(array_filter($reasonParts, static fn (mixed $part): bool => is_string($part) && $part !== ''))),
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $options
     */
    private function resolveTrainingFacility(array $state, ?string $goal, array $options): string
    {
        $focusStats = $options['focus_stats'] ?? [];
        if (is_array($focusStats) && isset($focusStats[0]) && is_string($focusStats[0])) {
            return $focusStats[0];
        }

        if ($goal !== null) {
            $goalLower = mb_strtolower($goal);
            foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
                if (str_contains($goalLower, $stat)) {
                    return $stat;
                }
            }
        }

        $stats = $this->intStats(is_array($state['stats'] ?? null) ? $state['stats'] : []);
        asort($stats);

        return (string) array_key_first($stats);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRaceLookup(Character $character, int $totalTurns): array
    {
        $lookup = [];
        $raceSchedule = $character->race_schedule;

        if (is_array($raceSchedule)) {
            foreach ($raceSchedule as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $turn = $this->intValue($entry['turn'] ?? $entry['turn_number'] ?? $entry['current_turn'] ?? 0);
                if ($turn <= 0 || $turn > $totalTurns) {
                    continue;
                }

                $lookup[$turn] = $entry;
            }
        }

        if ($lookup === []) {
            $recordedRaces = Race::query()
                ->where('character_id', $character->id)
                ->whereNotNull('turn_number')
                ->orderBy('turn_number')
                ->get();

            foreach ($recordedRaces as $race) {
                $turn = (int) ($race->turn_number ?? 0);
                if ($turn <= 0 || $turn > $totalTurns) {
                    continue;
                }

                $lookup[$turn] = [
                    'race_id' => $race->id,
                    'surface' => $race->surface,
                    'track_condition' => $race->track_condition,
                    'distance_meters' => $race->distance_meters,
                    'distance_category' => $race->distance_category,
                    'sp_reward' => $race->sp_reward,
                    'stat_requirements' => [
                        'speed' => (int) ($race->speed_at_race ?? 0),
                        'stamina' => (int) ($race->stamina_at_race ?? 0),
                        'power' => (int) ($race->power_at_race ?? 0),
                        'guts' => (int) ($race->guts_at_race ?? 0),
                        'wit' => (int) ($race->wit_at_race ?? 0),
                    ],
                    'reasoning' => 'Use an existing planned race from the character schedule to keep the simulation grounded in current run data.',
                ];
            }
        }

        if ($lookup === []) {
            foreach ([12, 24, 36, 48, 60, $totalTurns] as $turn) {
                $lookup[$turn] = [
                    'race_id' => 1000 + $turn,
                    'surface' => RaceConditionService::SURFACE_TURF,
                    'track_condition' => RaceConditionService::CONDITION_FIRM,
                    'distance_category' => 'mile',
                    'distance_meters' => 1600,
                    'sp_reward' => 45,
                    'reasoning' => 'Planned milestone race to validate progress and gain rewards.',
                ];
            }
        }

        ksort($lookup);

        return $lookup;
    }

    private function calculateTrainingRisk(int $energy): int
    {
        return match (true) {
            $energy <= 20 => 40,
            $energy <= 35 => 25,
            $energy <= 50 => 15,
            default => 8,
        };
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $options
     */
    private function makeSimulationCharacter(Character $character, array $state, ?string $goal, array $options): Character
    {
        $simulationCharacter = new Character;
        $simulationCharacter->forceFill([
            'id' => $character->id,
            'user_id' => $character->user_id,
            'name' => $character->name,
            'scenario_type' => $character->scenario_type,
            'career_stage' => $character->career_stage,
            'current_turn' => $character->current_turn,
            'current_stats' => $this->intStats(is_array($state['stats'] ?? null) ? $state['stats'] : []),
            'energy_level' => $this->intValue($state['energy'] ?? 100),
            'mood_status' => $this->stringValue($state['mood'] ?? 'normal'),
            'available_sp' => $this->intValue($state['sp'] ?? 0),
            'growth_rates' => $character->growth_rates,
            'goals' => $this->mergeGoalContext($character, $goal, $options),
            'stat_priorities' => $character->stat_priorities,
        ]);

        if ($character->relationLoaded('activeSupportDeck')) {
            $simulationCharacter->setRelation('activeSupportDeck', $character->getRelation('activeSupportDeck'));
        } else {
            $simulationCharacter->setRelation('activeSupportDeck', $character->activeSupportDeck);
        }

        return $simulationCharacter;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function mergeGoalContext(Character $character, ?string $goal, array $options): array
    {
        $goals = is_array($character->goals) ? $character->goals : [];

        if ($goal !== null && $goal !== '') {
            $goals['plan_goal'] = $goal;
        }

        $focusStats = $options['focus_stats'] ?? [];
        if (is_array($focusStats) && $focusStats !== []) {
            $targetStats = $goals['target_stats'] ?? [];
            $targetStats = is_array($targetStats) ? $targetStats : [];
            foreach ($focusStats as $stat) {
                if (! is_string($stat)) {
                    continue;
                }

                $characterStats = is_array($character->current_stats) ? $character->current_stats : [];
                $targetStats[$stat] = max(600, ($this->intValue($characterStats[$stat] ?? 0) + 150));
            }

            $goals['target_stats'] = $targetStats;
        }

        return $goals;
    }

    /**
     * @param  array<string, mixed>  $race
     * @return array<string, int>
     */
    private function defaultRaceRequirements(array $race): array
    {
        $distanceCategory = $this->stringValue($race['distance_category'] ?? 'mile');

        return match ($distanceCategory) {
            'sprint' => ['speed' => 350, 'stamina' => 220, 'power' => 250, 'guts' => 180, 'wit' => 220],
            'medium' => ['speed' => 500, 'stamina' => 420, 'power' => 350, 'guts' => 250, 'wit' => 300],
            'long' => ['speed' => 550, 'stamina' => 600, 'power' => 380, 'guts' => 280, 'wit' => 320],
            default => ['speed' => 420, 'stamina' => 300, 'power' => 300, 'guts' => 220, 'wit' => 260],
        };
    }

    private function intValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function floatValue(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param  array<string, mixed>  $stats
     * @return array<string, int>
     */
    private function intStats(array $stats): array
    {
        return [
            'speed' => $this->intValue($stats['speed'] ?? 0),
            'stamina' => $this->intValue($stats['stamina'] ?? 0),
            'power' => $this->intValue($stats['power'] ?? 0),
            'guts' => $this->intValue($stats['guts'] ?? 0),
            'wit' => $this->intValue($stats['wit'] ?? 0),
        ];
    }
}
