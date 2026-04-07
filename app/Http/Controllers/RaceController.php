<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameRace;
use App\Models\Race;
use App\Services\RaceExecutionService;
use Illuminate\Http\Request;

class RaceController extends Controller
{
    public function __construct(
        protected RaceExecutionService $raceService
    ) {}

    /**
     * Display the race catalog and recent race history.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $validSorts = ['date', 'grade', 'distance', 'fans'];
        $sort = in_array($request->input('sort'), $validSorts, true) ? $request->input('sort') : 'date';

        $gradeOrderSql = "CASE grade WHEN 'G1' THEN 1 WHEN 'G2' THEN 2 WHEN 'G3' THEN 3 WHEN 'OP' THEN 4 WHEN 'Pre-OP' THEN 5 WHEN 'Debut' THEN 6 ELSE 7 END";
        $seasonOrderSql = "CASE COALESCE(season, '') WHEN 'spring' THEN 1 WHEN 'summer' THEN 2 WHEN 'autumn' THEN 3 WHEN 'winter' THEN 4 ELSE 5 END";

        $catalogQuery = GameRace::query();

        if ($request->filled('grade')) {
            $catalogQuery->where('grade', '=', $request->input('grade'));
        }

        if ($request->filled('phase')) {
            $catalogQuery->where(static function ($q) use ($request): void {
                $q->where('phase', '=', $request->input('phase'))->orWhere('phase', '=', 'all');
            });
        }

        if ($request->filled('surface')) {
            $catalogQuery->where('surface', '=', $request->input('surface'));
        }

        if ($request->filled('distance')) {
            $catalogQuery->where('distance_category', '=', $request->input('distance'));
        }

        if ($request->filled('season')) {
            $catalogQuery->where('season', '=', $request->input('season'));
        }

        if ($request->filled('venue')) {
            $catalogQuery->where('venue', '=', $request->input('venue'));
        }

        $minFans = $request->integer('min_fans', 0);
        if ($minFans > 0) {
            $catalogQuery->where('fan_requirement', '>=', $minFans);
        }

        if ($sort === 'grade') {
            $catalogQuery
                ->orderByRaw($gradeOrderSql)
                ->orderByRaw('COALESCE(year_in_scenario, 9999)')
                ->orderByRaw($seasonOrderSql);
        } elseif ($sort === 'distance') {
            $catalogQuery
                ->orderBy('distance_meters')
                ->orderByRaw($gradeOrderSql);
        } elseif ($sort === 'fans') {
            $catalogQuery
                ->orderByRaw('(fan_requirement IS NULL OR fan_requirement = 0)')
                ->orderBy('fan_requirement', 'desc')
                ->orderByRaw($gradeOrderSql);
        } else {
            $catalogQuery
                ->orderByRaw('COALESCE(year_in_scenario, 9999)')
                ->orderByRaw($seasonOrderSql)
                ->orderByRaw($gradeOrderSql);
        }

        $catalog = $catalogQuery->paginate(50)->withQueryString();

        $activeFilters = [
            'grade' => $request->input('grade', ''),
            'phase' => $request->input('phase', ''),
            'surface' => $request->input('surface', ''),
            'distance' => $request->input('distance', ''),
            'season' => $request->input('season', ''),
            'venue' => $request->input('venue', ''),
            'min_fans' => $request->input('min_fans', ''),
            'sort' => $sort,
        ];

        $recentResults = Race::query()
            ->with('character')
            ->when(auth()->check(), static fn ($q) => $q->whereHas('character', static fn ($cq) => $cq->whereHas('careers', static fn ($cr) => $cr->where('user_id', auth()->id()))))
            ->latest()
            ->limit(10)
            ->get();

        $availableVenues = GameRace::query()
            ->whereNotNull('venue')
            ->distinct()
            ->orderBy('venue')
            ->pluck('venue');

        return view('races.index', [
            'catalog' => $catalog,
            'recentResults' => $recentResults,
            'activeFilters' => $activeFilters,
            'grades' => ['G1', 'G2', 'G3', 'OP', 'Pre-OP', 'Debut'],
            'phases' => ['junior', 'classic', 'senior'],
            'surfaces' => ['turf', 'dirt'],
            'distances' => ['sprint', 'mile', 'medium', 'long', 'super_long'],
            'availableSeasons' => ['spring', 'summer', 'autumn', 'winter'],
            'availableVenues' => $availableVenues,
            'fanThresholds' => [100, 200, 500, 1000],
            'sortOptions' => [
                'date' => 'Date (Default)',
                'grade' => 'Grade',
                'distance' => 'Distance',
                'fans' => 'Fan Requirement',
            ],
        ]);
    }

    /**
     * Display a single game race from the catalog.
     */
    public function show(string $race): \Illuminate\View\View
    {
        $gameRace = GameRace::query()->where('slug', '=', $race)->firstOrFail();

        return view('races.show', compact('gameRace'));
    }

