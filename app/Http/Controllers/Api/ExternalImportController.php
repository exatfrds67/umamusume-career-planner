<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportCardDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controller for importing external data (support cards, etc.)
 */
class ExternalImportController extends Controller
{
    /**
     * Import a support card from external source (umapyoi.net)
     */
    public function importSupportCard(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'external_id' => 'required|integer',
            'title_en' => 'nullable|string|max:255',
            'chara_id' => 'nullable|integer',
            'gametora' => 'nullable|string|max:255',
            'rarity' => 'nullable|string|in:R,SR,SSR',
            'image_url' => 'nullable|url|max:500',
            'source' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Check if card already exists by external_id
            $existingCard = SupportCardDefinition::where('external_source_id', (string) $data['external_id'])->first();

            if ($existingCard) {
                return response()->json([
                    'success' => true,
                    'message' => 'Support card already exists in your collection',
                    'data' => [
                        'id' => $existingCard->id,
                        'name' => $existingCard->name,
                        'already_imported' => true,
                    ],
                ]);
            }

            // Extract character name from gametora field
            $characterName = $this->extractCharacterName($data['gametora'] ?? '');

            // Create new support card definition
            $card = SupportCardDefinition::create([
                'name' => $data['title_en'] ?? 'Unknown Card #'.$data['external_id'],
                'internal_id' => 'ext_'.$data['external_id'],
                'external_source_id' => (string) $data['external_id'],
                'external_source' => $data['source'],
                'card_type' => $this->inferCardType($data['gametora'] ?? ''),
                'rarity' => $data['rarity'] ?? 'R',
                'character_name' => $characterName,
                'character_internal_id' => $data['chara_id'] ? (string) $data['chara_id'] : null,
                'artwork_url' => $data['image_url'] ?? null,
                'is_active' => true,
                'is_limited' => false,
                'card_metadata' => [
                    'source' => $data['source'],
                    'external_id' => $data['external_id'],
                    'gametora' => $data['gametora'] ?? null,
                    'imported_at' => now()->toIso8601String(),
                ],
            ]);

            Log::info('Support card imported from external source', [
                'card_id' => $card->id,
                'external_id' => $data['external_id'],
                'source' => $data['source'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Support card imported successfully!',
                'data' => [
                    'id' => $card->id,
                    'name' => $card->name,
                    'rarity' => $card->rarity,
                    'already_imported' => false,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to import support card', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to import support card: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract character name from gametora field
     * Format: "10001-special-week" -> "Special Week"
     */
    private function extractCharacterName(?string $gametora): ?string
    {
        if (empty($gametora)) {
            return null;
        }

        $parts = explode('-', $gametora);
        if (count($parts) < 2) {
            return $gametora;
        }

        // Remove the ID part and format the name
        $nameParts = array_slice($parts, 1);

        return implode(' ', array_map('ucfirst', $nameParts));
    }

    /**
     * Infer card type from gametora or other data
     * Default to 'speed' if unknown
     * Valid types: speed, stamina, power, guts, wit, friend
     */
    private function inferCardType(?string $gametora): string
    {
        // Valid card types in the database enum
        $typeMapping = [
            'speed' => 'speed',
            'stamina' => 'stamina',
            'power' => 'power',
            'guts' => 'guts',
            'wisdom' => 'wit',  // Map wisdom to wit
            'wit' => 'wit',
            'friend' => 'friend',
            'group' => 'friend',  // Map group to friend
        ];

        if (empty($gametora)) {
            return 'speed';
        }

        // Check if gametora contains any type hints
        $lowerGametora = strtolower($gametora);
        foreach ($typeMapping as $searchTerm => $cardType) {
            if (str_contains($lowerGametora, $searchTerm)) {
                return $cardType;
            }
        }

        // Try to infer from character name patterns
        // Some cards have type indicators in their names
        $typeIndicators = [
            'スピード' => 'speed',
            'スタミナ' => 'stamina',
            'パワー' => 'power',
            '根性' => 'guts',
            '賢さ' => 'wit',
            'フレンド' => 'friend',
            'グループ' => 'friend',
        ];

        foreach ($typeIndicators as $indicator => $cardType) {
            if (str_contains($lowerGametora, strtolower($indicator))) {
                return $cardType;
            }
        }

        return 'speed'; // Default
    }
}
