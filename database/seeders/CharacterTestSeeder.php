<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\User;
use Illuminate\Database\Seeder;

class CharacterTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed global demo characters under a stable owner account.
        // Visibility is controlled via is_seeded=true, not per-user duplication.
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        $seededNames = $this->seededCharacterNames();

        // Cleanup legacy per-admin copies created by older seeder versions.
        // Limit deletion to the three historical names to avoid touching user-owned data.
        $legacyPerAdminCharacterNames = ['Silence Suzuka', 'Tokai Teio', 'Gold Ship'];
        $adminUserIds = User::query()
            ->where('is_admin', true)
            ->where('id', '!=', $user->id)
            ->pluck('id');

        if ($adminUserIds->isNotEmpty()) {
            Character::query()
                ->whereIn('user_id', $adminUserIds)
                ->where('is_seeded', false)
                ->whereIn('name', $legacyPerAdminCharacterNames)
                ->delete();
        }

        foreach ($seededNames as $index => $name) {
            $characterData = $this->buildCharacterData($name, $index, $user->id);

            Character::updateOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                $characterData
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function seededCharacterNames(): array
    {
        return [
            'Silence Suzuka',
            'Fuji Kiseki',
            'Taiki Shuttle',
            'Daiwa Scarlet',
            'Agnes Tachyon',
            'Agnes Digital',
            'Sakura Bakushin O',
            'Admire Vega',
            'Maruzensky',
            'Gold Ship',
            'Kitasan Black',
            'Satono Diamond',
            'Rice Shower',
            'Mejiro McQueen',
            'T.M. Opera O',
            'Mejiro Palmer',
            'Mejiro Ryan',
            'Mejiro Dober',
            'Manhattan Cafe',
            'Tamamo Cross',
            'Matikane Fukukitaru',
            'Oguri Cap',
            'Narita Brian',
            'Haru Urara',
            'Mihono Bourbon',
            'Special Week',
            'Tokai Teio',
            'Vodka',
            'Air Groove',
            'Symboli Rudolf',
            'Grass Wonder',
            'Biwa Hayahide',
            'King Halo',
            'El Condor Pasa',
            'Fine Motion',
            'Tosen Jordan',
            'Kawakami Princess',
            'Seiun Sky',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCharacterData(string $name, int $index, int $ownerUserId): array
    {
        $scenarioTypes = ['ura_finale', 'unity_cup'];
        $careerStages = ['junior', 'classic', 'senior'];
        $moods = ['good', 'great', 'normal'];
        $turnByCareerStage = [14, 29, 53];
        $energyByCareerStage = [88, 82, 76];

        $stageIndex = $index % count($careerStages);
        $baseStats = $this->baseStatsFor($name);

        $currentStats = collect($baseStats)
            ->map(fn (int $value): int => max(300, min(1200, ($value * 15) + 180)))
            ->all();

        $targetStats = collect($currentStats)
            ->map(fn (int $value): int => min(1200, $value + 150))
            ->all();

        return [
            'user_id' => $ownerUserId,
            'name' => $name,
            'scenario_type' => $scenarioTypes[$index % count($scenarioTypes)],
            'career_stage' => $careerStages[$stageIndex],
            'current_turn' => $turnByCareerStage[$stageIndex],
            'current_stats' => $currentStats,
            'stat_priorities' => $this->buildStatPriorities($currentStats),
            'energy_level' => $energyByCareerStage[$stageIndex],
            'mood_status' => $moods[$index % count($moods)],
            'goals' => [
                'target_stats' => $targetStats,
            ],
            'is_seeded' => true,
        ];
    }

    /**
     * @param  array<string, int>  $stats
     * @return array<string, int>
     */
    private function buildStatPriorities(array $stats): array
    {
        arsort($stats);
        $priority = 5;
        $priorities = [];

        foreach (array_keys($stats) as $key) {
            $priorities[$key] = $priority;
            $priority--;
        }

        return $priorities;
    }

    /**
     * @return array<string, int>
     */
    private function baseStatsFor(string $characterName): array
    {
        $specializedStats = [
            'Silence Suzuka' => ['speed' => 60, 'stamina' => 40, 'power' => 45, 'guts' => 40, 'wit' => 50],
            'Fuji Kiseki' => ['speed' => 60, 'stamina' => 35, 'power' => 50, 'guts' => 40, 'wit' => 45],
            'Taiki Shuttle' => ['speed' => 65, 'stamina' => 30, 'power' => 55, 'guts' => 40, 'wit' => 40],
            'Daiwa Scarlet' => ['speed' => 60, 'stamina' => 45, 'power' => 50, 'guts' => 40, 'wit' => 45],
            'Agnes Tachyon' => ['speed' => 60, 'stamina' => 40, 'power' => 45, 'guts' => 35, 'wit' => 60],
            'Agnes Digital' => ['speed' => 60, 'stamina' => 40, 'power' => 50, 'guts' => 40, 'wit' => 45],
            'Sakura Bakushin O' => ['speed' => 65, 'stamina' => 30, 'power' => 60, 'guts' => 40, 'wit' => 35],
            'Admire Vega' => ['speed' => 55, 'stamina' => 45, 'power' => 45, 'guts' => 40, 'wit' => 50],
            'Maruzensky' => ['speed' => 40, 'stamina' => 60, 'power' => 45, 'guts' => 50, 'wit' => 45],
            'Gold Ship' => ['speed' => 40, 'stamina' => 60, 'power' => 45, 'guts' => 55, 'wit' => 35],
            'Kitasan Black' => ['speed' => 45, 'stamina' => 60, 'power' => 50, 'guts' => 50, 'wit' => 40],
            'Satono Diamond' => ['speed' => 40, 'stamina' => 60, 'power' => 45, 'guts' => 50, 'wit' => 45],
            'Rice Shower' => ['speed' => 40, 'stamina' => 60, 'power' => 40, 'guts' => 55, 'wit' => 45],
            'Mejiro McQueen' => ['speed' => 40, 'stamina' => 60, 'power' => 45, 'guts' => 50, 'wit' => 45],
            'T.M. Opera O' => ['speed' => 45, 'stamina' => 60, 'power' => 50, 'guts' => 45, 'wit' => 40],
            'Mejiro Palmer' => ['speed' => 40, 'stamina' => 60, 'power' => 45, 'guts' => 50, 'wit' => 45],
            'Mejiro Ryan' => ['speed' => 40, 'stamina' => 60, 'power' => 45, 'guts' => 50, 'wit' => 45],
            'Mejiro Dober' => ['speed' => 40, 'stamina' => 60, 'power' => 45, 'guts' => 50, 'wit' => 45],
            'Manhattan Cafe' => ['speed' => 40, 'stamina' => 60, 'power' => 45, 'guts' => 50, 'wit' => 45],
            'Tamamo Cross' => ['speed' => 40, 'stamina' => 60, 'power' => 45, 'guts' => 50, 'wit' => 45],
            'Matikane Fukukitaru' => ['speed' => 40, 'stamina' => 60, 'power' => 45, 'guts' => 55, 'wit' => 40],
            'Oguri Cap' => ['speed' => 45, 'stamina' => 50, 'power' => 60, 'guts' => 55, 'wit' => 35],
            'Narita Brian' => ['speed' => 50, 'stamina' => 45, 'power' => 60, 'guts' => 45, 'wit' => 40],
            'Haru Urara' => ['speed' => 50, 'stamina' => 40, 'power' => 60, 'guts' => 60, 'wit' => 30],
            'Mihono Bourbon' => ['speed' => 60, 'stamina' => 40, 'power' => 60, 'guts' => 45, 'wit' => 35],
            'Special Week' => ['speed' => 50, 'stamina' => 50, 'power' => 50, 'guts' => 45, 'wit' => 45],
            'Tokai Teio' => ['speed' => 50, 'stamina' => 50, 'power' => 50, 'guts' => 45, 'wit' => 45],
            'Vodka' => ['speed' => 50, 'stamina' => 50, 'power' => 45, 'guts' => 50, 'wit' => 45],
            'Air Groove' => ['speed' => 50, 'stamina' => 50, 'power' => 50, 'guts' => 45, 'wit' => 45],
            'Symboli Rudolf' => ['speed' => 50, 'stamina' => 50, 'power' => 50, 'guts' => 45, 'wit' => 45],
            'Grass Wonder' => ['speed' => 50, 'stamina' => 50, 'power' => 45, 'guts' => 45, 'wit' => 50],
            'Biwa Hayahide' => ['speed' => 45, 'stamina' => 50, 'power' => 50, 'guts' => 45, 'wit' => 55],
            'King Halo' => ['speed' => 50, 'stamina' => 50, 'power' => 50, 'guts' => 45, 'wit' => 45],
            'El Condor Pasa' => ['speed' => 50, 'stamina' => 50, 'power' => 50, 'guts' => 50, 'wit' => 40],
            'Fine Motion' => ['speed' => 50, 'stamina' => 45, 'power' => 50, 'guts' => 45, 'wit' => 50],
            'Tosen Jordan' => ['speed' => 45, 'stamina' => 50, 'power' => 50, 'guts' => 45, 'wit' => 50],
            'Kawakami Princess' => ['speed' => 50, 'stamina' => 45, 'power' => 50, 'guts' => 45, 'wit' => 50],
            'Seiun Sky' => ['speed' => 50, 'stamina' => 50, 'power' => 45, 'guts' => 45, 'wit' => 50],
        ];

        return $specializedStats[$characterName] ?? [
            'speed' => 45,
            'stamina' => 45,
            'power' => 45,
            'guts' => 45,
            'wit' => 45,
        ];
    }
}
