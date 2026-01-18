<?php

namespace Tests\Unit\Services;

use App\Models\Character;
use App\Services\CharacterStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterStateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CharacterStateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CharacterStateService;
    }

    #[Test]
    public function it_recovers_energy_on_rest(): void
    {
        $character = Character::factory()->create([
            'energy_level' => 30,
            'mood_status' => 'normal',
        ]);

        $result = $this->service->rest($character);

        expect($result['recovered'])->toBeIn([50, 70]);
        expect($result['new_energy'])->toBe(min(100, 30 + $result['recovered']));
    }

    #[Test]
    public function it_advances_turn_and_stage(): void
    {
        $character = Character::factory()->create([
            'current_turn' => 24,
            'career_stage' => 'junior',
        ]);

        $result = $this->service->progressTurn($character);

        expect($result['turn'])->toBe(25)
            ->and($result['stage'])->toBe('classic')
            ->and($result['stage_changed'])->toBeTrue();
    }

    #[Test]
    public function it_updates_mood(): void
    {
        $character = Character::factory()->create([
            'mood_status' => 'normal',
        ]);

        $newMood = $this->service->updateMood($character, 1);

        expect($newMood)->toBe('good');
    }
}
