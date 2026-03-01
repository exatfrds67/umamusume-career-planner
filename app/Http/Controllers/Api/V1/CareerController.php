<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\CareerPhase;
use App\Enums\Mood;
use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Services\CareerAnalyticsService;
use App\Services\TrainingAdvisoryService;
use App\Services\TrainingCalculationService;
use App\Services\TrainingService;
use App\ValueObjects\CharacterStats;
use App\ValueObjects\SupportCardDeck;
use App\ValueObjects\TrainingContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CareerController extends Controller
{
    public function __construct(
        protected TrainingCalculationService $trainingCalculationService,
        protected TrainingService $trainingService,
        protected CareerAnalyticsService $careerAnalyticsService,
        protected TrainingAdvisoryService $trainingAdvisoryService,
    ) {}

    /**
     * Verify that the career's character belongs to the authenticated user.
     * Uses policy authorization which allows admins full access.
     */
    private function verifyCareerOwnership(Career $career): void
    {
        $this->authorize('view', $career);
    }

    /**
     * Display a listing of careers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Career::with('character')
            ->whereHas('character', function ($q) {
                $q->where('user_id', Auth::id());
            });

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Pagination
        $perPageInput = $request->input('per_page', 15);
        $perPage = is_numeric($perPageInput) ? (int) $perPageInput : 15;
        $careers = $query->paginate($perPage);

        return response()->json([
            'data' => $careers->items(),
            'meta' => [
                'current_page' => $careers->currentPage(),
                'last_page' => $careers->lastPage(),
                'per_page' => $careers->perPage(),
                'total' => $careers->total(),
            ],
        ]);
    }

    /**
     * Store a newly created career.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'character_id' => ['required', 'integer', 'exists:ucp_characters,id'],
            'scenario_type' => ['required', 'string', 'in:ura_finale,unity_cup'],
        ]);

        /** @var Character $character */
        $character = Character::where('user_id', Auth::id())
            ->findOrFail($validated['character_id']);

        // Generate a default career name if not provided
        $careerName = $validated['career_name'] ?? \sprintf(
            '%s - %s Career #%d',
            $character->name,
            ucfirst(str_replace('_', ' ', $validated['scenario_type'])),
            Career::where('character_id', $character->id)->count() + 1
        );

        $career = Career::create([
            'character_id' => $character->id,
            'user_id' => Auth::id(),
            'career_name' => $careerName,
            'scenario_type' => $validated['scenario_type'],
            'status' => 'active',
            'current_turn' => 1,
            'current_phase' => 'junior',
            'started_at' => now(),
        ]);

        return response()->json([
            'data' => $career,
        ], 201);
    }

    /**
     * Display the specified career.
     */
    public function show(string $id): JsonResponse
    {
        $career = Career::with('character')
            ->findOrFail($id);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        return response()->json([
            'data' => $career,
        ]);
    }

    /**
     * Update the specified career.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:active,completed,abandoned'],
            'current_turn' => ['nullable', 'integer', 'min:1', 'max:78'],
            'current_phase' => ['nullable', 'string'],
        ]);

        $career = Career::with('character')
            ->findOrFail($id);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        $career->update($validated);

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $career->update(['completed_at' => now()]);
        }

        return response()->json([
            'data' => $career,
        ]);
    }

    /**
     * Get available races for the career.
     */
    public function availableRaces(string $id): JsonResponse
    {
        $career = Career::with('character')
            ->findOrFail($id);

        $this->verifyCareerOwnership($career);

        $races = Race::where('career_id', $career->id)
            ->where('turn_number', '>=', $career->current_turn)
            ->orderBy('turn_number')
            ->get(['id', 'race_name', 'race_grade', 'distance_meters', 'distance_category', 'surface', 'track_type', 'turn_number', 'career_phase', 'weather', 'track_condition']);

        if ($races->isEmpty()) {
            $races = Race::where('career_id', $career->id)
                ->orderByDesc('turn_number')
                ->limit(10)
                ->get(['id', 'race_name', 'race_grade', 'distance_meters', 'distance_category', 'surface', 'track_type', 'turn_number', 'career_phase', 'weather', 'track_condition']);
        }

        return response()->json([
            'data' => $races,
            'meta' => [
                'current_turn' => $career->current_turn,
                'current_phase' => $career->current_phase,
                'total_available' => $races->count(),
            ],
        ]);
    }

    /**
     * Get training predictions for the career.
     */
    public function trainingPredictions(string $id): JsonResponse
    {
        $career = Career::with('character')
            ->findOrFail($id);

        $this->verifyCareerOwnership($career);

        $character = $career->character;
        if ($character === null) {
            abort(404, 'Character not found');
        }

        $trainingTypes = ['speed', 'stamina', 'power', 'guts', 'wit'];

        $predictions = $this->trainingCalculationService->calculateBatchPredictions(
            $character,
            $trainingTypes
        );

        $recommended = $this->trainingCalculationService->getRecommendedTraining($character);

        return response()->json([
            'data' => [
                'predictions' => $predictions,
                'recommended' => $recommended,
                'career_turn' => $career->current_turn,
                'career_phase' => $career->current_phase,
            ],
        ]);
    }

    /**
     * Store a training session.
     */
    public function storeTrainingSession(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'training_type' => ['required', 'string', 'in:speed,stamina,power,guts,wit'],
            'turn_number' => ['required', 'integer', 'min:1'],
            'actual_gains' => ['nullable', 'array'],
            'actual_gains.speed' => ['nullable', 'integer', 'min:0'],
            'actual_gains.stamina' => ['nullable', 'integer', 'min:0'],
            'actual_gains.power' => ['nullable', 'integer', 'min:0'],
            'actual_gains.guts' => ['nullable', 'integer', 'min:0'],
            'actual_gains.wit' => ['nullable', 'integer', 'min:0'],
        ]);

        $career = Career::with('character')
            ->findOrFail($id);

        $this->verifyCareerOwnership($career);

        $character = $career->character;
        if ($character === null) {
            abort(404, 'Character not found');
        }

        if (! empty($validated['actual_gains'])) {
            $actualGains = $validated['actual_gains'];
        } else {
            $prediction = $this->trainingCalculationService->calculateTrainingPrediction(
                $character,
                $validated['training_type']
            );
            $actualGains = $prediction['stat_gains'];
        }

        $result = $this->trainingService->executeTraining(
            $character,
            $validated['training_type'],
            $actualGains
        );

        return response()->json([
            'data' => $result,
        ], 201);
    }

    /**
     * Store multiple training sessions in bulk.
     */
    public function bulkStoreTrainingSessions(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'sessions' => ['required', 'array', 'min:1', 'max:100'],
            'sessions.*.training_type' => ['required', 'string', 'in:speed,stamina,power,guts,wit'],
            'sessions.*.turn_number' => ['required', 'integer', 'min:1'],
            'sessions.*.actual_gains' => ['nullable', 'array'],
            'sessions.*.actual_gains.speed' => ['nullable', 'integer', 'min:0'],
            'sessions.*.actual_gains.stamina' => ['nullable', 'integer', 'min:0'],
            'sessions.*.actual_gains.power' => ['nullable', 'integer', 'min:0'],
            'sessions.*.actual_gains.guts' => ['nullable', 'integer', 'min:0'],
            'sessions.*.actual_gains.wit' => ['nullable', 'integer', 'min:0'],
        ]);

        $career = Career::with('character')
            ->findOrFail($id);

        $this->verifyCareerOwnership($career);

        $character = $career->character;
        if ($character === null) {
            abort(404, 'Character not found');
        }

        $results = [];

        foreach ($validated['sessions'] as $sessionData) {
            if (! empty($sessionData['actual_gains'])) {
                $actualGains = $sessionData['actual_gains'];
            } else {
                $prediction = $this->trainingCalculationService->calculateTrainingPrediction(
                    $character,
                    $sessionData['training_type']
                );
                $actualGains = $prediction['stat_gains'];
            }

            $results[] = $this->trainingService->executeTraining(
                $character,
                $sessionData['training_type'],
                $actualGains
            );

            $character->refresh();
        }

        return response()->json([
            'message' => 'Training sessions created successfully',
            'count' => \count($results),
            'results' => $results,
        ], 201);
    }

    /**
     * Store a race entry.
     */
    public function storeRace(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'race_name' => ['required', 'string', 'max:255'],
            'race_grade' => ['required', 'string', 'in:G1,G2,G3,OP,Pre-OP,debut,maiden'],
            'turn_number' => ['required', 'integer', 'min:1'],
            'distance_category' => ['nullable', 'string', 'in:short,mile,intermediate,long'],
            'distance_meters' => ['nullable', 'integer', 'min:800', 'max:4000'],
            'surface' => ['nullable', 'string', 'in:turf,dirt'],
            'track_type' => ['nullable', 'string'],
            'running_style' => ['nullable', 'string', 'in:escape,leading,insert,tracking'],
            'weather' => ['nullable', 'string', 'in:sunny,cloudy,rainy,snowy'],
            'track_condition' => ['nullable', 'string', 'in:firm,good,yielding,soft,heavy'],
            'field_size' => ['nullable', 'integer', 'min:1', 'max:18'],
            'finish_position' => ['nullable', 'integer', 'min:1', 'max:18'],
            'won_race' => ['nullable', 'boolean'],
            'sp_reward' => ['nullable', 'integer', 'min:0'],
            'fans_gained' => ['nullable', 'integer', 'min:0'],
        ]);

        $career = Career::with('character')
            ->findOrFail($id);

        $this->verifyCareerOwnership($career);

        $character = $career->character;
        if ($character === null) {
            abort(404, 'Character not found');
        }

        $currentStats = $character->current_stats;
        $finishPosition = $validated['finish_position'] ?? null;

        $race = Race::create([
            'career_id' => $career->id,
            'character_id' => $career->character_id,
            'race_name' => $validated['race_name'],
            'race_grade' => $validated['race_grade'],
            'turn_number' => $validated['turn_number'],
            'career_phase' => $career->current_phase ?? 'classic',
            'distance_category' => $validated['distance_category'] ?? 'intermediate',
            'distance_meters' => $validated['distance_meters'] ?? 2000,
            'surface' => $validated['surface'] ?? 'turf',
            'track_type' => $validated['track_type'] ?? null,
            'running_style' => $validated['running_style'] ?? 'leading',
            'track_condition' => $validated['track_condition'] ?? 'good',
            'weather' => $validated['weather'] ?? 'sunny',
            'field_size' => $validated['field_size'] ?? 18,
            'energy_level' => $character->energy_level ?? 100,
            'speed_at_race' => $currentStats['speed'] ?? 0,
            'stamina_at_race' => $currentStats['stamina'] ?? 0,
            'power_at_race' => $currentStats['power'] ?? 0,
            'guts_at_race' => $currentStats['guts'] ?? 0,
            'wit_at_race' => $currentStats['wit'] ?? 0,
            'finish_position' => $finishPosition,
            'won_race' => $validated['won_race'] ?? ($finishPosition === 1),
            'race_result' => match (true) {
                $finishPosition === 1 => 'victory',
                $finishPosition !== null && $finishPosition <= 2 => 'place',
                $finishPosition !== null && $finishPosition <= 3 => 'show',
                default => 'out_of_money',
            },
            'sp_reward' => $validated['sp_reward'] ?? 0,
            'fans_gained' => $validated['fans_gained'] ?? 0,
        ]);

        return response()->json([
            'data' => $race,
        ], 201);
    }

    /**
     * Update a race result.
     */
    public function updateRace(Request $request, string $careerId, string $raceId): JsonResponse
    {
        $validated = $request->validate([
            'finish_position' => ['required', 'integer', 'min:1', 'max:18'],
            'won_race' => ['required', 'boolean'],
            'sp_reward' => ['nullable', 'integer', 'min:0'],
        ]);

        $career = Career::with('character')
            ->findOrFail($careerId);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        $race = Race::where('career_id', $career->id)
            ->findOrFail($raceId);

        $race->update([
            'finish_position' => $validated['finish_position'],
            'won_race' => $validated['won_race'],
            'sp_reward' => $validated['sp_reward'] ?? 0,
        ]);

        return response()->json([
            'data' => $race,
        ]);
    }

    /**
     * Get all races for the career.
     */
    public function races(string $id): JsonResponse
    {
        $career = Career::with('character')
            ->findOrFail($id);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        $races = Race::where('career_id', $career->id)->get();

        return response()->json([
            'data' => $races,
        ]);
    }

    /**
     * Get all training sessions for the career.
     */
    public function trainingSessions(string $id): JsonResponse
    {
        $career = Career::with('character')
            ->findOrFail($id);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        $trainingSessions = TrainingSession::where('career_id', $career->id)->get();

        return response()->json([
            'data' => $trainingSessions,
        ]);
    }

    /**
     * Remove the specified career.
     */
    public function destroy(string $id): JsonResponse
    {
        $career = Career::with('character')
            ->findOrFail($id);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        $career->delete();

        return response()->json([
            'message' => 'Career deleted successfully',
        ]);
    }

    /**
     * Get career report.
     */
    public function report(string $id): JsonResponse
    {
        $career = Career::with(['character', 'trainingSessions', 'races'])
            ->findOrFail($id);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        $report = [
            'career_summary' => [
                'total_turns' => $career->current_turn,
                'status' => $career->status,
                'scenario_type' => $career->scenario_type,
            ],
            'stat_progression' => [
                'final_speed' => $career->final_speed,
                'final_stamina' => $career->final_stamina,
                'final_power' => $career->final_power,
                'final_guts' => $career->final_guts,
                'final_wit' => $career->final_wit,
            ],
            'training_analysis' => [
                'total_sessions' => $career->trainingSessions->count(),
                'average_gains' => $career->trainingSessions->count() > 0
                    ? round((float) ($career->trainingSessions->avg(fn ($s) => ($s->speed_gain ?? 0) + ($s->stamina_gain ?? 0) + ($s->power_gain ?? 0) + ($s->guts_gain ?? 0) + ($s->wit_gain ?? 0)) ?? 0.0), 2)
                    : 0,
            ],
            'race_performance' => [
                'total_races' => $career->races->count(),
                'wins' => $career->races->where('won_race', true)->count(),
            ],
        ];

        return response()->json([
            'data' => $report,
        ]);
    }

    /**
     * Get career statistics.
     */
    public function statistics(string $id): JsonResponse
    {
        $career = Career::with(['character', 'trainingSessions', 'races'])
            ->findOrFail($id);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        $totalTrainingSessions = $career->trainingSessions->count();
        $totalStatGains = $career->trainingSessions->reduce(
            fn (int $carry, $session): int => $carry
                + (int) ($session->speed_gain ?? 0)
                + (int) ($session->stamina_gain ?? 0)
                + (int) ($session->power_gain ?? 0)
                + (int) ($session->guts_gain ?? 0)
                + (int) ($session->wit_gain ?? 0),
            0,
        );

        // Calculate efficiency rating (stat gains per training session)
        $efficiencyRating = $totalTrainingSessions > 0
            ? round($totalStatGains / $totalTrainingSessions, 2)
            : 0;

        $stats = [
            'total_turns' => $career->current_turn,
            'total_training_sessions' => $totalTrainingSessions,
            'total_stat_gains' => $totalStatGains,
            'efficiency_rating' => $efficiencyRating,
            'races_entered' => $career->races->count(),
            'races_won' => $career->races->where('won_race', true)->count(),
            'total_sp_earned' => $career->races->sum('sp_reward'),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Compare multiple careers.
     */
    public function compare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'career_ids' => ['required', 'array', 'min:2'],
            'career_ids.*' => ['required', 'integer', 'exists:ucp_careers,id'],
        ]);

        $careers = Career::with(['character', 'trainingSessions', 'races'])
            ->whereIn('id', $validated['career_ids'])
            ->get();

        // Verify all careers belong to characters owned by the authenticated user
        /** @var Career $career */
        foreach ($careers as $career) {
            $this->verifyCareerOwnership($career);
        }

        $comparison = [
            'careers' => $careers,
            'comparison_summary' => [
                'total_compared' => $careers->count(),
            ],
            'stat_comparison' => [
                'average_speed' => $careers->avg('final_speed'),
                'average_stamina' => $careers->avg('final_stamina'),
                'average_power' => $careers->avg('final_power'),
                'average_guts' => $careers->avg('final_guts'),
                'average_wit' => $careers->avg('final_wit'),
            ],
            'best_performer' => $careers->sortByDesc('final_speed')->first(),
        ];

        return response()->json([
            'data' => $comparison,
        ]);
    }

    /**
     * Identify success patterns across careers.
     */
    public function patterns(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'career_ids' => ['required', 'array', 'min:2'],
            'career_ids.*' => ['required', 'integer', 'exists:ucp_careers,id'],
        ]);

        $careers = Career::with(['character', 'trainingSessions', 'races'])
            ->whereIn('id', $validated['career_ids'])
            ->get();

        /** @var Career $career */
        foreach ($careers as $career) {
            $this->verifyCareerOwnership($career);
        }

        $characterIds = $careers->pluck('character_id')->unique();
        $allPatterns = [];

        foreach ($characterIds as $characterId) {
            /** @var int $characterId */
            $character = Character::find($characterId, ['*']);
            if ($character) {
                $allPatterns[$characterId] = $this->careerAnalyticsService->getComprehensiveAnalytics($character);
            }
        }

        $trainingFocusDistribution = [];
        $raceWinRates = [];
        $phaseEfficiency = [];

        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;
            if ($sessions->isNotEmpty()) {
                $typeCounts = $sessions->groupBy('training_type')->map->count();
                $dominantType = $typeCounts->sortDesc()->keys()->first();
                $trainingFocusDistribution[$dominantType] = ($trainingFocusDistribution[$dominantType] ?? 0) + 1;
            }

            $races = $career->races;
            if ($races->isNotEmpty()) {
                $wins = $races->where('won_race', true)->count();
                $raceWinRates[] = $races->count() > 0 ? round(($wins / $races->count()) * 100, 1) : 0;
            }

            if ($sessions->isNotEmpty()) {
                foreach (['junior', 'classic', 'senior'] as $phase) {
                    $phaseSessions = $sessions->where('career_phase', $phase);
                    if ($phaseSessions->isNotEmpty()) {
                        $totalGains = $phaseSessions->sum(fn ($s) => ($s->speed_gain ?? 0) + ($s->stamina_gain ?? 0) + ($s->power_gain ?? 0) + ($s->guts_gain ?? 0) + ($s->wit_gain ?? 0));
                        $phaseEfficiency[$phase][] = round($totalGains / $phaseSessions->count(), 2);
                    }
                }
            }
        }

        $avgPhaseEfficiency = [];
        foreach ($phaseEfficiency as $phase => $values) {
            $avgPhaseEfficiency[$phase] = round(array_sum($values) / count($values), 2);
        }

        $successFactors = [];
        $avgWinRate = ! empty($raceWinRates) ? round(array_sum($raceWinRates) / count($raceWinRates), 1) : 0;
        if ($avgWinRate > 50) {
            $successFactors[] = 'consistent_race_wins';
        }
        if (! empty($trainingFocusDistribution)) {
            arsort($trainingFocusDistribution);
            $topFocus = array_key_first($trainingFocusDistribution);
            $successFactors[] = "focused_{$topFocus}_training";
        }
        if (isset($avgPhaseEfficiency['junior']) && $avgPhaseEfficiency['junior'] > 10) {
            $successFactors[] = 'strong_early_stat_building';
        }
        if (isset($avgPhaseEfficiency['senior']) && $avgPhaseEfficiency['senior'] > 15) {
            $successFactors[] = 'efficient_late_game_training';
        }

        return response()->json([
            'data' => [
                'common_strategies' => [
                    'training_focus_distribution' => $trainingFocusDistribution,
                    'dominant_training_type' => ! empty($trainingFocusDistribution) ? array_key_first($trainingFocusDistribution) : null,
                    'average_race_win_rate' => $avgWinRate,
                ],
                'phase_efficiency' => $avgPhaseEfficiency,
                'success_factors' => $successFactors,
                'per_character_analytics' => $allPatterns,
                'careers_analyzed' => $careers->count(),
            ],
        ]);
    }

    /**
     * Get recommendations based on career patterns.
     *
     * Combines analytics-based insights from CareerAnalyticsService with
     * AI-powered recommendations from TrainingAdvisoryService.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'career_ids' => ['required', 'array', 'min:1'],
            'career_ids.*' => ['required', 'integer', 'exists:ucp_careers,id'],
        ]);

        $careers = Career::with(['character', 'trainingSessions', 'races'])
            ->whereIn('id', $validated['career_ids'])
            ->get();

        /** @var Career $career */
        foreach ($careers as $career) {
            $this->verifyCareerOwnership($career);
        }

        $trainingRecommendations = [];
        $raceRecommendations = [];
        $skillRecommendations = [];
        $aiRecommendations = [];

        $characterIds = $careers->pluck('character_id')->unique();

        foreach ($characterIds as $characterId) {
            /** @var int $characterId */
            $character = Character::find($characterId, ['*']);
            if (! $character) {
                continue;
            }

            $analytics = $this->careerAnalyticsService->getComprehensiveAnalytics($character);

            $analyticsRecs = $analytics['recommendations'] ?? [];
            if (is_array($analyticsRecs) && ! empty($analyticsRecs)) {
                $trainingRecommendations = array_merge($trainingRecommendations, $analyticsRecs);
            }

            $efficiency = is_array($analytics['training_effectiveness'] ?? null) ? $analytics['training_effectiveness'] : [];
            $improvementSuggestions = is_array($efficiency['improvement_suggestions'] ?? null) ? $efficiency['improvement_suggestions'] : [];
            if (! empty($improvementSuggestions)) {
                $trainingRecommendations = array_merge($trainingRecommendations, $improvementSuggestions);
            }

            $goalCompletion = $analytics['goal_completion'] ?? [];
            $timelineAnalysis = \is_array($goalCompletion['timeline_analysis'] ?? null) ? $goalCompletion['timeline_analysis'] : [];
            $atRiskGoals = \is_array($timelineAnalysis['at_risk_goals'] ?? null) ? $timelineAnalysis['at_risk_goals'] : [];
            foreach ($atRiskGoals as $goal) {
                $goalName = is_array($goal) && \is_string($goal['name'] ?? null) ? $goal['name'] : 'Unknown';
                $trainingRecommendations[] = "Prioritize training for at-risk goal: {$goalName}";
            }
        }

        foreach ($careers as $career) {
            $this->collectRaceRecommendations($career, $raceRecommendations);
            $this->collectSkillRecommendations($career, $skillRecommendations);
            $this->collectAIRecommendations($career, $aiRecommendations);
        }

        $trainingRecommendations = array_values(array_unique($trainingRecommendations));
        $raceRecommendations = array_values(array_unique($raceRecommendations));
        $skillRecommendations = array_values(array_unique($skillRecommendations));

        return response()->json([
            'data' => [
                'training_recommendations' => \array_slice($trainingRecommendations, 0, 5),
                'race_recommendations' => \array_slice($raceRecommendations, 0, 5),
                'skill_recommendations' => \array_slice($skillRecommendations, 0, 5),
                'ai_recommendations' => \array_slice($aiRecommendations, 0, 5),
                'careers_analyzed' => $careers->count(),
            ],
        ]);
    }

    /**
     * Collect race-specific recommendations for a career.
     *
     * @param  array<string>  $raceRecommendations
     */
    private function collectRaceRecommendations(Career $career, array &$raceRecommendations): void
    {
        $races = $career->races;
        if ($races->isNotEmpty()) {
            $wins = $races->where('won_race', true)->count();
            $total = $races->count();
            $winRate = $total > 0 ? round(($wins / $total) * 100, 1) : 0;

            if ($winRate < 50) {
                $raceRecommendations[] = 'Win rate is below 50%. Consider entering lower-grade races to build momentum.';
            }

            $g1Races = $races->where('race_grade', 'G1');
            if ($g1Races->isNotEmpty() && $g1Races->where('won_race', true)->isEmpty()) {
                $raceRecommendations[] = 'No G1 wins yet. Ensure stats meet G1 requirements before entering.';
            }
        } else {
            $raceRecommendations[] = 'No races recorded. Enter races to earn SP and fans.';
        }
    }

    /**
     * Collect skill-specific recommendations for a career.
     *
     * @param  array<string>  $skillRecommendations
     */
    private function collectSkillRecommendations(Career $career, array &$skillRecommendations): void
    {
        $sessions = $career->trainingSessions;
        if ($sessions->isNotEmpty()) {
            $spGained = $career->races->sum('sp_reward');
            if ($spGained < 500 && ($career->current_turn ?? 0) > 20) {
                $skillRecommendations[] = 'SP earnings are low. Participate in more races to fund skill acquisitions.';
            }
        }
    }

    /**
     * Collect AI-powered recommendations from TrainingAdvisoryService.
     *
     * @param  array<array<string, mixed>>  $aiRecommendations
     */
    private function collectAIRecommendations(Career $career, array &$aiRecommendations): void
    {
        if ($career->status !== 'active') {
            return;
        }

        $character = $career->character;
        if (! $character) {
            return;
        }

        try {
            $context = $this->buildTrainingContext($career, $character);
            $recommendations = $this->trainingAdvisoryService->getTrainingRecommendations($context);

            foreach ($recommendations as $recommendation) {
                $aiRecommendations[] = $recommendation->toArray();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to get AI recommendations for career', [
                'career_id' => $career->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build a TrainingContext from a Career and Character for the advisory service.
     */
    private function buildTrainingContext(Career $career, Character $character): TrainingContext
    {
        $currentStats = $character->current_stats ?? [];
        $stats = CharacterStats::fromArray([
            'speed' => $currentStats['speed'] ?? 0,
            'stamina' => $currentStats['stamina'] ?? 0,
            'power' => $currentStats['power'] ?? 0,
            'guts' => $currentStats['guts'] ?? 0,
            'wisdom' => $currentStats['wit'] ?? $currentStats['wisdom'] ?? 0,
        ]);

        $moodValue = $character->mood_status ?? 'normal';
        $mood = Mood::tryFrom($moodValue) ?? Mood::NORMAL;

        $turnNumber = $career->current_turn ?? 1;
        $phase = CareerPhase::fromTurn($turnNumber);

        $deckData = $career->support_deck;
        $deck = is_array($deckData) && ! empty($deckData) ? SupportCardDeck::fromArray($deckData) : SupportCardDeck::empty();

        /** @var array<array{id: int, distance: string, turn: int}> $upcomingRaces */
        $upcomingRaces = $career->races()
            ->where('turn_number', '>=', $turnNumber)
            ->orderBy('turn_number')
            ->limit(5)
            ->get(['id', 'distance_category', 'turn_number'])
            ->map(fn (Race $race) => [
                'id' => intval($race->id),
                'distance' => (string) ($race->distance_category ?? 'intermediate'),
                'turn' => intval($race->turn_number),
            ])
            ->toArray();

        return new TrainingContext(
            turnNumber: $turnNumber,
            phase: $phase,
            stats: $stats,
            spAvailable: $character->total_sp_available ?? 0,
            energy: $character->energy_level ?? 100,
            mood: $mood,
            acquiredSkills: [],
            skillHints: [],
            deck: $deck,
            facilityLevels: [
                'speed' => 1,
                'stamina' => 1,
                'power' => 1,
                'guts' => 1,
                'wisdom' => 1,
            ],
            upcomingRaces: $upcomingRaces,
            scenario: $career->scenario_type,
            storageMode: 'account',
            careerRunId: $career->id,
        );
    }
}
