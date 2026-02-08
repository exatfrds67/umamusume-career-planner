<?php

namespace Database\Factories;

use App\Models\Career;
use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Race>
 */
class RaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $finishPosition = fake()->numberBetween(1, 18);
        $fieldSize = fake()->numberBetween(12, 18);
        $distanceMeters = fake()->randomElement([1200, 1400, 1600, 1800, 2000, 2200, 2400, 2500, 3000, 3200]);

        // Determine distance category based on meters
        // Database uses: 'short', 'mile', 'intermediate', 'long'
        // NOT the RaceDistance enum values ('sprint', 'mile', 'medium', 'long')
        $distanceCategory = match (true) {
            $distanceMeters < 1400 => 'short',
            $distanceMeters < 1800 => 'mile',
            $distanceMeters < 2400 => 'intermediate',
            default => 'long',
        };

        return [
            'career_id' => Career::factory(),
            'character_id' => Character::factory(),
            'race_name' => fake()->randomElement([
                'Satsuki Sho',
                'Tokyo Yushun',
                'Kikuka Sho',
                'Tenno Sho Spring',
                'Tenno Sho Autumn',
                'Japan Cup',
                'Arima Kinen',
                'Takarazuka Kinen',
                'Oka Sho',
                'Yushun Himba',
                'Shuka Sho',
                'Victoria Mile',
            ]),
            'race_internal_id' => fake()->optional()->uuid(),
            'turn_number' => fake()->numberBetween(1, 72),
            'career_phase' => fake()->randomElement(['junior', 'classic', 'senior']),
            'race_grade' => fake()->randomElement(['debut', 'maiden', 'G3', 'G2', 'G1']),
            'distance_category' => $distanceCategory,
            'distance_meters' => $distanceMeters,
            'surface' => fake()->randomElement(['turf', 'dirt']),
            'track_type' => fake()->randomElement(['right', 'left', 'straight']),
            'running_style' => fake()->randomElement(['escape', 'leading', 'insert', 'tracking']),
            'weather' => fake()->randomElement(['sunny', 'cloudy', 'rainy', 'snowy']),
            'track_condition' => fake()->randomElement(['firm', 'good', 'yielding', 'soft', 'heavy']),
            'field_size' => $fieldSize,
            'race_conditions' => [
                'weather' => fake()->randomElement(['sunny', 'cloudy', 'rainy']),
                'track_condition' => fake()->randomElement(['good', 'slightly_heavy', 'heavy']),
            ],
            'character_condition' => fake()->randomElement(['perfect', 'good', 'normal', 'bad', 'very_bad']),
            'motivation' => fake()->randomElement(['very_high', 'high', 'normal', 'low', 'very_low']),
            'energy_level' => fake()->numberBetween(50, 100),
            'speed_at_race' => fake()->numberBetween(400, 1200),
            'stamina_at_race' => fake()->numberBetween(400, 1200),
            'power_at_race' => fake()->numberBetween(400, 1200),
            'guts_at_race' => fake()->numberBetween(400, 1200),
            'wit_at_race' => fake()->numberBetween(400, 1200),
            'finish_position' => $finishPosition,
            'finish_time' => fake()->randomFloat(3, 60, 200),
            'won_race' => $finishPosition === 1,
            'margin_of_victory' => $finishPosition === 1 ? fake()->numberBetween(1, 10) : null,
            'race_result' => match (true) {
                $finishPosition === 1 => 'victory',
                $finishPosition <= 2 => 'place',
                $finishPosition <= 3 => 'show',
                default => 'out_of_money',
            },
            'skills_activated' => [],
            'race_segments' => [],
            'speed_rating' => fake()->optional()->randomFloat(2, 70, 120),
            'performance_analysis' => null,
            'fans_gained' => fake()->numberBetween(100, 50000),
            'sp_reward' => fake()->numberBetween(10, 50),
            'item_rewards' => [],
            'stat_bonuses' => [],
            'injury_occurred' => false,
            'is_ura_finale_race' => false,
            'ura_finale_stage' => null,
            'ura_finale_requirements' => null,
            'is_unity_cup_match' => false,
            'unity_cup_points_earned' => null,
            'unity_cup_opponent_rank' => null,
            'race_notes' => null,
            'strategic_importance' => fake()->randomElement(['high', 'medium', 'low']),
            'preparation_strategy' => null,
            'lessons_learned' => null,
            'race_metadata' => null,
        ];
    }

    /**
     * Indicate that the race has prediction data.
     */
    public function withPredictions(): static
    {
        return $this->state(function (array $attributes) {
            $predictedPosition = $attributes['finish_position'] + fake()->numberBetween(-2, 2);
            $predictedPosition = max(1, min($attributes['field_size'], $predictedPosition));

            return [
                'performance_analysis' => [
                    'predicted_position' => $predictedPosition,
                    'prediction_confidence' => fake()->randomFloat(2, 0.5, 0.9),
                    'stat_adequacy' => [
                        'speed' => fake()->randomElement(['adequate', 'borderline', 'insufficient']),
                        'stamina' => fake()->randomElement(['adequate', 'borderline', 'insufficient']),
                        'power' => fake()->randomElement(['adequate', 'borderline', 'insufficient']),
                    ],
                ],
            ];
        });
    }

    /**
     * Indicate that the race was won.
     */
    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'finish_position' => 1,
            'won_race' => true,
            'race_result' => 'victory',
            'margin_of_victory' => fake()->numberBetween(1, 10),
        ]);
    }

    /**
     * Indicate that the race was a URA Finale race.
     */
    public function uraFinale(string $stage = 'URA1'): static
    {
        return $this->state(fn (array $attributes) => [
            'is_ura_finale_race' => true,
            'ura_finale_stage' => $stage,
            'race_grade' => $stage,
        ]);
    }

    /**
     * Set specific race grade.
     */
    public function grade(string $grade): static
    {
        return $this->state(fn (array $attributes) => [
            'race_grade' => $grade,
        ]);
    }
}
