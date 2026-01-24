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
     * @return array<string, mixed>
     */
    public function transform(string $dataType, mixed $data): array
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
     * @param  array<mixed, mixed>  $character
     * @return array<string, mixed>
     */
    protected function transformCharacter(array $character): array
    {
        $aptitudes = isset($character['aptitudes']) && is_array($character['aptitudes']) ? $character['aptitudes'] : [];

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
            'aptitudes' => $this->transformAptitudes($aptitudes),
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
     * @param  array<mixed, mixed>  $characters
     * @return array<int, array<string, mixed>>
     */
    protected function transformCharacters(array $characters): array
    {
        return array_map(
            fn ($character) => $this->transformCharacter(is_array($character) ? $character : []),
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
                $value = $aptitudes[$key] ?? null;
                $transformed[$key] = $this->normalizeAptitude(is_string($value) ? $value : null);
            }
        }

        foreach (['runner', 'leader', 'betweener', 'chaser'] as $style) {
            $value = $aptitudes[$style] ?? null;
            $transformed[$style] = $this->normalizeAptitude(is_string($value) ? $value : null);
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
     * @param  array<mixed, mixed>  $card
     * @return array<string, mixed>
     */
    protected function transformSupportCard(array $card): array
    {
        $rarityValue = $card['rarity'] ?? null;

        return [
            'id' => $card['id'] ?? null,
            'name' => $card['name'] ?? '',
            'rarity' => $this->normalizeRarity(is_string($rarityValue) ? $rarityValue : null),
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
     * @param  array<mixed, mixed>  $cards
     * @return array<int, array<string, mixed>>
     */
    protected function transformSupportCards(array $cards): array
    {
        return array_map(
            fn ($card) => $this->transformSupportCard(is_array($card) ? $card : []),
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
     * @param  array<mixed, mixed>  $skill
     * @return array<string, mixed>
     */
    protected function transformSkill(array $skill): array
    {
        $rarityValue = $skill['rarity'] ?? null;

        return [
            'id' => $skill['id'] ?? null,
            'name' => $skill['name'] ?? '',
            'description' => $skill['description'] ?? '',
            'effect' => $skill['effect'] ?? '',
            'type' => $skill['type'] ?? 'normal',
            'rarity' => $this->normalizeSkillRarity(is_string($rarityValue) ? $rarityValue : null),
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
     * @param  array<mixed, mixed>  $skills
     * @return array<int, array<string, mixed>>
     */
    protected function transformSkills(array $skills): array
    {
        return array_map(
            fn ($skill) => $this->transformSkill(is_array($skill) ? $skill : []),
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
     * @param  array<mixed, mixed>  $news
     * @return array<int, array<string, mixed>>
     */
    protected function transformNews(array $news): array
    {
        return array_map(function (mixed $item): array {
            if (! is_array($item)) {
                return [
                    'id' => null,
                    'title' => '',
                    'content' => '',
                    'published_at' => null,
                    'category' => 'general',
                    'metadata' => [
                        'source' => 'umapyoi',
                        'transformed_at' => now()->toISOString(),
                    ],
                ];
            }

            $publishedAt = $item['published_at'] ?? null;

            return [
                'id' => $item['id'] ?? null,
                'title' => $item['title'] ?? '',
                'content' => $item['content'] ?? '',
                'published_at' => $this->normalizeDate(is_string($publishedAt) ? $publishedAt : null),
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

        $existingMetadata = isset($data['metadata']) && is_array($data['metadata']) ? $data['metadata'] : [];
        $data['metadata'] = array_merge($existingMetadata, $metadata);

        return $data;
    }

    /**
     * Batch transform multiple items
     *
     * @param  array<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    public function batchTransform(array $items, string $dataType): array
    {
        return array_map(
            fn ($item) => $this->transform($dataType, $item),
            $items
        );
    }
}
