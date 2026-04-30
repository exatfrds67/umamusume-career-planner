<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Character;
use App\Services\CharacterGameDataResolver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class PopulateCharacterAvatarUrlsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cache the image resolution map for performance
        $imageMap = Cache::rememberForever('character_image_map', function () {
            return (new CharacterGameDataResolver)->localImagesByName();
        });

        if (empty($imageMap)) {
            $this->command->warn('No character images found. Skipping avatar URL population.');

            return;
        }

        // Update all characters with avatar URLs
        $updated = 0;
        $skipped = 0;

        foreach (Character::all() as $character) {
            // Normalize character name to match image filename key
            $normalizedName = $this->normalizeCharacterName($character->name);

            // Look up image URL in map
            $avatarUrl = null;
            foreach ($imageMap as $imageName => $imagePath) {
                if (str($imageName)->lower()->contains($normalizedName)) {
                    $avatarUrl = $imagePath;
                    break;
                }
            }

            // Update character if image found
            if ($avatarUrl) {
                $character->update(['avatar_url' => $avatarUrl]);
                $updated++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("✓ Updated {$updated} character(s) with avatar URLs.");
        if ($skipped > 0) {
            $this->command->warn("⚠ Skipped {$skipped} character(s) without matching images.");
        }
    }

    /**
     * Normalize character name for image filename matching.
     */
    private function normalizeCharacterName(string $name): string
    {
        return str($name)
            ->lower()
            ->replace(' ', '_')
            ->replace('-', '_')
            ->value();
    }
}
