<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeletionStatus;
use App\Models\DeletionRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeletionRequest>
 */
class DeletionRequestFactory extends Factory
{
    protected $model = DeletionRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => DeletionStatus::Pending,
            'reason' => fake()->optional()->sentence(),
            'grace_period_ends_at' => now()->addDays(30),
        ];
    }

    /**
     * Indicate the request has been cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => DeletionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Indicate the deletion has been completed.
     */
    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => DeletionStatus::Completed,
            'completed_at' => now(),
            'grace_period_ends_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate the grace period has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => DeletionStatus::Pending,
            'grace_period_ends_at' => now()->subDay(),
        ]);
    }
}
