<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\RivalRaceOutcome;
use App\Enums\TsDistance;
use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Services\AgendaService;
use App\Services\TrackblazerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackblazerController extends Controller
{
    public function __construct(
        protected TrackblazerService $trackblazerService,
        protected AgendaService $agendaService
    ) {}

    public function logRival(Request $request, Career $career): JsonResponse
    {
        $validated = $request->validate([
            'race_id' => 'required|integer',
            'distance' => 'required|string',
            'rival_uma' => 'required|string',
            'outcome' => 'required|string',
            'skill_hints' => 'nullable|array',
        ]);

        $log = $this->trackblazerService->logRivalEncounter(
            $career,
            $validated['race_id'],
            TsDistance::from($validated['distance']),
            $validated['rival_uma'],
            RivalRaceOutcome::from($validated['outcome']),
            $validated['skill_hints'] ?? []
        );

        return response()->json(['success' => true, 'data' => $log]);
    }

    public function reserveAgenda(Request $request, Career $career): JsonResponse
    {
        $validated = $request->validate([
            'reserved_coins' => 'required|integer|min:0',
            'reserved_hammers' => 'required|integer|min:0',
            'target_ts_distance' => 'nullable|string',
        ]);

        $reservation = $this->agendaService->reserveItems(
            $career,
            $validated['reserved_coins'],
            $validated['reserved_hammers'],
            $validated['target_ts_distance']
        );

        return response()->json(['success' => true, 'data' => $reservation]);
    }

    public function exportAgenda(Request $request, Career $career): JsonResponse
    {
        $validated = $request->validate([
            'csv_data' => 'required|string',
        ]);

        $export = $this->agendaService->exportAgenda(
            $career,
            $validated['csv_data']
        );

        return response()->json(['success' => true, 'data' => $export]);
    }
}