    /**
     * Display the race planning calendar carousel.
     */
    public function calendar(): \Illuminate\View\View
    {
        $races = GameRace::query()
            ->orderByRaw("CASE grade WHEN 'G1' THEN 1 WHEN 'G2' THEN 2 WHEN 'G3' THEN 3 WHEN 'OP' THEN 4 WHEN 'Pre-OP' THEN 5 WHEN 'Debut' THEN 6 ELSE 7 END")
            ->orderBy('year_in_scenario')
            ->get()
            ->map(fn (GameRace $race): array => [
                'id' => $race->id,
                'name' => $race->name_en,
                'grade' => $race->grade,
                'surface' => $race->surface,
                'distance' => $race->distance_meters,
                'distanceCategory' => $race->distance_category,
                'phase' => $race->phase,
                'month' => $race->month_label,
                'year' => $race->year_in_scenario,
                'venue' => $race->venue,
                'fanRequirement' => $race->fan_requirement ?? 0,
                'fansReward' => $race->fans_reward ?? 0,
                'spReward' => $race->sp_reward ?? 0,
                'statRequirements' => $race->stat_requirements ?? [],
                'isUraFinale' => $race->is_ura_finale,
                'status' => 'upcoming',
            ]);

        return view('races.calendar', compact('races'));
    }

    /**
     * Display the race targeting and planning interface.
     */
    public function targets(Request $request): \Illuminate\View\View
    {
        $character = null;

        $races = GameRace::query()
            ->orderByRaw("CASE grade WHEN 'G1' THEN 1 WHEN 'G2' THEN 2 WHEN 'G3' THEN 3 WHEN 'OP' THEN 4 WHEN 'Pre-OP' THEN 5 WHEN 'Debut' THEN 6 ELSE 7 END")
            ->orderBy('year_in_scenario')
            ->get()
            ->map(fn (GameRace $race): array => [
                'id' => $race->id,
                'name' => $race->name_en,
                'grade' => $race->grade,
                'distance' => $race->distance_meters,
                'distanceCategory' => $race->distance_category,
                'type' => $race->surface,
                'phase' => $race->phase,
                'fanCount' => $race->fans_reward ?? 0,
                'fanRequirement' => $race->fan_requirement ?? 0,
                'spReward' => $race->sp_reward ?? 0,
                'statRequirements' => $race->stat_requirements ?? [],
                'year' => $race->year_in_scenario,
                'month' => $race->month_label,
                'isUraFinale' => $race->is_ura_finale,
            ]);

        return view('races.targets', compact('character', 'races'));
    }

    /**
     * Enter a character into a race and simulate the result.
     */
    public function enter(Character $character, GameRace $gameRace): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $character);

        $result = $this->raceService->enterRace($character, $gameRace);

        if (! ($result['success'] ?? false)) {
            return redirect()
                ->route('races.show', $gameRace->slug)
                ->with('error', $result['message'] ?? 'Failed to enter race.');
        }

        $raceResult = $result['result'] ?? [];
        $finishPosition = is_array($raceResult) ? ($raceResult['finish_position'] ?? null) : null;
        $position = is_int($finishPosition) ? (string) $finishPosition : '?';
        $won = is_array($raceResult) && ($raceResult['won_race'] ?? false);

        $message = $won
            ? "Won {$gameRace->name_en}! Finished 1st place!"
            : 'Finished '.$position."th in {$gameRace->name_en}.";

        if ($result['career_completed'] ?? false) {
            return redirect()
                ->route('characters.show', $character)
                ->with('success', "{$message} Career run completed!");
        }

        return redirect()
            ->route('training.index', $character)
            ->with($won ? 'success' : 'warning', $message);
    }
}
