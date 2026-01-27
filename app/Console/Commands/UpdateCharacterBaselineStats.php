<?php

namespace App\Console\Commands;

use App\Models\Character;
use Illuminate\Console\Command;

class UpdateCharacterBaselineStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'characters:update-baseline-stats {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing characters with proper baseline stats based on their specializations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        // Get all characters with zero stats
        $characters = Character::whereJsonContains('current_stats->speed', 0)
            ->whereJsonContains('current_stats->stamina', 0)
            ->whereJsonContains('current_stats->power', 0)
            ->whereJsonContains('current_stats->guts', 0)
            ->whereJsonContains('current_stats->wit', 0)
            ->get();

        if ($characters->isEmpty()) {
            $this->info('No characters found with zero baseline stats.');

            return 0;
        }

        $this->info("Found {$characters->count()} characters with zero baseline stats.");

        $updated = 0;
        $skipped = 0;

        foreach ($characters as $character) {
            $baseStats = $this->getBaseStats($character->name);

            if ($dryRun) {
                $this->line("Would update {$character->name}: ".json_encode($baseStats));
            } else {
                $character->update(['current_stats' => $baseStats]);
                $this->info("Updated {$character->name}: ".json_encode($baseStats));
            }

            $updated++;
        }

        if ($dryRun) {
            $this->info("DRY RUN COMPLETE: Would update {$updated} characters, skip {$skipped} characters");
        } else {
            $this->info("COMPLETE: Updated {$updated} characters, skipped {$skipped} characters");
        }

        return 0;
    }

    /**
     * Get base stats for a character based on their specialization.
     *
     * @return array{speed: int, stamina: int, power: int, guts: int, wit: int}
     */
    private function getBaseStats(string $characterName): array
    {
        // Character-specific base stats based on specializations
        $specializedStats = [
            // Speed Specialists (Sprint/Mile focus)
            'Silence Suzuka' => ['speed' => 60, 'stamina' => 40, 'power' => 45, 'guts' => 40, 'wit' => 50],
            'Fuji Kiseki' => ['speed' => 60, 'stamina' => 35, 'power' => 50, 'guts' => 40, 'wit' => 45],
            'Taiki Shuttle' => ['speed' => 65, 'stamina' => 30, 'power' => 55, 'guts' => 40, 'wit' => 40],
            'Daiwa Scarlet' => ['speed' => 60, 'stamina' => 45, 'power' => 50, 'guts' => 40, 'wit' => 45],
            'Agnes Tachyon' => ['speed' => 60, 'stamina' => 40, 'power' => 45, 'guts' => 35, 'wit' => 60],
            'Agnes Digital' => ['speed' => 60, 'stamina' => 40, 'power' => 50, 'guts' => 40, 'wit' => 45],
            'Sakura Bakushin O' => ['speed' => 65, 'stamina' => 30, 'power' => 60, 'guts' => 40, 'wit' => 35],
            'Admire Vega' => ['speed' => 55, 'stamina' => 45, 'power' => 45, 'guts' => 40, 'wit' => 50],

            // Stamina Specialists (Long distance focus)
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

            // Power Specialists (Acceleration focus)
            'Oguri Cap' => ['speed' => 45, 'stamina' => 50, 'power' => 60, 'guts' => 55, 'wit' => 35],
            'Narita Brian' => ['speed' => 50, 'stamina' => 45, 'power' => 60, 'guts' => 45, 'wit' => 40],
            'Haru Urara' => ['speed' => 50, 'stamina' => 40, 'power' => 60, 'guts' => 60, 'wit' => 30],
            'Mihono Bourbon' => ['speed' => 60, 'stamina' => 40, 'power' => 60, 'guts' => 45, 'wit' => 35],

            // Balanced All-Rounders (Medium distance focus)
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

        // Return character-specific stats if available, otherwise balanced defaults
        return $specializedStats[$characterName] ?? [
            'speed' => 45,
            'stamina' => 45,
            'power' => 45,
            'guts' => 45,
            'wit' => 45,
        ];
    }
}
