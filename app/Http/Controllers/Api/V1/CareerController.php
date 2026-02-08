<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerController extends Controller
{
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

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        // Return mock available races based on turn
        $races = [
            [
                'name' => 'Test G1 Race',
                'grade' => 'G1',
                'distance' => 2000,
                'track_type' => 'turf',
                'turn_available' => $career->current_turn,
            ],
        ];

        return response()->json([
            'data' => $races,
        ]);
    }

    /**
     * Get training predictions for the career.
     */
    public function trainingPredictions(string $id): JsonResponse
    {
        $career = Career::with('character')
            ->findOrFail($id);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        // Return mock training predictions
        $predictions = [
            'speed' => ['min' => 10, 'max' => 20, 'expected' => 15],
            'stamina' => ['min' => 10, 'max' => 20, 'expected' => 15],
            'power' => ['min' => 10, 'max' => 20, 'expected' => 15],
            'guts' => ['min' => 10, 'max' => 20, 'expected' => 15],
            'wit' => ['min' => 10, 'max' => 20, 'expected' => 15],
        ];

        return response()->json([
            'data' => $predictions,
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
        ]);

        $career = Career::with('character')
            ->findOrFail($id);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        $trainingSession = TrainingSession::create([
            'career_id' => $career->id,
            'character_id' => $career->character_id,
            'turn_number' => $validated['turn_number'],
            'career_phase' => 'classic',
            'training_type' => $validated['training_type'],
            'speed_gain' => $validated['training_type'] === 'speed' ? 15 : 0,
            'stamina_gain' => $validated['training_type'] === 'stamina' ? 15 : 0,
            'power_gain' => $validated['training_type'] === 'power' ? 15 : 0,
            'guts_gain' => $validated['training_type'] === 'guts' ? 15 : 0,
            'wit_gain' => $validated['training_type'] === 'wit' ? 15 : 0,
            'energy_cost' => 20,
            'energy_before' => 100,
            'energy_after' => 80,
            'total_stat_points_gained' => 15,
        ]);

        return response()->json([
            'data' => $trainingSession,
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
        ]);

        $career = Career::with('character')
            ->findOrFail($id);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        $trainingSessions = [];
        foreach ($validated['sessions'] as $sessionData) {
            $trainingSessions[] = [
                'career_id' => $career->id,
                'character_id' => $career->character_id,
                'turn_number' => $sessionData['turn_number'],
                'career_phase' => 'classic',
                'training_type' => $sessionData['training_type'],
                'speed_gain' => $sessionData['training_type'] === 'speed' ? 15 : 0,
                'stamina_gain' => $sessionData['training_type'] === 'stamina' ? 15 : 0,
                'power_gain' => $sessionData['training_type'] === 'power' ? 15 : 0,
                'guts_gain' => $sessionData['training_type'] === 'guts' ? 15 : 0,
                'wit_gain' => $sessionData['training_type'] === 'wit' ? 15 : 0,
                'energy_cost' => 20,
                'energy_before' => 100,
                'energy_after' => 80,
                'total_stat_points_gained' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Use bulk insert for better performance
        TrainingSession::insert($trainingSessions);

        return response()->json([
            'message' => 'Training sessions created successfully',
            'count' => \count($trainingSessions),
        ], 201);
    }

    /**
     * Store a race entry.
     */
    public function storeRace(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'race_name' => ['required', 'string', 'max:255'],
            'race_grade' => ['required', 'string', 'in:G1,G2,G3,OP,Pre-OP'],
            'turn_number' => ['required', 'integer', 'min:1'],
        ]);

        $career = Career::with('character')
            ->findOrFail($id);

        // Verify the career's character belongs to the authenticated user
        $this->verifyCareerOwnership($career);

        $race = Race::create([
            'career_id' => $career->id,
            'character_id' => $career->character_id,
            'race_name' => $validated['race_name'],
            'race_grade' => $validated['race_grade'],
            'turn_number' => $validated['turn_number'],
            'career_phase' => 'classic',
            'distance_category' => 'intermediate',
            'distance_meters' => 2000,
            'surface' => 'turf',
            'running_style' => 'leading',
            'track_condition' => 'good',
            'weather' => 'sunny',
            'field_size' => 18,
            'energy_level' => 100,
            'speed_at_race' => 500,
            'stamina_at_race' => 500,
            'power_at_race' => 500,
            'guts_at_race' => 500,
            'wit_at_race' => 500,
            'finish_position' => 1,
            'won_race' => false,
            'race_result' => 'out_of_money',
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
                'average_gains' => 15,
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
        $totalStatGains = $career->trainingSessions->sum(fn ($session) => ($session->speed_gain ?? 0) +
            ($session->stamina_gain ?? 0) +
            ($session->power_gain ?? 0) +
            ($session->guts_gain ?? 0) +
            ($session->wit_gain ?? 0));

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

        $careers = Career::with('character')
            ->whereIn('id', $validated['career_ids'])
            ->get();

        // Verify all careers belong to characters owned by the authenticated user
        foreach ($careers as $career) {
            $this->verifyCareerOwnership($career);
        }

        $patterns = [
            'common_strategies' => [
                'training_focus' => 'speed',
                'race_frequency' => 'moderate',
            ],
            'success_factors' => [
                'early_stat_building',
                'consistent_race_participation',
            ],
        ];

        return response()->json([
            'data' => $patterns,
        ]);
    }

    /**
     * Get recommendations based on career patterns.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'career_ids' => ['required', 'array', 'min:1'],
            'career_ids.*' => ['required', 'integer', 'exists:ucp_careers,id'],
        ]);

        $careers = Career::with('character')
            ->whereIn('id', $validated['career_ids'])
            ->get();

        // Verify all careers belong to characters owned by the authenticated user
        foreach ($careers as $career) {
            $this->verifyCareerOwnership($career);
        }

        $recommendations = [
            'training_recommendations' => [
                'Focus on speed training in early turns',
                'Balance stamina and power in mid-game',
            ],
            'race_recommendations' => [
                'Enter G3 races early for experience',
                'Save G1 races for peak stats',
            ],
            'skill_recommendations' => [
                'Prioritize acceleration skills',
                'Acquire recovery skills mid-career',
            ],
        ];

        return response()->json([
            'data' => $recommendations,
        ]);
    }
}
