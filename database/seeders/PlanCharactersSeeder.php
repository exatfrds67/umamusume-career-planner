<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Career;
use App\Models\Character;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * PlanCharactersSeeder
 *
 * Creates ucp_characters rows owned by the admin user (admin@umamusume.local)
 * to represent each of the 8 imported career plans, then re-links each
 * ucp_careers row to the new character so the Training Predictions dropdown
 * shows the correct entries.
 *
 * Safe to re-run: skips creation if characters already exist for careers 1–8.
 */
class PlanCharactersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::where('email', 'admin@umamusume.local')->firstOrFail();

        // Guard: skip if Biwa Hayahide already exists as an admin-owned plan character
        $alreadySeeded = Character::where('user_id', $admin->id)
            ->where('name', 'Biwa Hayahide')
            ->exists();

        if ($alreadySeeded) {
            $this->command->warn('Plan characters for admin already exist. Skipping.');

            return;
        }

        $this->command->info("Seeding plan characters for admin: {$admin->email}");

        $plans = $this->planDefinitions();

        foreach ($plans as $careerId => $plan) {
            $character = Character::create([
                'user_id' => $admin->id,
                'uuid' => Str::uuid()->toString(),
                'name' => $plan['name'],
                'scenario_type' => $plan['scenario_type'],
                'career_stage' => $plan['career_stage'],
                'current_turn' => $plan['current_turn'],
                'current_stats' => $plan['current_stats'],
                'stat_priorities' => $plan['stat_priorities'],
                'growth_rates' => $plan['growth_rates'],
                'available_sp' => $plan['available_sp'],
                'energy_level' => $plan['energy_level'],
                'mood_status' => $plan['mood_status'],
                'status' => $plan['status'],
            ]);

            Career::where('id', $careerId)->update(['character_id' => $character->id]);

            $this->command->line("  Career {$careerId}: {$plan['name']} → character #{$character->id}");
        }

        $this->command->info('  Done — '.count($plans).' characters created and careers re-linked.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function planDefinitions(): array
    {
        return [
            // Career 1 — Biwa Hayahide (planning, senior)
            1 => [
                'name' => 'Biwa Hayahide',
                'scenario_type' => 'ura_finale',
                'career_stage' => 'senior',
                'current_turn' => 1,
                'current_stats' => ['speed' => 396, 'stamina' => 340, 'power' => 368, 'guts' => 400, 'wit' => 254],
                'stat_priorities' => ['speed' => 3, 'stamina' => 2, 'power' => 3, 'guts' => 5, 'wit' => 4],
                'growth_rates' => ['speed' => 1.0, 'stamina' => 1.0, 'power' => 1.0, 'guts' => 1.1, 'wit' => 1.2],
                'available_sp' => 17,
                'energy_level' => 80,
                'mood_status' => 'good',
                'status' => 'active',
            ],
            // Career 2 — Vodka (Star, completed)
            2 => [
                'name' => 'Vodka (Star)',
                'scenario_type' => 'ura_finale',
                'career_stage' => 'senior',
                'current_turn' => 78,
                'current_stats' => ['speed' => 767, 'stamina' => 410, 'power' => 769, 'guts' => 324, 'wit' => 253],
                'stat_priorities' => ['speed' => 5, 'stamina' => 2, 'power' => 5, 'guts' => 1, 'wit' => 1],
                'growth_rates' => ['speed' => 1.1, 'stamina' => 1.0, 'power' => 1.2, 'guts' => 1.0, 'wit' => 1.0],
                'available_sp' => 0,
                'energy_level' => 100,
                'mood_status' => 'great',
                'status' => 'completed',
            ],
            // Career 3 — Vodka (Platinum, completed)
            3 => [
                'name' => 'Vodka (Platinum)',
                'scenario_type' => 'ura_finale',
                'career_stage' => 'senior',
                'current_turn' => 78,
                'current_stats' => ['speed' => 646, 'stamina' => 474, 'power' => 765, 'guts' => 284, 'wit' => 279],
                'stat_priorities' => ['speed' => 4, 'stamina' => 3, 'power' => 5, 'guts' => 1, 'wit' => 2],
                'growth_rates' => ['speed' => 1.1, 'stamina' => 1.0, 'power' => 1.2, 'guts' => 1.0, 'wit' => 1.0],
                'available_sp' => 347,
                'energy_level' => 100,
                'mood_status' => 'great',
                'status' => 'completed',
            ],
            // Career 4 — Daiwa Scarlet (Star, completed)
            4 => [
                'name' => 'Daiwa Scarlet',
                'scenario_type' => 'ura_finale',
                'career_stage' => 'senior',
                'current_turn' => 78,
                'current_stats' => ['speed' => 663, 'stamina' => 437, 'power' => 543, 'guts' => 408, 'wit' => 303],
                'stat_priorities' => ['speed' => 5, 'stamina' => 3, 'power' => 3, 'guts' => 5, 'wit' => 2],
                'growth_rates' => ['speed' => 1.1, 'stamina' => 1.0, 'power' => 1.0, 'guts' => 1.2, 'wit' => 1.0],
                'available_sp' => 75,
                'energy_level' => 100,
                'mood_status' => 'great',
                'status' => 'completed',
            ],
            // Career 5 — Tokai Teio (planning, classic)
            5 => [
                'name' => 'Tokai Teio',
                'scenario_type' => 'ura_finale',
                'career_stage' => 'classic',
                'current_turn' => 28,
                'current_stats' => ['speed' => 345, 'stamina' => 395, 'power' => 256, 'guts' => 252, 'wit' => 303],
                'stat_priorities' => ['speed' => 3, 'stamina' => 5, 'power' => 2, 'guts' => 3, 'wit' => 4],
                'growth_rates' => ['speed' => 1.1, 'stamina' => 1.1, 'power' => 1.0, 'guts' => 1.1, 'wit' => 1.0],
                'available_sp' => 38,
                'energy_level' => 90,
                'mood_status' => 'good',
                'status' => 'active',
            ],
            // Career 6 — Haru Urara (JBC Sprint, planning, senior)
            6 => [
                'name' => 'Haru Urara (JBC)',
                'scenario_type' => 'ura_finale',
                'career_stage' => 'senior',
                'current_turn' => 56,
                'current_stats' => ['speed' => 485, 'stamina' => 305, 'power' => 404, 'guts' => 314, 'wit' => 264],
                'stat_priorities' => ['speed' => 4, 'stamina' => 2, 'power' => 3, 'guts' => 5, 'wit' => 2],
                'growth_rates' => ['speed' => 1.0, 'stamina' => 1.0, 'power' => 1.0, 'guts' => 1.2, 'wit' => 1.0],
                'available_sp' => 174,
                'energy_level' => 85,
                'mood_status' => 'good',
                'status' => 'active',
            ],
            // Career 7 — Haru Urara (URA Finale Qualifier, planning, senior)
            7 => [
                'name' => 'Haru Urara (URA)',
                'scenario_type' => 'ura_finale',
                'career_stage' => 'senior',
                'current_turn' => 78,
                'current_stats' => ['speed' => 423, 'stamina' => 276, 'power' => 461, 'guts' => 448, 'wit' => 264],
                'stat_priorities' => ['speed' => 3, 'stamina' => 2, 'power' => 4, 'guts' => 5, 'wit' => 2],
                'growth_rates' => ['speed' => 1.0, 'stamina' => 1.0, 'power' => 1.1, 'guts' => 1.2, 'wit' => 1.0],
                'available_sp' => 4,
                'energy_level' => 95,
                'mood_status' => 'great',
                'status' => 'active',
            ],
            // Career 8 — El Condor Pasa (planning, junior)
            8 => [
                'name' => 'El Condor Pasa',
                'scenario_type' => 'ura_finale',
                'career_stage' => 'junior',
                'current_turn' => 12,
                'current_stats' => ['speed' => 194, 'stamina' => 154, 'power' => 133, 'guts' => 97, 'wit' => 156],
                'stat_priorities' => ['speed' => 5, 'stamina' => 3, 'power' => 2, 'guts' => 1, 'wit' => 4],
                'growth_rates' => ['speed' => 1.2, 'stamina' => 1.0, 'power' => 1.0, 'guts' => 1.0, 'wit' => 1.1],
                'available_sp' => 38,
                'energy_level' => 100,
                'mood_status' => 'normal',
                'status' => 'active',
            ],
        ];
    }
}
