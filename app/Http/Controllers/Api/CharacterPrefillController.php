<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalAPI\UmapyoiApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Character Prefill Controller
 *
 * Handles fetching character data from external APIs for prefilling character creation forms
 */
class CharacterPrefillController extends Controller
{
    public function __construct(
        protected UmapyoiApiClient $umapyoiClient
    ) {}

    /**
     * Get character data for prefilling
     *
     * GET /api/characters/prefill/{externalId}
     */
    public function getPrefillData(string $externalId): JsonResponse
    {
        try {
            $result = $this->umapyoiClient->getCharacter($externalId);

            if (! $result['success'] || ! $result['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Character not found in external database',
                ], 404);
            }

            $character = $result['data'];

            // Transform to prefill format
            $prefillData = $this->transformToPrefillFormat($character);

            return response()->json([
                'success' => true,
                'data' => $prefillData,
                'source' => $result['source'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch character prefill data', [
                'external_id' => $externalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch character data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search characters for prefill
     *
     * GET /api/characters/prefill/search?q=special+week
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('q', '');

            $result = $this->umapyoiClient->getCharacters();

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch characters',
                ], 500);
            }

            $characters = $result['data'];

            // Filter by search query
            if ($query) {
                $characters = array_filter($characters, function ($char) use ($query) {
                    $nameEn = strtolower($char['name_en'] ?? '');
                    $nameJp = strtolower($char['name_jp'] ?? '');
                    $searchTerm = strtolower($query);

                    return str_contains($nameEn, $searchTerm) || str_contains($nameJp, $searchTerm);
                });
            }

            // Transform to simplified format
            $simplified = array_map(function ($char) {
                return [
                    'id' => $char['id'],
                    'name' => $char['name_en'] ?? $char['name_jp'] ?? 'Unknown',
                    'name_en' => $char['name_en'] ?? '',
                    'name_jp' => $char['name_jp'] ?? '',
                    'image' => $char['thumb_img'] ?? null,
                    'category' => $char['category_label_en'] ?? null,
                    'color' => $char['color_main'] ?? '#3B82F6',
                ];
            }, array_values($characters));

            return response()->json([
                'success' => true,
                'data' => $simplified,
                'total' => count($simplified),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to search characters', [
                'query' => $request->input('q'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to search characters',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transform external character data to prefill format
     */
    protected function transformToPrefillFormat(array $character): array
    {
        return [
            'external_id' => $character['id'] ?? null,
            'name' => $character['name_en'] ?? $character['name_jp'] ?? '',
            'name_en' => $character['name_en'] ?? '',
            'name_jp' => $character['name_jp'] ?? '',
            'image_url' => $character['thumb_img'] ?? null,
            'category' => $character['category_label_en'] ?? null,
            'color' => $character['color_main'] ?? '#3B82F6',
            'stats' => [
                'speed' => $character['base_stats']['speed'] ?? 0,
                'stamina' => $character['base_stats']['stamina'] ?? 0,
                'power' => $character['base_stats']['power'] ?? 0,
                'guts' => $character['base_stats']['guts'] ?? 0,
                'wit' => $character['base_stats']['wisdom'] ?? 0,
            ],
            'aptitudes' => [
                'distance' => [
                    'sprint' => $character['aptitudes']['turf_short'] ?? 'G',
                    'mile' => $character['aptitudes']['turf_mile'] ?? 'G',
                    'medium' => $character['aptitudes']['turf_medium'] ?? 'G',
                    'long' => $character['aptitudes']['turf_long'] ?? 'G',
                ],
                'surface' => [
                    'turf' => $this->getAverageTurfAptitude($character['aptitudes'] ?? []),
                    'dirt' => $this->getAverageDirtAptitude($character['aptitudes'] ?? []),
                ],
                'style' => [
                    'front_runner' => $character['aptitudes']['runner'] ?? 'G',
                    'pace_chaser' => $character['aptitudes']['leader'] ?? 'G',
                    'late_surger' => $character['aptitudes']['betweener'] ?? 'G',
                    'end_closer' => $character['aptitudes']['chaser'] ?? 'G',
                ],
            ],
            'metadata' => [
                'source' => 'umapyoi.net',
                'fetched_at' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Get average turf aptitude
     */
    protected function getAverageTurfAptitude(array $aptitudes): string
    {
        $turfAptitudes = [
            $aptitudes['turf_short'] ?? 'G',
            $aptitudes['turf_mile'] ?? 'G',
            $aptitudes['turf_medium'] ?? 'G',
            $aptitudes['turf_long'] ?? 'G',
        ];

        return $this->calculateAverageGrade($turfAptitudes);
    }

    /**
     * Get average dirt aptitude
     */
    protected function getAverageDirtAptitude(array $aptitudes): string
    {
        $dirtAptitudes = [
            $aptitudes['dirt_short'] ?? 'G',
            $aptitudes['dirt_mile'] ?? 'G',
            $aptitudes['dirt_medium'] ?? 'G',
            $aptitudes['dirt_long'] ?? 'G',
        ];

        return $this->calculateAverageGrade($dirtAptitudes);
    }

    /**
     * Calculate average grade from multiple grades
     */
    protected function calculateAverageGrade(array $grades): string
    {
        $gradeValues = [
            'G' => 0,
            'F' => 1,
            'E' => 2,
            'D' => 3,
            'C' => 4,
            'B' => 5,
            'A' => 6,
            'S' => 7,
            'SS' => 8,
        ];

        $reverseGrades = array_flip($gradeValues);

        $total = 0;
        $count = 0;

        foreach ($grades as $grade) {
            if (isset($gradeValues[$grade])) {
                $total += $gradeValues[$grade];
                $count++;
            }
        }

        if ($count === 0) {
            return 'G';
        }

        $average = (int) round($total / $count);

        return $reverseGrades[$average] ?? 'G';
    }
}
