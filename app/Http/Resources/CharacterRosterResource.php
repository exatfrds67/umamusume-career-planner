<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @mixin Character
 */
class CharacterRosterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Character) {
            return $this->mapVariant($this->resource, true);
        }

        /** @var array{default_character: Character, canonical_name?: string, variants: Collection<int, Character>|array<int, Character>} $grouped */
        $grouped = $this->resource;
        $defaultCharacter = $grouped['default_character'];
        $variantsCollection = $grouped['variants'] instanceof Collection
            ? $grouped['variants']
            : collect($grouped['variants']);

        $defaultVariant = $this->mapVariant($defaultCharacter, true);

        return [
            'id' => $defaultVariant['id'],
            'canonical_key' => $this->resolveCanonicalKey($defaultCharacter),
            'name' => $grouped['canonical_name'] ?? $defaultCharacter->name,
            'title' => $defaultVariant['title'],
            'status' => $defaultVariant['status'],
            'scenario_type' => $defaultVariant['scenario_type'],
            'is_pinned' => $defaultVariant['is_pinned'],
            'is_seeded' => $defaultVariant['is_seeded'],
            'group_letter' => $this->resolveGroupLetter((string) ($grouped['canonical_name'] ?? $defaultCharacter->name)),
            'progress' => $defaultVariant['progress'],
            'avatar_url' => $defaultVariant['avatar_url'],
            'avatar_processed' => $defaultVariant['avatar_processed'],
            'avatar_circular' => $defaultVariant['avatar_circular'],
            'avatar_fallback_url' => $defaultVariant['avatar_fallback_url'],
            'current_stats' => $defaultVariant['current_stats'],
            'goal_races' => $defaultVariant['goal_races'],
            'next_goal' => $defaultVariant['next_goal'],
            'remaining_goal_count' => $defaultVariant['remaining_goal_count'],
            'updated_at' => $defaultVariant['updated_at'],
            'created_at' => $defaultVariant['created_at'],
            'variant_count' => $variantsCollection->count(),
            'variants' => $variantsCollection
                ->map(fn (Character $variant): array => $this->mapVariant(
                    $variant,
                    $variant->id === $defaultCharacter->id,
                ))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapVariant(Character $character, bool $isDefault): array
    {
        $gameCharacter = $character->gameCharacter;
        $goalRaceCollection = $gameCharacter instanceof \App\Models\GameCharacter
            ? $gameCharacter->goalRaces
            : collect();

        $goalRaces = collect($goalRaceCollection)
            ->map(fn ($goalRace): array => GoalResource::make($goalRace)->resolve())
            ->sortBy('priority')
            ->unique(fn (array $goalRace): string => mb_strtolower((string) ($goalRace['name'] ?? '')))
            ->values();

        $nextGoal = $goalRaces->first();

        return [
            'id' => $character->id,
            'name' => $character->name,
            'title' => $character->title,
            'status' => $character->status,
            'scenario_type' => $character->scenario_type,
            'is_pinned' => (bool) $character->is_pinned,
            'is_seeded' => (bool) $character->is_seeded,
            'is_default' => $isDefault,
            'progress' => $character->getProgressPercentage(),
            'avatar_url' => $character->avatar_url,
            'avatar_processed' => $character->avatar_processed,
            'avatar_circular' => $character->avatar_circular,
            'avatar_fallback_url' => '/images/trainee_images/default.png',
            'current_stats' => $this->sanitizeStats($character->current_stats),
            'goal_races' => $goalRaces->all(),
            'next_goal' => $nextGoal,
            'remaining_goal_count' => max(0, $goalRaces->count() - ($nextGoal ? 1 : 0)),
            'updated_at' => $character->updated_at?->toDateTimeString(),
            'created_at' => $character->created_at?->toDateTimeString(),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $stats
     * @return array<string, int|null>
     */
    private function sanitizeStats(?array $stats): array
    {
        $stats = is_array($stats) ? $stats : [];

        return [
            'speed' => $this->normalizeStat($stats['speed'] ?? null),
            'stamina' => $this->normalizeStat($stats['stamina'] ?? null),
            'power' => $this->normalizeStat($stats['power'] ?? null),
            'guts' => $this->normalizeStat($stats['guts'] ?? null),
            'wit' => $this->normalizeStat($stats['wit'] ?? null),
        ];
    }

    private function normalizeStat(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $intValue = (int) $value;

        if ($intValue < 0 || $intValue > 2000) {
            return null;
        }

        return $intValue;
    }

    private function resolveGroupLetter(string $name): string
    {
        $firstCharacter = mb_strtoupper(mb_substr(trim($name), 0, 1));

        return preg_match('/^[A-Z]$/', $firstCharacter) === 1
            ? $firstCharacter
            : '#';
    }

    private function resolveCanonicalKey(Character $character): string
    {
        if ($character->game_character_id !== null) {
            return 'game:'.$character->game_character_id;
        }

        $normalized = preg_replace('/[^a-z0-9]/i', '', mb_strtolower($character->name)) ?? '';

        return 'name:'.$normalized;
    }
}
