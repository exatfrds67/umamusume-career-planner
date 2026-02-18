<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCharacterRequest;
use App\Http\Requests\UpdateCharacterRequest;
use App\Models\Aptitude;
use App\Models\Character;
use App\Models\ExternalData;
use App\Models\Factor;
use App\Services\CharacterStateService;
use App\Services\FactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function __construct(
        protected CharacterStateService $characterStateService,
        protected FactorService $factorService
    ) {}

    /**
     * Display a listing of characters with filtering and search
     */
    public function index(Request $request): View
    {
        // Load all characters for client-side filtering (instant search)
        $characters = Character::query()
            ->with(['aptitudes'])
            ->orderBy('is_pinned', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Add progress percentage to each character
        $characters->transform(function (Character $character): Character {
            // Calculate progress percentage
            $progress = $character->getProgressPercentage();
            // Add progress as a dynamic property for the view
            $character->setAttribute('progress', $progress);

            return $character;
        });

        return view('characters.index', compact('characters'));
    }

    /**
     * Select the active character for the session.
     */
    public function select(Request $request, Character $character): \Illuminate\Http\Response|RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $character);

        $request->session()->put('current_character_id', $character->id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'character' => [
                    'id' => $character->id,
                    'name' => $character->name,
                ],
                'topStatus' => [
                    'currentTurn' => $character->current_turn,
                    'maxTurns' => 78,
                    'spAvailable' => $character->available_sp ?? 0,
                    'storageMode' => 'account',
                    'energy' => $character->energy_level,
                    'mood' => $character->mood_status,
                    'careerStage' => $character->career_stage,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Selected character updated.');
    }

    /**
     * Show the form for creating a new character
     */
    public function create(): View
    {
        // Fetch trainee data from external_data table (type: 'trainee')
        $trainees = ExternalData::where('data_type', 'trainee')
            ->orWhere('data_type', 'character')
            ->get()
            ->map(function (ExternalData $item): array {
                /** @var array<string, mixed> $data */
                $data = $item->data_content ?? [];

                return [
                    'id' => $item->id,
                    'name' => \is_string($data['name'] ?? null) ? $data['name'] : 'Unknown',
                    'rarity' => \is_int($data['rarity'] ?? null) ? $data['rarity'] : 3,
                    'surface' => \is_string($data['surface'] ?? null) ? $data['surface'] : 'Turf',
                    'distance' => \is_string($data['distance'] ?? null) ? $data['distance'] : 'Medium',
                    'style' => \is_string($data['style'] ?? null) ? $data['style'] : 'Runner',
                    'aptitudes' => \is_array($data['aptitudes'] ?? null) ? $data['aptitudes'] : [],
                    'image' => \is_string($data['image'] ?? null) ? $data['image'] : null,
                    'stats' => \is_array($data['stats'] ?? null) ? $data['stats'] : [
                        'speed' => 0,
                        'stamina' => 0,
                        'power' => 0,
                        'guts' => 0,
                        'wisdom' => 0,
                    ],
                    'growth' => \is_array($data['growth'] ?? null) ? $data['growth'] : [
                        'speed' => 0,
                        'stamina' => 0,
                        'power' => 0,
                        'guts' => 0,
                        'wisdom' => 0,
                    ],
                ];
            })
            ->toArray();

        return view('characters.create', compact('trainees'));
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
                'is_seeded' => false, // User-created characters are not seeded
                'completion_data' => [],
            ]);

            // Create aptitude records
            $aptitudes = $request->input('aptitudes');
            if (\is_array($aptitudes) && $this->isValidAptitudesArray($aptitudes)) {
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
        // Use policy authorization (allows admins and owners)
        $this->authorize('view', $character);

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
        // Use policy authorization (allows admins and owners)
        $this->authorize('update', $character);

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
                if (\is_array($newGoals)) {
                    $updateData['goals'] = [...$currentGoals, ...$newGoals];
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
        // Use policy authorization (allows admins and owners)
        $this->authorize('delete', $character);

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

        return \is_array($aptitudes['distance'])
            && \is_array($aptitudes['surface'])
            && \is_array($aptitudes['style']);
    }

    /**
     * Toggle the pinned status of a character
     */
    public function togglePin(Character $character): RedirectResponse
    {
        $this->authorize('update', $character);

        $isPinned = $character->togglePin(Auth::id());

        $message = $isPinned
            ? 'Character pinned successfully!'
            : 'Character unpinned successfully!';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Rest action to recover energy
     */
    public function rest(Character $character): RedirectResponse
    {
        // Use policy authorization (allows admins and owners)
        $this->authorize('update', $character);

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
        // Use policy authorization (allows admins and owners)
        $this->authorize('update', $character);

        // Check if character has enough energy (optional logic, for now just consume a bit or none)
        // $this->characterStateService->consumeEnergy($character, 5);

        $result = $this->characterStateService->progressTurn($character);

        $message = "Turn advanced to {$result['turn']}.";
        if ($result['stage_changed']) {
            $message .= ' Advanced to '.ucfirst($result['stage']).' stage!';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Show factor management page for a character
     */
    public function manageFactors(Character $character): View
    {
        $this->authorize('update', $character);

        $character->load('factors');
        $factorsByType = $this->factorService->getFactorsByType($character);
        $factorCounts = $this->factorService->getFactorCountByStarLevel($character);

        return view('characters.factors.manage', compact('character', 'factorsByType', 'factorCounts'));
    }

    /**
     * Store new factors for a character
     */
    public function storeFactors(Request $request, Character $character): RedirectResponse
    {
        $this->authorize('update', $character);

        $request->validate([
            'factor_type' => 'required|in:blue_stats,red_aptitudes,green_unique_skills,white_normal_skills',
            'factor_name' => 'required|string|max:255',
            'star_level' => 'required|in:1_star,2_star,3_star',
            'stat_type' => 'nullable|string|in:speed,stamina,power,guts,wit',
            'aptitude_type' => 'nullable|string|in:sprint,mile,medium,long,turf,dirt,front_runner,pace_chaser,late_surger,end_closer',
            'unique_skill_name' => 'nullable|string|max:255',
            'normal_skill_name' => 'nullable|string|max:255',
            'source_parent' => 'required|in:main_parent_1,main_parent_2,grandparent_1,grandparent_2,grandparent_3,grandparent_4',
            'source_character_name' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $factorType = $request->input('factor_type');
            $starLevelInput = $request->input('star_level');
            $starLevel = \is_string($starLevelInput) ? $starLevelInput : '';
            $sourceParentInput = $request->input('source_parent');
            $sourceParent = \is_string($sourceParentInput) ? $sourceParentInput : '';
            $sourceCharacterName = $request->input('source_character_name');
            $sourceCharacterName = \is_string($sourceCharacterName) ? $sourceCharacterName : null;
            $factorNameInput = $request->input('factor_name');
            $factorName = \is_string($factorNameInput) ? $factorNameInput : null;

            switch ($factorType) {
                case 'blue_stats':
                    $statType = $request->input('stat_type');
                    if (! $statType || ! \is_string($statType)) {
                        throw new \InvalidArgumentException('Stat type is required for blue factors');
                    }
                    $this->factorService->createBlueFactor(
                        $character,
                        $statType,
                        $starLevel,
                        $sourceParent,
                        $sourceCharacterName,
                        $factorName
                    );
                    break;

                case 'red_aptitudes':
                    $aptitudeType = $request->input('aptitude_type');
                    if (! $aptitudeType || ! \is_string($aptitudeType)) {
                        throw new \InvalidArgumentException('Aptitude type is required for red factors');
                    }
                    $gradeImprovement = match ($starLevel) {
                        '1_star' => 1,
                        '2_star' => 2,
                        '3_star' => 3,
                        default => throw new \InvalidArgumentException('Invalid star level'),
                    };
                    $this->factorService->createRedFactor(
                        $character,
                        $aptitudeType,
                        $gradeImprovement,
                        $starLevel,
                        $sourceParent,
                        $sourceCharacterName,
                        $factorName
                    );
                    break;

                case 'green_unique_skills':
                    $skillName = $request->input('unique_skill_name');
                    if (! $skillName || ! \is_string($skillName)) {
                        throw new \InvalidArgumentException('Unique skill name is required for green factors');
                    }
                    $this->factorService->createGreenFactor(
                        $character,
                        $skillName,
                        [], // Skill effects can be added later
                        $sourceParent,
                        $sourceCharacterName
                    );
                    break;

                case 'white_normal_skills':
                    $skillName = $request->input('normal_skill_name');
                    if (! $skillName || ! \is_string($skillName)) {
                        throw new \InvalidArgumentException('Normal skill name is required for white factors');
                    }
                    $this->factorService->createWhiteFactor(
                        $character,
                        $skillName,
                        [], // Race bonuses can be added later
                        $starLevel,
                        $sourceParent,
                        $sourceCharacterName
                    );
                    break;
            }

            DB::commit();

            return redirect()
                ->route('characters.factors.manage', $character)
                ->with('success', 'Factor added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Factor creation failed: '.$e->getMessage(), [
                'character_id' => $character->id,
                'exception' => $e,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to add factor. Please try again.');
        }
    }

    /**
     * Update an existing factor
     */
    public function updateFactor(Request $request, Character $character, Factor $factor): RedirectResponse
    {
        $this->authorize('update', $character);

        if ($factor->character_id !== $character->id) {
            abort(404);
        }

        $request->validate([
            'factor_name' => 'required|string|max:255',
            'source_character_name' => 'nullable|string|max:255',
        ]);

        try {
            $factor->update([
                'factor_name' => $request->input('factor_name'),
                'source_character_name' => $request->input('source_character_name'),
            ]);

            return redirect()
                ->route('characters.factors.manage', $character)
                ->with('success', 'Factor updated successfully!');
        } catch (\Exception $e) {
            Log::error('Factor update failed: '.$e->getMessage(), [
                'factor_id' => $factor->id,
                'exception' => $e,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to update factor. Please try again.');
        }
    }

    /**
     * Toggle factor active status
     */
    public function toggleFactor(Character $character, Factor $factor): RedirectResponse
    {
        $this->authorize('update', $character);

        if ($factor->character_id !== $character->id) {
            abort(404);
        }

        try {
            $factor->update([
                'is_active' => ! $factor->is_active,
            ]);

            $status = $factor->is_active ? 'activated' : 'deactivated';

            return redirect()
                ->route('characters.factors.manage', $character)
                ->with('success', "Factor {$status} successfully!");
        } catch (\Exception $e) {
            Log::error('Factor toggle failed: '.$e->getMessage(), [
                'factor_id' => $factor->id,
                'exception' => $e,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to toggle factor. Please try again.');
        }
    }

    /**
     * Delete a factor
     */
    public function destroyFactor(Character $character, Factor $factor): RedirectResponse
    {
        $this->authorize('update', $character);

        if ($factor->character_id !== $character->id) {
            abort(404);
        }

        try {
            $factor->delete();

            return redirect()
                ->route('characters.factors.manage', $character)
                ->with('success', 'Factor deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Factor deletion failed: '.$e->getMessage(), [
                'factor_id' => $factor->id,
                'exception' => $e,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to delete factor. Please try again.');
        }
    }
}
