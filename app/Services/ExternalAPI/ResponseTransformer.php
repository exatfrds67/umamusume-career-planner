<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use Illuminate\Support\Facades\Log;

/**
 * Response Transformer for External API Responses
 *
 * Transforms external API responses to internal application format
 * with consistent structure and data normalization.
 *
 * Requirements: 14.1, 14.3, Task 1.2.3
 */
class ResponseTransformer
{
    /**
     * Transform API response to internal format
     *
     * @param  mixed  $data
     * @return array<string, mixed>
     */
    public function transform(string $dataType, $data): array
    {
        if (! is_array($data)) {
            Log::warning('[ResponseTransformer] Invalid data type for transformation', [
                'data_type' => $dataType,
                'actual_type' => gettype($data),
            ]);

            return [];
        }

        return match ($dataType) {
            'character' => $this->transformCharacter($data),
            'characters' => $this->transformCharacters($data),
            'support_card' => $this->transformSupportCard($data),
            'support_cards' => $this->transformSupportCards($data),
            'skill' => $this->transformSkill($data),
            'skills' => $this->transformSkills($data),
            'news' => $this->transformNews($data),
            default => $data,
        };
    }

    /**
     * Transform single character data
     *
     * @param  array<string, mixed>  $character
     * @return array<string, mixed>
     */
    protected function transformCharacter(array $character): array
    {
        return [
            'id' => $character['id'] ?? null,
            'name' => $character['name'] ?? '',
            'title' => $character['title'] ?? null,
            'rarity' => $character['rarity'] ?? null,
            'base_stats' => [
                'speed' => $character['speed'] ?? 0,
                'stamina' => $character['stamina'] ?? 0,
                'power' => $character['power'] ?? 0,
                'guts' => $character['guts'] ?? 0,
                'wisdom' => $character['wisdom'] ?? 0,
            ],
            'aptitudes' => $this->transformAptitudes($character['aptitudes'] ?? []),
            'skills' => $character['skills'] ?? [],
            'growth_rate' => $character['growth_rate'] ?? null,
            'metadata' => [
                'source' => 'umapyoi',
                'transformed_at' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Transform multiple characters
     *
     * @param  array<int, array<string, mixed>>  $characters
     * @return array<int, array<string, mixed>>
     */
    protected function transformCharacters(array $characters): array
    {
        return array_map(
            fn ($character) => $this->transformCharacter($character),
            $characters
        );
    }

    /**
     * Transform character aptitudes
     *
     * @param  array<string, mixed>  $aptitudes
     * @return array<string, mixed>
     */
    protected function transformAptitudes(array $aptitudes): array
    {
        $transformed = [];

        foreach (['turf', 'dirt'] as $surface) {
            foreach (['short', 'mile', 'medium', 'long'] as $distance) {
                $key = "{$surface}_{$distance}";
                $transformed[$key] = $this->normalizeAptitude($aptitudes[$key] ?? null);
            }
        }

        foreach (['runner', 'leader', 'betweener', 'chaser'] as $style) {
            $transformed[$style] = $this->normalizeAptitude($aptitudes[$style] ?? null);
        }

        return $transformed;
    }

    /**
     * Normalize aptitude value to standard format
     */
    protected function normalizeAptitude(?string $aptitude): string
    {
        if (! $aptitude) {
            return 'G';
        }

        // Convert various formats to standard (G, F, E, D, C, B, A, S, SS)
        $aptitude = strtoupper(trim($aptitude));

        $validAptitudes = ['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S', 'SS'];

        return in_array($aptitude, $validAptitudes, true) ? $aptitude : 'G';
    }

    /**
     * Transform single support card data
     *
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    protected function transformSupportCard(array $card): array
    {
        return [
            'id' => $card['id'] ?? null,
            'name' => $card['name'] ?? '',
            'rarity' => $this->normalizeRarity($card['rarity'] ?? null),
            'type' => $card['type'] ?? null,
            'character_id' => $card['character_id'] ?? null,
            'stats' => [
                'speed' => $card['speed_bonus'] ?? 0,
                'stamina' => $card['stamina_bonus'] ?? 0,
                'power' => $card['power_bonus'] ?? 0,
                'guts' => $card['guts_bonus'] ?? 0,
                'wisdom' => $card['wisdom_bonus'] ?? 0,
            ],
            'effects' => $card['effects'] ?? [],
            'skills' => $card['skills'] ?? [],
            'unique_effect' => $card['unique_effect'] ?? null,
            'friendship_bonus' => $card['friendship_bonus'] ?? 0,
            'metadata' => [
                'source' => 'umapyoi',
                'transformed_at' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Transform multiple support cards
     *
     * @param  array<int, array<string, mixed>>  $cards
     * @return array<int, array<string, mixed>>
     */
    protected function transformSupportCards(array $cards): array
    {
        return array_map(
            fn ($card) => $this->transformSupportCard($card),
            $cards
        );
    }

    /**
     * Normalize rarity value
     */
    protected function normalizeRarity(?string $rarity): string
    {
        if (! $rarity) {
            return 'R';
        }

        $rarity = strtoupper(trim($rarity));

        $validRarities = ['R', 'SR', 'SSR'];

        return in_array($rarity, $validRarities, true) ? $rarity : 'R';
    }

    /**
     * Transform single skill data
     *
     * @param  array<string, mixed>  $skill
     * @return array<string, mixed>
     */
    protected function transformSkill(array $skill): array
    {
        return [
            'id' => $skill['id'] ?? null,
            'name' => $skill['name'] ?? '',
            'description' => $skill['description'] ?? '',
            'effect' => $skill['effect'] ?? '',
            'type' => $skill['type'] ?? 'normal',
            'rarity' => $this->normalizeSkillRarity($skill['rarity'] ?? null),
            'conditions' => $skill['conditions'] ?? [],
            'metadata' => [
                'source' => 'umapyoi',
                'transformed_at' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Transform multiple skills
     *
     * @param  array<int, array<string, mixed>>  $skills
     * @return array<int, array<string, mixed>>
     */
    protected function transformSkills(array $skills): array
    {
        return array_map(
            fn ($skill) => $this->transformSkill($skill),
            $skills
        );
    }

    /**
     * Normalize skill rarity
     */
    protected function normalizeSkillRarity(?string $rarity): string
    {
        if (! $rarity) {
            return 'normal';
        }

        $rarity = strtolower(trim($rarity));

        $validRarities = ['normal', 'rare', 'unique'];

        return in_array($rarity, $validRarities, true) ? $rarity : 'normal';
    }

    /**
     * Transform news data
     *
     * @param  array<int, array<string, mixed>>  $news
     * @return array<int, array<string, mixed>>
     */
    protected function transformNews(array $news): array
    {
        return array_map(function ($item) {
            return [
                'id' => $item['id'] ?? null,
                'title' => $item['title'] ?? '',
                'content' => $item['content'] ?? '',
                'published_at' => $this->normalizeDate($item['published_at'] ?? null),
                'category' => $item['category'] ?? 'general',
                'metadata' => [
                    'source' => 'umapyoi',
                    'transformed_at' => now()->toISOString(),
                ],
            ];
        }, $news);
    }

    /**
     * Normalize date to ISO 8601 format
     */
    protected function normalizeDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->toISOString();
        } catch (\Exception $e) {
            Log::warning('[ResponseTransformer] Failed to parse date', [
                'date' => $date,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Add metadata to transformed data
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $additionalMetadata
     * @return array<string, mixed>
     */
    public function addMetadata(array $data, array $additionalMetadata = []): array
    {
        $metadata = array_merge([
            'transformed_at' => now()->toISOString(),
        ], $additionalMetadata);

        if (isset($data['metadata'])) {
            $data['metadata'] = array_merge($data['metadata'], $metadata);
        } else {
            $data['metadata'] = $metadata;
        }

        return $data;
    }

    /**
     * Batch transform multiple items
     *
     * @param  array<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    public function batchTransform(string $dataType, array $items): array
    {
        return array_map(
            fn ($item) => $this->transform($dataType, $item),
            $items
        );
    }
}
