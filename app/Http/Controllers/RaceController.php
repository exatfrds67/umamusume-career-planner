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
     * Display the race calendar with filtering and the Livewire race strategy panel.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $allowedSorts = ['date', 'grade', 'distance', 'fans'];
        $sort = in_array($request->query('sort', 'date'), $allowedSorts, true)
            ? $request->query('sort', 'date')
            : 'date';

        $activeFilters = [
            'grade' => $request->query('grade', ''),
            'season' => $request->query('season', ''),
            'surface' => $request->query('surface', ''),
            'distance' => $request->query('distance', ''),
            'venue' => $request->query('venue', ''),
            'min_fans' => $request->query('min_fans', ''),
            'sort' => $sort,
        ];

        $query = GameRace::query();

        if ($activeFilters['grade'] !== '') {
            $query->where('grade', $activeFilters['grade']);
        }
        if ($activeFilters['season'] !== '') {
            $query->where('season', $activeFilters['season']);
        }
        if ($activeFilters['surface'] !== '') {
            $query->where('surface', $activeFilters['surface']);
        }
        if ($activeFilters['distance'] !== '') {
            $query->where('distance_category', $activeFilters['distance']);
        }
        if ($activeFilters['venue'] !== '') {
            $query->where('venue', $activeFilters['venue']);
        }
        if ($activeFilters['min_fans'] !== '' && is_numeric($activeFilters['min_fans'])) {
            $query->where('fan_requirement', '>=', (int) $activeFilters['min_fans']);
        }

        $query = match ($sort) {
            'grade' => $query->orderByRaw("CASE grade WHEN 'G1' THEN 1 WHEN 'G2' THEN 2 WHEN 'G3' THEN 3 WHEN 'OP' THEN 4 WHEN 'Pre-OP' THEN 5 WHEN 'Debut' THEN 6 ELSE 7 END"),
            'distance' => $query->orderBy('distance_meters'),
            'fans' => $query->orderByDesc('fans_reward'),
            default => $query->orderBy('year_in_scenario')->orderBy('month_label'),
        };

        $catalog = $query->paginate(100)->withQueryString();

        $grades = GameRace::query()->distinct()->orderBy('grade')->pluck('grade');
        $availableSeasons = GameRace::query()->distinct()->whereNotNull('season')->orderBy('season')->pluck('season');
        $availableVenues = GameRace::query()->distinct()->whereNotNull('venue')->orderBy('venue')->pluck('venue');
        $fanThresholds = [500, 1000, 2000, 5000, 10000];
        $sortOptions = [
            'date' => 'Date (Default)',
            'grade' => 'Grade',
            'distance' => 'Distance',
            'fans' => 'Fan Reward',
        ];

        $recentResults = collect();
        if (auth()->check()) {
            $recentResults = Race::query()
                ->whereHas('character', fn ($q) => $q->where('user_id', auth()->id()))
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

        return view('races.index', compact(
            'catalog',
            'activeFilters',
            'grades',
            'availableSeasons',
            'availableVenues',
            'fanThresholds',
            'sortOptions',
            'recentResults'
        ));
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
