<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Character;
use App\Models\GameCharacter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CharacterGameDataResolver
{
    /**
     * @var array<int, GameCharacter>|null
     */
    private ?array $gameCharactersById = null;

    /**
     * @var array<string, GameCharacter>|null
     */
    private ?array $gameCharactersByName = null;

    /**
     * @var array<string, string>|null
     */
    private ?array $localImagesByName = null;

    public function findByCharacter(Character $character): ?GameCharacter
    {
        $linkedGameCharacter = $character->relationLoaded('gameCharacter')
            ? $character->getRelation('gameCharacter')
            : null;

        if ($linkedGameCharacter instanceof GameCharacter) {
            return $linkedGameCharacter;
        }

        $gameCharacterId = $character->getAttribute('game_character_id');
        if (is_numeric($gameCharacterId)) {
            return $this->findById((int) $gameCharacterId);
        }

        $name = $character->getAttribute('name');
        if (! is_string($name) || $name === '') {
            return null;
        }

        return $this->findByCharacterName($name);
    }

    public function findByCharacterName(string $name): ?GameCharacter
    {
        foreach ($this->candidateLookupKeys($name) as $lookupKey) {
            $gameCharacter = $this->gameCharactersByName()[$lookupKey] ?? null;

            if ($gameCharacter instanceof GameCharacter) {
                return $gameCharacter;
            }
        }

        return null;
    }

    public function resolveAvatarUrlForCharacter(Character $character, mixed $storedAvatarUrl): ?string
    {
        $normalizedStoredAvatarUrl = $this->normalizeImagePath($storedAvatarUrl);
        if ($normalizedStoredAvatarUrl !== null) {
            return $normalizedStoredAvatarUrl;
        }

        return $this->resolveAvatarUrlForGameCharacter($this->findByCharacter($character));
    }

    public function resolveAvatarUrlForGameCharacter(?GameCharacter $gameCharacter): ?string
    {
        if (! $gameCharacter instanceof GameCharacter) {
            return null;
        }

        $storedImagePath = $this->normalizeImagePath($gameCharacter->image_path);

        if ($storedImagePath !== null) {
            return $storedImagePath;
        }

        foreach ([$gameCharacter->name_en, $gameCharacter->name_jp] as $candidateName) {
            $localImagePath = $this->findLocalImagePathByName($candidateName);

            if ($localImagePath !== null) {
                return $localImagePath;
            }
        }

        return null;
    }

    public function normalizeVariantName(string $name): string
    {
        $normalizedName = preg_replace('/\s+/', ' ', trim($name));

        if (! is_string($normalizedName) || $normalizedName === '') {
            return '';
        }

        $baseName = preg_replace('/\s*\([^)]*\)\s*$/u', '', $normalizedName);

        if (! is_string($baseName) || trim($baseName) === '') {
            return $normalizedName;
        }

        return trim($baseName);
    }

    /**
     * @return array<string>
     */
    public function candidateLookupKeys(string $name): array
    {
        $exactKey = $this->normalizeLookupKey($name);
        $baseKey = $this->normalizeLookupKey($this->normalizeVariantName($name));

        return array_values(array_unique(array_filter([$exactKey, $baseKey])));
    }

    private function findById(int $gameCharacterId): ?GameCharacter
    {
        return $this->gameCharactersById()[$gameCharacterId] ?? null;
    }

    /**
     * @return array<int, GameCharacter>
     */
    private function gameCharactersById(): array
    {
        if ($this->gameCharactersById === null) {
            $this->primeLookups();
        }

        return $this->gameCharactersById ?? [];
    }

    /**
     * @return array<string, GameCharacter>
     */
    private function gameCharactersByName(): array
    {
        if ($this->gameCharactersByName === null) {
            $this->primeLookups();
        }

        return $this->gameCharactersByName ?? [];
    }

    private function primeLookups(): void
    {
        $this->gameCharactersById = [];
        $this->gameCharactersByName = [];

        $gameCharacters = GameCharacter::query()
            ->select(['id', 'name_en', 'name_jp', 'image_path'])
            ->get();

        foreach ($gameCharacters as $gameCharacter) {
            $this->gameCharactersById[$gameCharacter->id] = $gameCharacter;

            foreach ([$gameCharacter->name_en, $gameCharacter->name_jp] as $candidateName) {
                $lookupKey = $this->normalizeLookupKey($candidateName);

                if ($lookupKey !== null) {
                    $this->gameCharactersByName[$lookupKey] = $gameCharacter;
                }
            }
        }
    }

    private function normalizeLookupKey(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalizedValue = preg_replace('/\s+/', ' ', trim($value));

        if (! is_string($normalizedValue) || $normalizedValue === '') {
            return null;
        }

        return mb_strtolower($normalizedValue);
    }

    private function normalizeImagePath(mixed $imagePath): ?string
    {
        if (! is_string($imagePath)) {
            return null;
        }

        $normalizedPath = trim($imagePath);
        if ($normalizedPath === '') {
            return null;
        }

        if (str_starts_with($normalizedPath, 'http://') || str_starts_with($normalizedPath, 'https://') || str_starts_with($normalizedPath, '/')) {
            return $normalizedPath;
        }

        return '/'.$normalizedPath;
    }

    private function findLocalImagePathByName(mixed $name): ?string
    {
        $lookupKey = $this->normalizeLookupKey($name);

        if ($lookupKey === null) {
            return null;
        }

        return $this->localImagesByName()[$lookupKey] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function localImagesByName(): array
    {
        if ($this->localImagesByName !== null) {
            return $this->localImagesByName;
        }

        $imageDirectory = public_path('images/trainee_images');
        if (! File::exists($imageDirectory)) {
            $this->localImagesByName = [];

            return $this->localImagesByName;
        }

        $localImagesByName = [];

        foreach (File::files($imageDirectory) as $file) {
            $filename = $file->getFilename();

            if (! preg_match('/^__([a-z_]+)_umamusume/u', $filename, $matches)) {
                continue;
            }

            $characterName = $this->slugToName($matches[1]);
            $lookupKey = $this->normalizeLookupKey($characterName);

            if ($lookupKey === null) {
                continue;
            }

            $localImagesByName[$lookupKey] = '/images/trainee_images/'.$filename;
        }

        foreach ($this->manualLocalImageOverrides() as $characterName => $path) {
            $lookupKey = $this->normalizeLookupKey($characterName);

            if ($lookupKey !== null) {
                $localImagesByName[$lookupKey] = $path;
            }
        }

        $this->localImagesByName = $localImagesByName;

        return $this->localImagesByName;
    }

    private function slugToName(string $slug): string
    {
        $nameMap = [
            'admire_vega' => 'Admire Vega',
            'agnes_digital' => 'Agnes Digital',
            'agnes_tachyon' => 'Agnes Tachyon',
            'air_groove' => 'Air Groove',
            'biwa_hayahide' => 'Biwa Hayahide',
            'curren_chan' => 'Curren Chan',
            'daiwa_scarlet' => 'Daiwa Scarlet',
            'el_condor_pasa' => 'El Condor Pasa',
            'fine_motion' => 'Fine Motion',
            'fuji_kiseki' => 'Fuji Kiseki',
            'gold_city' => 'Gold City',
            'gold_ship' => 'Gold Ship',
            'grass_wonder' => 'Grass Wonder',
            'haru_urara' => 'Haru Urara',
            'hishi_akebono' => 'Hishi Akebono',
            'hishi_amazon' => 'Hishi Amazon',
            'ikuno_dictus' => 'Ikuno Dictus',
            'ines_fujin' => 'Ines Fujin',
            'kawakami_princess' => 'Kawakami Princess',
            'king_halo' => 'King Halo',
            'kitasan_black' => 'Kitasan Black',
            'manhattan_cafe' => 'Manhattan Cafe',
            'maruzensky' => 'Maruzensky',
            'matikanefukukitaru' => 'Matikane Fukukitaru',
            'matikanetannhauser' => 'Matikane Tannhauser',
            'mayano_top_gun' => 'Mayano Top Gun',
            'meisho_doto' => 'Meisho Doto',
            'mejiro_ardan' => 'Mejiro Ardan',
            'mejiro_dober' => 'Mejiro Dober',
            'mejiro_mcqueen' => 'Mejiro McQueen',
            'mejiro_palmer' => 'Mejiro Palmer',
            'mejiro_ryan' => 'Mejiro Ryan',
            'mihono_bourbon' => 'Mihono Bourbon',
            'mr_c_b' => 'Mr. C.B.',
            'nakayama_festa' => 'Nakayama Festa',
            'narita_brian' => 'Narita Brian',
            'narita_taishin' => 'Narita Taishin',
            'nice_nature' => 'Nice Nature',
            'nishino_flower' => 'Nishino Flower',
            'oguri_cap' => 'Oguri Cap',
            'rice_shower' => 'Rice Shower',
            'sakura_bakushin_o' => 'Sakura Bakushin O',
            'sakura_chiyono_o' => 'Sakura Chiyono O',
            'satono_diamond' => 'Satono Diamond',
            'seiun_sky' => 'Seiun Sky',
            'shinko_windy' => 'Shinko Windy',
            'silence_suzuka' => 'Silence Suzuka',
            'smart_falcon' => 'Smart Falcon',
            'special_week' => 'Special Week',
            'symboli_rudolf' => 'Symboli Rudolf',
            't_m_opera_o' => 'T.M. Opera O',
            'taiki_shuttle' => 'Taiki Shuttle',
            'tamamo_cross' => 'Tamamo Cross',
            'tokai_teio' => 'Tokai Teio',
            'tosen_jordan' => 'Tosen Jordan',
            'twin_turbo' => 'Twin Turbo',
            'vodka' => 'Vodka',
            'yaeno_muteki' => 'Yaeno Muteki',
            'yukino_bijin' => 'Yukino Bijin',
            'zenno_rob_roy' => 'Zenno Rob Roy',
        ];

        return $nameMap[$slug] ?? Str::title(str_replace('_', ' ', $slug));
    }

    /**
     * @return array<string, string>
     */
    private function manualLocalImageOverrides(): array
    {
        $overrides = [];

        // Silence Suzuka — hash-named file
        $silenceSuzukaPath = '/images/trainee_images/bb962aabeafaee5cbf7831e4d178ca64.jpg';
        if (File::exists(public_path(ltrim($silenceSuzukaPath, '/')))) {
            $overrides['Silence Suzuka'] = $silenceSuzukaPath;
        }

        // Characters that only appear in multi-character images — map to the best available file
        $multiCharOverrides = [
            'Biwa Hayahide' => '/images/trainee_images/__narita_brian_and_biwa_hayahide_umamusume_drawn_by_hitoto__sample-e1edfe57e7e12f49d5a724698f738783.jpg',
            'Grass Wonder' => '/images/trainee_images/__special_week_and_grass_wonder_umamusume_drawn_by_murasaki_himuro__c5cd811241a372d775e9ba2e2d09c65f.jpg',
            'Nakayama Festa' => '/images/trainee_images/__nakayama_festa_and_alex_umamusume_and_2_more_drawn_by_hakuki__c80d09aab832bf5d94c91dfbe030df9f.jpg',
            'Winning Ticket' => '/images/trainee_images/__maruzensky_and_sakura_chiyono_o_umamusume_drawn_by_rin_yukameiko__0042184bd89f44eb954daa70b0b0b732.jpg',
        ];

        foreach ($multiCharOverrides as $characterName => $path) {
            if (File::exists(public_path(ltrim($path, '/')))) {
                $overrides[$characterName] = $path;
            }
        }

        return $overrides;
    }
}
