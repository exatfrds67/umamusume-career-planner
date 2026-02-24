<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ConsentType;
use App\Models\ConsentRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConsentRecord>
 */
class ConsentRecordFactory extends Factory
{
    protected $model = ConsentRecord::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'consent_type' => fake()->randomElement(ConsentType::cases()),
            'granted' => true,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'granted_at' => now(),
        ];
    }

    /**
     * Indicate the consent has been revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (): array => [
            'granted' => false,
            'revoked_at' => now(),
        ]);
    }

    /**
     * Indicate a specific consent type.
     */
    public function forType(ConsentType $type): static
    {
        return $this->state(fn (): array => [
            'consent_type' => $type,
        ]);
    }
}
