<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RivalRaceOutcome;
use App\Enums\TsDistance;
use App\Models\Career;
use App\Models\RivalLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RivalLog>
 */
class RivalLogFactory extends Factory
{
    protected $model = RivalLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'career_id' => Career::factory(),
            'race_id' => fake()->numberBetween(1, 9999),
            'distance' => TsDistance::Mile->value,
            'rival_uma' => fake()->name(),
            'outcome' => RivalRaceOutcome::Lost->value,
            'skill_hints' => [fake()->word()],
        ];
    }
}
