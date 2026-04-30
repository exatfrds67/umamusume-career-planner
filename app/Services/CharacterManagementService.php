<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aptitude;
use App\Models\Character;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CharacterManagementService
{
    public function __construct(
        protected FactorService $factorService,
    ) {}

    /**
     * Create a new character with initial stats and aptitudes.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Character
    {
        return DB::transaction(function () use ($data): Character {
            $traineeId = isset($data['trainee_id']) ? (int) $data['trainee_id'] : null;

            $character = Character::create([
                'user_id' => Auth::id(),
                'game_character_id' => $traineeId,
                'name' => (string) ($data['name'] ?? 'New Character'),
                'scenario_type' => (string) ($data['scenario_type'] ?? 'ura_finale'),
                'career_stage' => 'junior',
                'current_turn' => 1,
                'current_stats' => $data['stats'] ?? [
                    'speed' => 0,
                    'stamina' => 0,
                    'power' => 0,
                    'guts' => 0,
                    'wit' => 0,
                ],
                'stat_priorities' => [],
                'stat_breakpoints' => [],
                'energy_level' => 100,
                'mood_status' => 'normal',
                'conditions' => [],
                'status' => 'active',
                'is_pinned' => false,
                'is_seeded' => false,
                'available_sp' => 0,
            ]);

            if (isset($data['aptitudes']) && is_array($data['aptitudes'])) {
                $this->createAptitudes($character, $data['aptitudes']);
            }

            return $character;
        });
    }

    /**
     * Compute the stat bonuses contributed by a set of parent factor data.
     *
     * @param  array<int, array<string, mixed>>  $parentFactors  Each element: ['stat' => string, 'stars' => int]
     * @return array<string, int>
     */
    public function computeStatInheritance(array $parentFactors): array
    {
        $bonuses = ['speed' => 0, 'stamina' => 0, 'power' => 0, 'guts' => 0, 'wit' => 0];

        $starBonusMap = [1 => 5, 2 => 12, 3 => 21];

        foreach ($parentFactors as $factor) {
            $stat = (string) ($factor['stat'] ?? '');
            $stars = (int) ($factor['stars'] ?? 0);

            if (array_key_exists($stat, $bonuses) && array_key_exists($stars, $starBonusMap)) {
                $bonuses[$stat] += $starBonusMap[$stars];
            }
        }

        return $bonuses;
    }

    /**
     * @param  array<string, array<string, string>>  $aptitudes
     */
    private function createAptitudes(Character $character, array $aptitudes): void
    {
        foreach ($aptitudes['distance'] ?? [] as $type => $grade) {
            Aptitude::create([
                'character_id' => $character->id,
                'distance_type' => $type,
                'grade' => $grade,
            ]);
        }

        foreach ($aptitudes['surface'] ?? [] as $type => $grade) {
            Aptitude::create([
                'character_id' => $character->id,
                'surface_type' => $type,
                'grade' => $grade,
            ]);
        }

        foreach ($aptitudes['style'] ?? [] as $type => $grade) {
            Aptitude::create([
                'character_id' => $character->id,
                'running_style' => $type,
                'grade' => $grade,
            ]);
        }
    }
}
