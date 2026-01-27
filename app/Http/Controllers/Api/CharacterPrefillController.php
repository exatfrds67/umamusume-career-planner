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
            $queryInput = $request->input('q', '');
            $query = is_string($queryInput) ? $queryInput : '';

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
                    $nameEn = isset($char['name_en']) && is_string($char['name_en']) ? strtolower($char['name_en']) : '';
                    $nameJp = isset($char['name_jp']) && is_string($char['name_jp']) ? strtolower($char['name_jp']) : '';
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
     *
     * @param  array<string, mixed>  $character
     * @return array{external_id: mixed, name: string, name_en: string, name_jp: string, image_url: string|null, category: string|null, color: string, stats: array{speed: int, stamina: int, power: int, guts: int, wit: int}, aptitudes: array{distance: array{sprint: string, mile: string, medium: string, long: string}, surface: array{turf: string, dirt: string}, style: array{front_runner: string, pace_chaser: string, late_surger: string, end_closer: string}}, metadata: array{source: string, fetched_at: string}}
     */
    protected function transformToPrefillFormat(array $character): array
    {
        $baseStats = isset($character['base_stats']) && \is_array($character['base_stats']) ? $character['base_stats'] : [];
        $aptitudes = isset($character['aptitudes']) && \is_array($character['aptitudes']) ? $character['aptitudes'] : [];

        $nameEn = isset($character['name_en']) && \is_string($character['name_en']) ? $character['name_en'] : '';
        $nameJp = isset($character['name_jp']) && \is_string($character['name_jp']) ? $character['name_jp'] : '';
        $thumbImg = isset($character['thumb_img']) && \is_string($character['thumb_img']) ? $character['thumb_img'] : null;
        $categoryLabel = isset($character['category_label_en']) && \is_string($character['category_label_en']) ? $character['category_label_en'] : null;
        $colorMain = isset($character['color_main']) && \is_string($character['color_main']) ? $character['color_main'] : '#3B82F6';

        return [
            'external_id' => $character['id'] ?? null,
            'name' => $nameEn !== '' ? $nameEn : $nameJp,
            'name_en' => $nameEn,
            'name_jp' => $nameJp,
            'image_url' => $thumbImg,
            'category' => $categoryLabel,
            'color' => $colorMain,
            'stats' => [
                'speed' => isset($baseStats['speed']) && \is_numeric($baseStats['speed']) ? (int) $baseStats['speed'] : 0,
                'stamina' => isset($baseStats['stamina']) && \is_numeric($baseStats['stamina']) ? (int) $baseStats['stamina'] : 0,
                'power' => isset($baseStats['power']) && \is_numeric($baseStats['power']) ? (int) $baseStats['power'] : 0,
                'guts' => isset($baseStats['guts']) && \is_numeric($baseStats['guts']) ? (int) $baseStats['guts'] : 0,
                'wit' => isset($baseStats['wisdom']) && \is_numeric($baseStats['wisdom']) ? (int) $baseStats['wisdom'] : 0,
            ],
            'aptitudes' => [
                'distance' => [
                    'sprint' => isset($aptitudes['turf_short']) && \is_string($aptitudes['turf_short']) ? $aptitudes['turf_short'] : 'G',
                    'mile' => isset($aptitudes['turf_mile']) && \is_string($aptitudes['turf_mile']) ? $aptitudes['turf_mile'] : 'G',
                    'medium' => isset($aptitudes['turf_medium']) && \is_string($aptitudes['turf_medium']) ? $aptitudes['turf_medium'] : 'G',
                    'long' => isset($aptitudes['turf_long']) && \is_string($aptitudes['turf_long']) ? $aptitudes['turf_long'] : 'G',
                ],
                'surface' => [
                    'turf' => $this->getAverageTurfAptitude($aptitudes),
                    'dirt' => $this->getAverageDirtAptitude($aptitudes),
                ],
                'style' => [
                    'front_runner' => isset($aptitudes['runner']) && \is_string($aptitudes['runner']) ? $aptitudes['runner'] : 'G',
                    'pace_chaser' => isset($aptitudes['leader']) && \is_string($aptitudes['leader']) ? $aptitudes['leader'] : 'G',
                    'late_surger' => isset($aptitudes['betweener']) && \is_string($aptitudes['betweener']) ? $aptitudes['betweener'] : 'G',
                    'end_closer' => isset($aptitudes['chaser']) && \is_string($aptitudes['chaser']) ? $aptitudes['chaser'] : 'G',
                ],
            ],
            'metadata' => [
                'source' => 'umapyoi.net',
                'fetched_at' => now()->toISOString() ?? '',
            ],
        ];
    }

    /**
     * Get average turf aptitude
     *
     * @param  array<string, mixed>  $aptitudes
     */
    protected function getAverageTurfAptitude(array $aptitudes): string
    {
        $turfAptitudes = [
            is_string($aptitudes['turf_short'] ?? null) ? $aptitudes['turf_short'] : 'G',
            is_string($aptitudes['turf_mile'] ?? null) ? $aptitudes['turf_mile'] : 'G',
            is_string($aptitudes['turf_medium'] ?? null) ? $aptitudes['turf_medium'] : 'G',
            is_string($aptitudes['turf_long'] ?? null) ? $aptitudes['turf_long'] : 'G',
        ];

        return $this->calculateAverageGrade($turfAptitudes);
    }

    /**
     * Get average dirt aptitude
     *
     * @param  array<string, mixed>  $aptitudes
     */
    protected function getAverageDirtAptitude(array $aptitudes): string
    {
        $dirtAptitudes = [
            is_string($aptitudes['dirt_short'] ?? null) ? $aptitudes['dirt_short'] : 'G',
            is_string($aptitudes['dirt_mile'] ?? null) ? $aptitudes['dirt_mile'] : 'G',
            is_string($aptitudes['dirt_medium'] ?? null) ? $aptitudes['dirt_medium'] : 'G',
            is_string($aptitudes['dirt_long'] ?? null) ? $aptitudes['dirt_long'] : 'G',
        ];

        return $this->calculateAverageGrade($dirtAptitudes);
    }

    /**
     * Calculate average grade from multiple grades
     *
     * @param  list<string>  $grades
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
