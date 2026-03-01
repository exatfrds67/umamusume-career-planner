<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GameRace;
use App\Models\Race;
use Illuminate\Http\Request;

class RaceController extends Controller
{
    /**
     * Display the race catalog and recent race history.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $catalogQuery = GameRace::query()
            ->orderByRaw("CASE grade WHEN 'G1' THEN 1 WHEN 'G2' THEN 2 WHEN 'G3' THEN 3 WHEN 'OP' THEN 4 WHEN 'Pre-OP' THEN 5 WHEN 'Debut' THEN 6 ELSE 7 END")
            ->orderBy('year_in_scenario');

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

        $catalog = $catalogQuery->get();

        $recentResults = Race::query()
            ->with('character')
            ->when(auth()->check(), static fn ($q) => $q->whereHas('character', static fn ($cq) => $cq->whereHas('careers', static fn ($cr) => $cr->where('user_id', auth()->id()))))
            ->latest()
            ->limit(10)
            ->get();

        return view('races.index', [
            'catalog' => $catalog,
            'recentResults' => $recentResults,
            'grades' => ['G1', 'G2', 'G3', 'OP', 'Pre-OP', 'Debut'],
            'phases' => ['junior', 'classic', 'senior'],
            'surfaces' => ['turf', 'dirt'],
            'distances' => ['sprint', 'mile', 'medium', 'long', 'super_long'],
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
}
