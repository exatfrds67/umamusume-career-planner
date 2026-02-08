<?php

declare(strict_types=1);

namespace App\Collections;

use App\ValueObjects\Recommendation;
use Illuminate\Support\Collection;

/**
 * Recommendation Collection
 *
 * Type-safe collection for Recommendation value objects.
 * Provides convenience methods for filtering and sorting recommendations.
 *
 * @extends Collection<int, Recommendation>
 */
class RecommendationCollection extends Collection
{
    /**
     * Create a new recommendation collection
     *
     * @param  array<Recommendation>  $items
     */
    public function __construct(array $items = [])
    {
        // Validate all items are Recommendation instances
        foreach ($items as $item) {
            if (! $item instanceof Recommendation) {
                throw new \InvalidArgumentException('All items must be Recommendation instances');
            }
        }

        parent::__construct($items);
    }

    /**
     * Get recommendations by priority
     */
    public function byPriority(\App\Enums\Priority $priority): static
    {
        return $this->filter(fn (Recommendation $rec) => $rec->priority === $priority);
    }

    /**
     * Get recommendations by type
     */
    public function byType(\App\Enums\RecommendationType $type): static
    {
        return $this->filter(fn (Recommendation $rec) => $rec->type === $type);
    }

    /**
     * Sort by priority (critical first)
     */
    public function sortByPriority(): static
    {
        $priorityOrder = [
            \App\Enums\Priority::CRITICAL->value => 0,
            \App\Enums\Priority::HIGH->value => 1,
            \App\Enums\Priority::MEDIUM->value => 2,
            \App\Enums\Priority::LOW->value => 3,
        ];

        return $this->sort(function (Recommendation $a, Recommendation $b) use ($priorityOrder) {
            return $priorityOrder[$a->priority->value] <=> $priorityOrder[$b->priority->value];
        });
    }

    /**
     * Sort by confidence score (highest first)
     */
    public function sortByConfidence(): static
    {
        return $this->sort(function (Recommendation $a, Recommendation $b) {
            $scoreA = $a->confidenceScore ?? 0.0;
            $scoreB = $b->confidenceScore ?? 0.0;

            return $scoreB <=> $scoreA; // Descending order
        });
    }

    /**
     * Get the highest priority recommendation
     */
    public function highest(): ?Recommendation
    {
        /** @var Recommendation|null */
        return $this->sortByPriority()->first();
    }

    /**
     * Get recommendations with confidence above threshold
     */
    public function withConfidenceAbove(float $threshold): static
    {
        return $this->filter(function (Recommendation $rec) use ($threshold) {
            return $rec->confidenceScore !== null && $rec->confidenceScore >= $threshold;
        });
    }

    /**
     * Override map to return a base Collection instead of RecommendationCollection.
     *
     * This is necessary because map() transforms items into different types,
     * which would fail the RecommendationCollection constructor validation.
     *
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function map(callable $callback): \Illuminate\Support\Collection
    {
        return new \Illuminate\Support\Collection(
            array_map($callback, $this->items, array_keys($this->items))
        );
    }
}
