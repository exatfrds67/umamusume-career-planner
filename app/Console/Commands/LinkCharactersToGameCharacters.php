<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Services\CharacterGameDataResolver;
use Illuminate\Console\Command;

class LinkCharactersToGameCharacters extends Command
{
    protected $signature = 'characters:link-game-data
        {--dry-run : Show matches without updating}
        {--force : Overwrite existing links}';

    protected $description = 'Auto-link user characters to game character catalog by matching names';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');
        $resolver = app(CharacterGameDataResolver::class);

        $query = Character::query();
        if (! $force) {
            $query->whereNull('game_character_id');
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, Character> $characters */
        $characters = $query->get();
        $linked = 0;
        $skipped = 0;
        $unmatched = [];

        foreach ($characters as $character) {
            $gameCharacter = $resolver->findByCharacterName($character->name);
            $gameCharacterId = $gameCharacter?->id;

            if ($gameCharacterId) {
                if ($isDryRun) {
                    $this->info("  [DRY RUN] Would link '{$character->name}' (#{$character->id}) → game_character #{$gameCharacterId}");
                } else {
                    $character->update([
                        'game_character_id' => $gameCharacterId,
                        'avatar_url' => $character->getRawOriginal('avatar_url') ?: $resolver->resolveAvatarUrlForGameCharacter($gameCharacter),
                    ]);
                }
                $linked++;
            } else {
                $unmatched[] = $character->name;
                $skipped++;
            }
        }

        $this->newLine();
        $this->info($isDryRun ? '=== DRY RUN RESULTS ===' : '=== LINKING RESULTS ===');
        $this->info("Linked: {$linked}");
        $this->info("Unmatched: {$skipped}");

        if (! empty($unmatched)) {
            $this->newLine();
            $this->warn('Unmatched characters (no game character found):');
            foreach ($unmatched as $name) {
                $this->line("  - {$name}");
            }
        }

        return self::SUCCESS;
    }
}
