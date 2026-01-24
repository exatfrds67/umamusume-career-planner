<?php

namespace Database\Factories;

use App\Models\ExternalData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExternalData>
 */
class ExternalDataFactory extends Factory
{
    protected $model = ExternalData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sources = ['umapyoi.net', 'umamusumedb.com', 'internal'];
        $dataTypes = ['character', 'support_card', 'skill', 'race', 'news'];

        return [
            'source' => fake()->randomElement($sources),
            'external_id' => fake()->uuid(),
            'data_type' => fake()->randomElement($dataTypes),
            'data' => [
                'payload' => fake()->sentence(),
                'version' => fake()->numberBetween(1, 5),
            ],
            'cached_at' => fake()->dateTimeBetween('-2 days', 'now'),
            'expires_at' => fake()->dateTimeBetween('now', '+2 days'),
            'is_valid' => true,
            'validation_errors' => [],
            'last_checked_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'metadata' => [
                'source_version' => fake()->numberBetween(1, 3),
                'etag' => fake()->uuid(),
            ],
        ];
    }

    /**
     * Indicate that the data is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-2 days', '-1 day'),
            'is_valid' => false,
        ]);
    }
}
