<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Career Import/Export Controller
 *
 * Handles JSON export/import for Career data backup and sharing.
 *
 * Requirements: Task 5.3
 */
class CareerExportController extends Controller
{
    /**
     * Export career data as JSON
     */
    public function export(Request $request, Character $character): JsonResponse
    {
        $this->authorize('view', $character);

        // Load all related career data
        $character->load([
            'careers.trainingSessions',
            'careers.races',
            'skills',
            'factors',
            'aptitudes',
            'supportCards.supportCard',
        ]);

        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'character' => [
                'name' => $character->name,
                'scenario_type' => $character->scenario_type,
                'current_stats' => $character->current_stats,
                'stat_priorities' => $character->stat_priorities,
                'goals' => $character->goals,
            ],
            'careers' => $character->careers->map(function (Career $career) {
                return [
                    'status' => $career->status,
                    'final_stats' => [
                        'speed' => $career->final_speed,
                        'stamina' => $career->final_stamina,
                        'power' => $career->final_power,
                        'guts' => $career->final_guts,
                        'wit' => $career->final_wit,
                        'sp' => $career->final_sp,
                    ],
                    'started_at' => $career->started_at?->toIso8601String(),
                    'completed_at' => $career->completed_at?->toIso8601String(),
                    'training_sessions' => $career->trainingSessions->map(fn ($s) => [
                        'training_type' => $s->training_type,
                        'stat_gains' => $s->stat_gains,
                        'turn_number' => $s->turn_number,
                    ])->toArray(),
                    'races' => $career->races->map(fn ($r) => [
                        'race_name' => $r->race_name,
                        'position' => $r->finish_position,
                        'distance' => $r->distance_meters,
                    ])->toArray(),
                ];
            })->toArray(),
            'skills' => $character->skills->map(fn ($s) => [
                'name' => $s->name,
                'skill_type' => $s->skill_type,
                'is_evolution' => $s->is_evolution,
            ])->toArray(),
            'support_deck' => $character->supportCards->map(fn ($dc) => [
                'card_name' => $dc->supportCard?->name,
                'limit_break' => $dc->limit_break_level,
                'friendship' => $dc->friendship_level,
            ])->toArray(),
        ];

        return response()->json([
            'success' => true,
            'data' => $exportData,
        ]);
    }

    /**
     * Import career data from JSON
     */
    public function import(Request $request, Character $character): JsonResponse
    {
        $this->authorize('update', $character);

        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.version' => 'required|string',
            'data.character' => 'required|array',
            'data.careers' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid import data format',
                'errors' => $validator->errors(),
            ], 422);
        }

        $importData = $request->input('data');
        if (! is_array($importData)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid import data format',
            ], 422);
        }

        try {
            DB::transaction(function () use ($character, $importData) {
                // Update character stats
                if (isset($importData['character']) && is_array($importData['character'])) {
                    $charData = $importData['character'];

                    if (isset($charData['current_stats']) && is_array($charData['current_stats'])) {
                        $character->current_stats = $charData['current_stats'];
                    }

                    if (isset($charData['stat_priorities']) && is_array($charData['stat_priorities'])) {
                        $character->stat_priorities = $charData['stat_priorities'];
                    }

                    if (isset($charData['goals']) && is_array($charData['goals'])) {
                        $character->goals = $charData['goals'];
                    }

                    $character->save();
                }

                // Import careers
                if (isset($importData['careers']) && is_array($importData['careers'])) {
                    foreach ($importData['careers'] as $careerData) {
                        if (! is_array($careerData)) {
                            continue;
                        }

                        $finalStats = [];
                        if (isset($careerData['final_stats']) && is_array($careerData['final_stats'])) {
                            $finalStats = $careerData['final_stats'];
                        }

                        $career = Career::create([
                            'character_id' => $character->id,
                            'user_id' => $character->user_id,
                            'career_name' => $careerData['career_name'] ?? $character->name,
                            'scenario_type' => $careerData['scenario_type'] ?? $character->scenario_type,
                            'status' => $careerData['status'] ?? 'completed',
                            'current_turn' => isset($careerData['current_turn']) ? (int) $careerData['current_turn'] : 1,
                            'current_phase' => $careerData['current_phase'] ?? 'junior',
                            'started_at' => $careerData['started_at'] ?? null,
                            'completed_at' => $careerData['completed_at'] ?? null,
                            'final_speed' => isset($finalStats['speed']) ? (int) $finalStats['speed'] : null,
                            'final_stamina' => isset($finalStats['stamina']) ? (int) $finalStats['stamina'] : null,
                            'final_power' => isset($finalStats['power']) ? (int) $finalStats['power'] : null,
                            'final_guts' => isset($finalStats['guts']) ? (int) $finalStats['guts'] : null,
                            'final_wit' => isset($finalStats['wit']) ? (int) $finalStats['wit'] : null,
                            'final_sp' => isset($finalStats['sp']) ? (int) $finalStats['sp'] : null,
                        ]);

                        // Import training sessions
                        if (isset($careerData['training_sessions']) && is_array($careerData['training_sessions'])) {
                            foreach ($careerData['training_sessions'] as $sessionData) {
                                if (! is_array($sessionData)) {
                                    continue;
                                }

                                $statGains = [];
                                if (isset($sessionData['stat_gains']) && is_array($sessionData['stat_gains'])) {
                                    $statGains = $sessionData['stat_gains'];
                                }

                                $career->trainingSessions()->create([
                                    'character_id' => $character->id,
                                    'training_type' => $sessionData['training_type'] ?? 'speed',
                                    'speed_gain' => isset($statGains['speed']) ? (int) $statGains['speed'] : 0,
                                    'stamina_gain' => isset($statGains['stamina']) ? (int) $statGains['stamina'] : 0,
                                    'power_gain' => isset($statGains['power']) ? (int) $statGains['power'] : 0,
                                    'guts_gain' => isset($statGains['guts']) ? (int) $statGains['guts'] : 0,
                                    'wit_gain' => isset($statGains['wit']) ? (int) $statGains['wit'] : 0,
                                    'sp_gain' => isset($statGains['sp']) ? (int) $statGains['sp'] : 0,
                                    'turn_number' => isset($sessionData['turn_number']) ? (int) $sessionData['turn_number'] : 1,
                                    'career_phase' => $sessionData['career_phase'] ?? 'junior',
                                ]);
                            }
                        }
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Career data imported successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download career as JSON file
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request, Character $character)
    {
        $this->authorize('view', $character);

        $exportResponse = $this->export($request, $character);
        $payload = $exportResponse->getContent();
        $payload = $payload === false ? '' : $payload;
        $exportData = json_decode($payload, true);

        $filename = sprintf(
            'career_%s_%s.json',
            str_replace(' ', '_', $character->name),
            now()->format('Y-m-d')
        );

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData['data'] ?? $exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}
