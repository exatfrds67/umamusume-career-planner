<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCharacterRequest;
use App\Http\Requests\UpdateCharacterRequest;
use App\Http\Resources\CharacterRosterResource;
use App\Models\Aptitude;
use App\Models\CareerPlan;
use App\Models\Character;
use App\Models\Factor;
use App\Models\GameCharacter;
use App\Services\AvatarProcessingService;
use App\Services\CharacterGameDataResolver;
use App\Services\CharacterStateService;
use App\Services\FactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function __construct(
        protected CharacterStateService $characterStateService,
        protected FactorService $factorService,
        protected CharacterGameDataResolver $characterGameDataResolver,
        protected AvatarProcessingService $avatarProcessingService
    ) {}

    /**
     * Display a listing of characters with filtering and search
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $scenario = (string) $request->query('scenario', '');
        $status = (string) $request->query('status', '');
        $sort = (string) $request->query('sort', 'updated_at');
        $perPage = max(1, min((int) $request->query('per_page', 50), 100));
        $requestedPage = max(1, (int) $request->query('page', 1));

        $characterQuery = Character::query()
            ->with(['gameCharacter.goalRaces', 'currentCareer'])
            ->where(function ($query): void {
                $query->where('user_id', Auth::id())
                    ->orWhere('is_seeded', true);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($scenario !== '', fn ($query) => $query->where('scenario_type', $scenario))
            ->when($status !== '', fn ($query) => $query->where('status', $status));

        $characters = $characterQuery
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->get();

        $groupedCharacters = $characters
            ->groupBy(fn (Character $character): string => $this->canonicalGroupKey($character));

        $normalizedCharacters = $groupedCharacters
            ->map(function ($variantGroup) {
                /** @var \Illuminate\Support\Collection<int, Character> $variantGroup */
                $sortedVariants = $variantGroup
                    ->sortByDesc(fn (Character $character) => $character->updated_at?->getTimestamp() ?? 0)
                    ->values();

                /** @var Character $defaultVariant */
                $defaultVariant = $sortedVariants->first();

                return CharacterRosterResource::make([
                    'canonical_name' => $defaultVariant->name,
                    'default_character' => $defaultVariant,
                    'variants' => $sortedVariants,
                ])->resolve();
            })
            ->values();

        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $normalizedCharacters */
        $sortedCharacters = $this->sortGroupedCharacters($normalizedCharacters, $sort);
        $totalGroups = $sortedCharacters->count();
        $lastPage = max(1, (int) ceil($totalGroups / $perPage));
        $currentPage = min($requestedPage, $lastPage);
        $offset = ($currentPage - 1) * $perPage;

        $currentItems = $sortedCharacters->slice($offset, $perPage)->values();

        $characters = new LengthAwarePaginator(
            $currentItems,
            $totalGroups,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        $groupedCharacters = $currentItems
            ->groupBy('group_letter')
            ->sortKeys();

        $activeFilters = [
            'search' => $search,
            'scenario' => $scenario,
            'status' => $status,
            'sort' => $sort,
            'per_page' => $perPage,
        ];

        // Aggregate counts for the stats widget (all visible characters, ignoring active filters)
        $scenarioCounts = Character::query()
            ->where(function ($q): void {
                $q->where('user_id', Auth::id())->orWhere('is_seeded', true);
            })
            ->whereNotNull('scenario_type')
            ->where('scenario_type', '!=', '')
            ->selectRaw('scenario_type, COUNT(*) as cnt')
            ->groupBy('scenario_type')
            ->pluck('cnt', 'scenario_type');

        $statusCounts = Character::query()
            ->where(function ($q): void {
                $q->where('user_id', Auth::id())->orWhere('is_seeded', true);
            })
            ->whereNotNull('status')
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return view('characters.index', [
            'characters' => $characters,
            'groupedCharacters' => $groupedCharacters,
            'activeFilters' => $activeFilters,
            'scenarioCounts' => $scenarioCounts,
            'statusCounts' => $statusCounts,
        ]);
    }

    private function canonicalGroupKey(Character $character): string
    {
        if ($character->game_character_id !== null) {
            return 'game:'.$character->game_character_id;
        }

        $normalizedName = preg_replace('/[^a-z0-9]/i', '', mb_strtolower($character->name)) ?? '';

        return 'name:'.$normalizedName;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $groupedCharacters
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function sortGroupedCharacters(Collection $groupedCharacters, string $sort): Collection
    {
        $sorted = $groupedCharacters->sort(function (array $left, array $right) use ($sort): int {
            if (($left['is_pinned'] ?? false) !== ($right['is_pinned'] ?? false)) {
                return ($left['is_pinned'] ?? false) ? -1 : 1;
            }

            return match ($sort) {
                'name' => strcmp($this->safeString($left['name'] ?? null), $this->safeString($right['name'] ?? null)),
                'created_at' => strcmp($this->safeString($right['created_at'] ?? null), $this->safeString($left['created_at'] ?? null)),
                default => strcmp($this->safeString($right['updated_at'] ?? null), $this->safeString($left['updated_at'] ?? null)),
            };
        });

        return $sorted->values();
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
                    'maxTurns' => $character->getMaxTurns(),
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

    private function safeString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * Show the form for creating a new character
     */
    public function create(): View
    {
        $trainees = GameCharacter::query()
            ->orderBy('name_en', 'asc')
            ->get(['*'])
            ->map(function (GameCharacter $gc): array {
                $avatarUrl = $this->resolveGameCharacterAvatarUrl($gc);

                return [
                    'id' => $gc->id,
                    'name' => $gc->name_en,
                    'title' => $gc->title,
                    'rarity' => 'SSR',
                    'surface' => 'Turf',
                    'distance' => ucfirst($gc->primary_distance ?? 'medium'),
                    'style' => ucfirst($gc->preferred_style ?? 'leader'),
                    'strategy' => ucfirst($gc->preferred_style ?? 'leader'),
                    'aptitudes' => [],
                    'avatar_url' => $avatarUrl,
                    'image' => $avatarUrl,
                    'image_path' => $gc->image_path,
                    'stats' => [
                        'speed' => 0,
                        'stamina' => 0,
                        'power' => 0,
                        'guts' => 0,
                        'wisdom' => 0,
                    ],
                    'growth' => [
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

    protected function resolveGameCharacterAvatarUrl(GameCharacter $gameCharacter): ?string
    {
        return $this->characterGameDataResolver->resolveAvatarUrlForGameCharacter($gameCharacter);
    }

    /**
     * Store a newly created character in storage
     */
    public function store(StoreCharacterRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $selectedAvatarUrl = $request->input('avatar_url');
            $hasCustomAvatarUpload = $selectedAvatarUrl === 'custom_upload' && $request->hasFile('avatar_upload');
            $persistedAvatarUrl = $selectedAvatarUrl === 'custom_upload' ? null : $selectedAvatarUrl;

            // Create the character with all required JSON fields
            $traineeId = $request->input('trainee_id');
            $character = Character::create([
                'user_id' => Auth::id(),
                'name' => $request->input('name'),
                'title' => $request->input('title'),
                'avatar_url' => $persistedAvatarUrl,
                'image_x' => $request->float('image_x', 0.0),
                'image_y' => $request->float('image_y', 0.0),
                'image_zoom' => $request->float('image_zoom', 1.0),
                'image_rotation' => $request->integer('image_rotation', 0),
                'image_flip_h' => $request->boolean('image_flip_h'),
                'scenario_type' => $request->input('scenario_type'),
                'game_character_id' => $traineeId !== null ? $request->integer('trainee_id') : null,
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

            $character->careers()->create([
                'user_id' => Auth::id(),
                'star_level' => 3,
                'career_name' => $character->name.' Career',
                'scenario_type' => $character->scenario_type,
                'status' => 'active',
                'current_turn' => 1,
                'current_phase' => 'junior',
                'started_at' => now()->toDateString(),
            ]);

            // Create aptitude records
            $aptitudes = $request->input('aptitudes');
            if (\is_array($aptitudes) && $this->isValidAptitudesArray($aptitudes)) {
                /** @var array<string, array<string, string>> $validAptitudes */
                $validAptitudes = $aptitudes;
                $this->createAptitudes($character, $validAptitudes);
            }

            DB::commit();

            if ($hasCustomAvatarUpload) {
                $uploadedAvatarPath = $request->file('avatar_upload')->store(
                    "avatars/uploads/{$character->id}",
                    'public',
                );

                if (! is_string($uploadedAvatarPath) || $uploadedAvatarPath === '') {
                    throw new \RuntimeException('Failed to store uploaded avatar.');
                }

                $character->update([
                    'avatar_url' => '/storage/'.ltrim($uploadedAvatarPath, '/'),
                ]);
            }

            // Process avatar (outside transaction — non-critical)
            $avatarUrl = $character->avatar_url;
            if ($avatarUrl) {
                $processed = $this->avatarProcessingService->processAvatar(
                    $avatarUrl,
                    [
                        'image_x' => $character->image_x,
                        'image_y' => $character->image_y,
                        'image_zoom' => $character->image_zoom,
                        'image_rotation' => $character->image_rotation,
                        'image_flip_h' => $character->image_flip_h,
                    ],
                    $character->id,
                );

                if ($processed['square'] || $processed['circular']) {
                    $character->update(array_filter([
                        'avatar_processed' => $processed['square'],
                        'avatar_circular' => $processed['circular'],
                    ]));
                }
            }

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

        session(['current_character_id' => $character->id]);

        $character->load([
            'aptitudes',
            'factors',
            'supportCards.supportCard',
            'gameCharacter.goalRaces',
        ]);

        $aiTip = $this->getAiTip($character);
        $latestCareerPlan = null;

        if (Schema::hasTable((new CareerPlan)->getTable())) {
            $latestCareerPlan = CareerPlan::query()
                ->where('user_id', (int) Auth::id())
                ->where('character_id', $character->id)
                ->latest('updated_at')
                ->first();
        }

        return view('characters.show', compact('character', 'aiTip', 'latestCareerPlan'));
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
            $factor->fill([
                'factor_name' => $request->input('factor_name'),
                'source_character_name' => $request->input('source_character_name'),
            ]);
            $factor->save();

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
            $factor->fill([
                'is_active' => ! $factor->is_active,
            ]);
            $factor->save();

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
            Factor::destroy($factor->id);

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

    /**
     * Display synergy build analysis for a character.
     */
    public function synergy(Character $character): View
    {
        $this->authorize('view', $character);

        $character->load(['aptitudes', 'skillAcquisitions.skill', 'careers.parentCharacters.inheritanceEvents', 'supportCards.supportCard']);

        return view('characters.synergy', compact('character'));
    }
}
