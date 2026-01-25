<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCharacterRequest;
use App\Http\Requests\UpdateCharacterRequest;
use App\Models\Aptitude;
use App\Models\Character;
use App\Services\CharacterStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function __construct(
        protected CharacterStateService $characterStateService
    ) {}

    /**
     * Display a listing of characters with filtering and search
     */
    public function index(Request $request): View
    {
        $query = Character::query()->with(['aptitudes']);

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            if (is_string($searchTerm)) {
                $query->where('name', 'like', '%'.$searchTerm.'%');
            }
        }

        // Filter by scenario type
        if ($request->filled('scenario')) {
            $scenario = $request->input('scenario');
            if (is_string($scenario)) {
                $query->where('scenario_type', $scenario);
            }
        }

        // Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $allowedSorts = ['name', 'created_at', 'scenario_type', 'updated_at'];
        if (is_string($sortField) && in_array($sortField, $allowedSorts) && is_string($sortDirection)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $characters = $query->paginate(12);

        return view('characters.index', compact('characters'));
    }

    /**
     * Show the form for creating a new character
     */
    public function create(): View
    {
        return view('characters.create');
    }

    /**
     * Store a newly created character in storage
     */
    public function store(StoreCharacterRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Create the character with all required JSON fields
            $character = Character::create([
                'user_id' => Auth::id(),
                'name' => $request->input('name'),
                'avatar_url' => $request->input('avatar_url'),
                'scenario_type' => $request->input('scenario_type'),
                'career_stage' => 'junior',
                'current_turn' => 1,
                'current_stats' => $request->input('stats'),
                'stat_priorities' => [],
                'stat_breakpoints' => [],
                'energy_level' => 100,
                'mood_status' => 'normal',
                'conditions' => [],
                'days_until_race' => null,
                'goals' => [],
                'race_schedule' => [],
                'training_plan' => [],
                'growth_rates' => [],
                'inherited_factors' => [],
                'legacy_parents' => [],
                'team_composition' => [],
                'facility_levels' => [],
                'spirit_burst_data' => [],
                'status' => 'active',
                'completion_data' => [],
            ]);

            // Create aptitude records
            $aptitudes = $request->input('aptitudes');
            if (is_array($aptitudes) && $this->isValidAptitudesArray($aptitudes)) {
                /** @var array<string, array<string, string>> $validAptitudes */
                $validAptitudes = $aptitudes;
                $this->createAptitudes($character, $validAptitudes);
            }

            DB::commit();

            return redirect()
                ->route('characters.show', $character)
                ->with('success', 'Character created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the actual error for debugging
            Log::error('Character creation failed: '.$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create character. Please try again.');
        }
    }

    /**
     * Display the specified character with comprehensive details
     */
    public function show(Character $character): View
    {
        $character->load([
            'aptitudes',
            'factors',
            'supportCards.supportCard',
        ]);

        $aiTip = $this->getAiTip($character);

        return view('characters.show', compact('character', 'aiTip'));
    }

    /**
     * Generate a contextual AI tip for the character
     */
    private function getAiTip(Character $character): string
    {
        $raceSchedule = $character->race_schedule ?? [];
        /** @var array{grade?: string}|null $nextRace */
        $nextRace = $raceSchedule[0] ?? null;
        $raceGrade = $nextRace['grade'] ?? 'G1';

        return "Based on recent races for {$character->name}, you should focus on increasing Stamina for the upcoming {$raceGrade} race.";
    }

    /**
     * Show the form for editing the specified character
     */
    public function edit(Character $character): View
    {
        // Ensure user owns this character
        if ($character->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $character->load('aptitudes');

        return view('characters.edit', compact('character'));
    }

    /**
     * Update the specified character in storage
     */
    public function update(UpdateCharacterRequest $request, Character $character): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Prepare update data
            $updateData = [];

            // Update basic fields if provided
            if ($request->has('name')) {
                $updateData['name'] = $request->input('name');
            }

            if ($request->has('scenario_type')) {
                $updateData['scenario_type'] = $request->input('scenario_type');
            }

            if ($request->has('career_stage')) {
                $updateData['career_stage'] = $request->input('career_stage');
            }

            if ($request->has('current_turn')) {
                $updateData['current_turn'] = $request->input('current_turn');
            }

            // Update stats if provided
            if ($request->has('stats')) {
                $updateData['current_stats'] = $request->input('stats');
            }

            // Update energy and mood
            if ($request->has('energy_level')) {
                $updateData['energy_level'] = $request->input('energy_level');
            }

            if ($request->has('mood_status')) {
                $updateData['mood_status'] = $request->input('mood_status');
            }

            // Update goals
            if ($request->has('goals')) {
                $currentGoals = $character->goals ?? [];
                $newGoals = $request->input('goals');

                // Merge with existing goals to preserve other goal data
                if (is_array($newGoals)) {
                    $updateData['goals'] = array_merge($currentGoals, $newGoals);
                }
            }

            // Update growth rates
            if ($request->has('growth_rates')) {
                $updateData['growth_rates'] = $request->input('growth_rates');
            }

            // Update facility levels (Unity Cup)
            if ($request->has('facility_levels')) {
                $updateData['facility_levels'] = $request->input('facility_levels');
            }

            // Perform the update
            $character->fill($updateData);
            $character->save();

            DB::commit();

            return redirect()
                ->route('characters.show', $character)
                ->with('success', 'Character updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Character update failed: '.$e->getMessage(), [
                'character_id' => $character->id,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update character. Please try again.');
        }
    }

    /**
     * Remove the specified character from storage
     */
    public function destroy(Character $character): RedirectResponse
    {
        // Ensure user owns this character
        if ($character->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Delete the character (cascade deletion will handle related records)
            Character::destroy($character->id);

            return redirect()
                ->route('characters.index')
                ->with('success', 'Character deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Character deletion failed: '.$e->getMessage(), [
                'character_id' => $character->id,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to delete character. Please try again.');
        }
    }

    /**
     * Create aptitude records for a character
     *
     * @param  array<string, array<string, string>>  $aptitudes
     */
    private function createAptitudes(Character $character, array $aptitudes): void
    {
        // Distance aptitudes
        foreach ($aptitudes['distance'] as $distanceType => $grade) {
            Aptitude::create([
                'character_id' => $character->id,
                'distance_type' => $distanceType,
                'surface_type' => null,
                'running_style' => null,
                'grade' => $grade,
            ]);
        }

        // Surface aptitudes
        foreach ($aptitudes['surface'] as $surfaceType => $grade) {
            Aptitude::create([
                'character_id' => $character->id,
                'distance_type' => null,
                'surface_type' => $surfaceType,
                'running_style' => null,
                'grade' => $grade,
            ]);
        }

        // Running style aptitudes
        foreach ($aptitudes['style'] as $runningStyle => $grade) {
            Aptitude::create([
                'character_id' => $character->id,
                'distance_type' => null,
                'surface_type' => null,
                'running_style' => $runningStyle,
                'grade' => $grade,
            ]);
        }
    }

    /**
     * Validate aptitudes array structure
     *
     * @param  array<mixed, mixed>  $aptitudes
     */
    private function isValidAptitudesArray(array $aptitudes): bool
    {
        if (! isset($aptitudes['distance'], $aptitudes['surface'], $aptitudes['style'])) {
            return false;
        }

        return is_array($aptitudes['distance'])
            && is_array($aptitudes['surface'])
            && is_array($aptitudes['style']);
    }

    /**
     * Rest action to recover energy
     */
    public function rest(Character $character): RedirectResponse
    {
        if ($character->user_id !== Auth::id()) {
            abort(403);
        }

        $result = $this->characterStateService->rest($character);

        $message = "Rested! Recovered {$result['recovered']} energy.";
        if ($result['result'] === 'great_success') {
            $message = "Great Rest! Recovered {$result['recovered']} energy and mood improved!";
        } elseif ($result['result'] === 'failure') {
            $message = "Bad Rest... Only recovered {$result['recovered']} energy and mood worsened.";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Advance to the next turn
     */
    public function nextTurn(Character $character): RedirectResponse
    {
        if ($character->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if character has enough energy (optional logic, for now just consume a bit or none)
        // $this->characterStateService->consumeEnergy($character, 5);

        $result = $this->characterStateService->progressTurn($character);

        $message = "Turn advanced to {$result['turn']}.";
        if ($result['stage_changed']) {
            $message .= ' Advanced to '.ucfirst($result['stage']).' stage!';
        }

        return redirect()->back()->with('success', $message);
    }
}
