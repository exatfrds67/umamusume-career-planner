<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\GameRace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GameRace
 */
class GoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var GameRace $goalRace */
        $goalRace = $this->resource;

        return [
            'id' => $goalRace->id,
            'name' => $goalRace->name_en,
            'grade' => $goalRace->grade,
            'distance' => $goalRace->distance_meters,
            'distance_category' => $goalRace->distance_category,
            'phase' => $goalRace->phase,
            'venue' => $goalRace->venue,
            'priority' => (int) ($goalRace->pivot->priority ?? 0),
            'notes' => $goalRace->pivot->notes,
        ];
    }
}
