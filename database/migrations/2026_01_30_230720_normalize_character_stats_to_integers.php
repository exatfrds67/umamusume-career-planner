<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration normalizes character stat values from strings to integers
     * in the current_stats JSON field.
     */
    public function up(): void
    {
        $characters = DB::table('ucp_characters')->get();
        $updatedCount = 0;
        $errorCount = 0;

        foreach ($characters as $character) {
            try {
                $currentStats = json_decode($character->current_stats, true);

                if (! is_array($currentStats)) {
                    continue;
                }

                // Check if any stat is a string
                $needsUpdate = false;
                foreach ($currentStats as $value) {
                    if (is_string($value)) {
                        $needsUpdate = true;
                        break;
                    }
                }

                if (! $needsUpdate) {
                    continue;
                }

                // Normalize all stats to integers
                $normalized = [
                    'speed' => $this->normalizeStatValue($currentStats['speed'] ?? 0),
                    'stamina' => $this->normalizeStatValue($currentStats['stamina'] ?? 0),
                    'power' => $this->normalizeStatValue($currentStats['power'] ?? 0),
                    'guts' => $this->normalizeStatValue($currentStats['guts'] ?? 0),
                    'wit' => $this->normalizeStatValue($currentStats['wit'] ?? 0),
                ];

                DB::table('ucp_characters')
                    ->where('id', $character->id)
                    ->update(['current_stats' => json_encode($normalized)]);

                $updatedCount++;
            } catch (\Exception $e) {
                Log::warning("Failed to normalize stats for character {$character->id}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        Log::info("Character stats normalization complete. Updated: {$updatedCount}, Errors: {$errorCount}");
    }

    /**
     * Reverse the migrations.
     *
     * No reversal needed - integer stats are valid in both states.
     */
    public function down(): void
    {
        Log::info('Character stats normalization rollback - no action needed (integer stats are valid)');
    }

    /**
     * Normalize a stat value to integer.
     */
    private function normalizeStatValue(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        Log::warning('Non-numeric stat value encountered: '.var_export($value, true));

        return 0;
    }
};
