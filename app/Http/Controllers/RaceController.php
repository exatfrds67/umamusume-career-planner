<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RaceController extends Controller
{
    /**
     * Display a listing of the races.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = \App\Models\Race::query();

        // Filter by grade
        if ($request->filled('grade')) {
            $query->where('race_grade', $request->input('grade'));
        }

        // Filter by distance category
        if ($request->filled('distance')) {
            $query->where('distance_category', $request->input('distance'));
        }

        // Filter by surface
        if ($request->filled('surface')) {
            $query->where('surface', $request->input('surface'));
        }

        // Filter by character if provided
        if ($request->filled('character_id')) {
            $query->where('character_id', $request->input('character_id'));
        }

        $races = $query->latest('turn_number')->paginate(20)->withQueryString();

        return view('races.index', compact('races'));
    }

    /**
     * Display the specified race.
     *
     * @return \Illuminate\View\View
     */
    public function show(\App\Models\Race $race)
    {
        $race->load(['character']);

        return view('races.show', compact('race'));
    }
}
