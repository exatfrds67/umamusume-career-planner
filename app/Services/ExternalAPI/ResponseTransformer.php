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
            'name_en' => $character['name_en'] ?? '',
            'name_jp' => $character['name_jp'] ?? '',
            'name' => $character['name_en'] ?? $character['name_jp'] ?? '',
            'title' => $character['title'] ?? null,
            'category_label_en' => $character['category_label_en'] ?? null,
            'category_label' => $character['category_label'] ?? null,
            'thumb_img' => $character['thumb_img'] ?? null,
            'color_main' => $character['color_main'] ?? null,
            'color_sub' => $character['color_sub'] ?? null,
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
        // API returns: id, chara_id, gametora, title_en
        // Map to internal format with name_en for display
        $rarityValue = $card['rarity'] ?? null;
        $titleEn = $card['title_en'] ?? '';
        $name = $card['name'] ?? $titleEn;
        $cardId = $card['id'] ?? 0;

        // Infer rarity from card ID if not provided
        // Card ID ranges (based on Uma Musume game structure):
        // 10001-10999: R cards
        // 20001-29999: SR cards
        // 30001-39999: SSR cards
        if ($rarityValue === null && is_numeric($cardId)) {
            $rarityValue = $this->inferRarityFromId((int) $cardId);
        }

        return [
            'id' => $cardId,
            'name' => $name,
            'name_en' => $titleEn,
            'name_jp' => $card['name_jp'] ?? '',
            'title_en' => $titleEn,
            'rarity' => $this->normalizeRarity(is_string($rarityValue) ? $rarityValue : null),
            'type' => $card['type'] ?? null,
            'character_id' => $card['chara_id'] ?? $card['character_id'] ?? null,
            'gametora' => $card['gametora'] ?? null,
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
     * Infer rarity from card ID based on Uma Musume card ID ranges
     */
    protected function inferRarityFromId(int $cardId): string
    {
        // Card ID ranges follow a pattern:
        // 10001-19999: R (Rare)
        // 20001-29999: SR (Super Rare)
        // 30001-39999: SSR (Super Super Rare)
        if ($cardId >= 30001 && $cardId <= 39999) {
            return 'SSR';
        } elseif ($cardId >= 20001 && $cardId <= 29999) {
            return 'SR';
        } else {
            return 'R';
        }
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
        $index = 0;

        return array_map(function (mixed $item) use (&$index): array {
            $index++;

            if (! is_array($item)) {
                return [
                    'id' => 'news_'.$index,
                    'title' => '',
                    'title_en' => '',
                    'title_jp' => '',
                    'content' => '',
                    'published_at' => null,
                    'category' => 'general',
                    'thumb_img' => null,
                    'metadata' => [
                        'source' => 'umapyoi',
                        'transformed_at' => now()->toISOString(),
                    ],
                ];
            }

            // API returns: message, message_english, post_at (Unix timestamp), label_name_en, image, article_image
            $titleEn = $item['message_english'] ?? '';
            $titleJp = $item['message'] ?? '';
            $content = $item['message_english'] ?? $item['message'] ?? '';
            $postAt = $item['post_at'] ?? null;

            // Convert Unix timestamp to ISO 8601 date
            $publishedAt = null;
            if ($postAt !== null && is_numeric($postAt)) {
                try {
                    $publishedAt = \Carbon\Carbon::createFromTimestamp((int) $postAt)->toISOString();
                } catch (\Exception $e) {
                    Log::warning('[ResponseTransformer] Failed to parse Unix timestamp', [
                        'timestamp' => $postAt,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Generate unique ID using announce_id or index
            $titleEnStr = \is_string($titleEn) ? $titleEn : '';
            $uniqueId = $item['announce_id'] ?? $item['id'] ?? 'news_'.md5($titleEnStr.(string) $index);

            // Get thumbnail image
            $thumbImg = $item['article_image'] ?? $item['image'] ?? null;
            if ($thumbImg === '') {
                $thumbImg = null;
            }

            return [
                'id' => $uniqueId,
                'title' => $titleEn ?: $titleJp,
                'title_en' => $titleEn,
                'title_jp' => $titleJp,
                'content' => $content,
                'published_at' => $publishedAt,
                'category' => $item['label_name_en'] ?? 'general',
                'thumb_img' => $thumbImg,
                'metadata' => [
                    'source' => 'umapyoi',
                    'transformed_at' => now()->toISOString(),
                    'original_id' => $item['id'] ?? null,
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
