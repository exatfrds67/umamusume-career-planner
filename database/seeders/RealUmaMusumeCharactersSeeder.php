<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RealUmaMusumeCharactersSeeder extends Seeder
{
    /**
     * Map of character names to local image files.
     */
    private \Illuminate\Support\Collection $localImageMap;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Build local image map
        $this->buildLocalImageMap();

        // Fetch character data from umapyoi.net API
        $response = Http::timeout(30)->get('https://api.umapyoi.net/api/v1/character/info');

        if (! $response->successful()) {
            Log::error('Failed to fetch Uma Musume character data from API');
            $this->command->error('Failed to fetch character data from umapyoi.net API');

            return;
        }

        $characters = $response->json();

        // Filter for English global server characters only (those with name_en)
        $englishCharacters = collect($characters)->filter(function ($char) {
            return ! empty($char['name_en']);
        });

        $this->command->info("Found {$englishCharacters->count()} English global server characters");
        $this->command->info("Found {$this->localImageMap->count()} local character images");

        $created = 0;
        $skipped = 0;
        $withLocalImage = 0;

        foreach ($englishCharacters as $charData) {
            try {
                // Check if character already exists
                $exists = Character::where('user_id', $user->id)
                    ->where('name', $charData['name_en'])
                    ->exists();

                if ($exists) {
                    $skipped++;

                    continue;
                }

                // Get avatar URL - prefer local image, fallback to API image
                $avatarUrl = $this->getAvatarUrl($charData);
                if (Str::startsWith($avatarUrl, '/images/trainee_images/')) {
                    $withLocalImage++;
                }

                // Create character with default values
                Character::create([
                    'user_id' => $user->id,
                    'name' => $charData['name_en'],
                    'avatar_url' => $avatarUrl,
                    'scenario_type' => 'ura_finale',
                    'career_stage' => 'junior',
                    'current_turn' => 1,
                    'current_stats' => [
                        'speed' => 0,
                        'stamina' => 0,
                        'power' => 0,
                        'guts' => 0,
                        'wit' => 0,
                    ],
                    'stat_priorities' => [
                        'speed' => 5,
                        'stamina' => 4,
                        'power' => 3,
                        'guts' => 2,
                        'wit' => 1,
                    ],
                    'stat_breakpoints' => [],
                    'energy_level' => 100,
                    'mood_status' => 'normal',
                    'conditions' => [],
                    'days_until_race' => null,
                    'goals' => [
                        'target_stats' => [
                            'speed' => 1200,
                            'stamina' => 1000,
                            'power' => 1000,
                            'guts' => 800,
                            'wit' => 1000,
                        ],
                    ],
                    'race_schedule' => [],
                    'training_plan' => [],
                    'growth_rates' => [
                        'speed' => 1.0,
                        'stamina' => 1.0,
                        'power' => 1.0,
                        'guts' => 1.0,
                        'wit' => 1.0,
                    ],
                    'inherited_factors' => [],
                    'legacy_parents' => [],
                    'team_composition' => [],
                    'facility_levels' => [],
                    'spirit_burst_data' => [],
                    'status' => 'active',
                    'completion_data' => [],
                    'available_sp' => 0,
                ]);

                $created++;
            } catch (\Exception $e) {
                Log::error("Failed to create character: {$charData['name_en']}", [
                    'error' => $e->getMessage(),
                ]);
                $this->command->warn("Failed to create character: {$charData['name_en']}");
            }
        }

        $this->command->info("Successfully created {$created} characters");
        $this->command->info("Using local images for {$withLocalImage} characters");
        if ($skipped > 0) {
            $this->command->info("Skipped {$skipped} existing characters");
        }
    }

    /**
     * Build a map of character names to local image files.
     */
    private function buildLocalImageMap(): void
    {
        $imagePath = base_path('images/trainee_images');

        if (! File::exists($imagePath)) {
            $this->localImageMap = collect();

            return;
        }

        $files = File::files($imagePath);

        $this->localImageMap = collect($files)->mapWithKeys(function ($file) {
            $filename = $file->getFilename();

            // Extract character name from filename (format: __character_name_umamusume_...)
            if (preg_match('/^__([a-z_]+)_umamusume/', $filename, $matches)) {
                $characterSlug = $matches[1];

                // Convert slug to proper name format
                $characterName = $this->slugToName($characterSlug);

                return [$characterName => '/images/trainee_images/'.$filename];
            }

            return [];
        })->filter();
    }

    /**
     * Convert character slug to proper name format.
     */
    private function slugToName(string $slug): string
    {
        // Map of known slug variations to proper names
        $nameMap = [
            'admire_vega' => 'Admire Vega',
            'agnes_digital' => 'Agnes Digital',
            'agnes_tachyon' => 'Agnes Tachyon',
            'air_groove' => 'Air Groove',
            'curren_chan' => 'Curren Chan',
            'daiwa_scarlet' => 'Daiwa Scarlet',
            'el_condor_pasa' => 'El Condor Pasa',
            'fine_motion' => 'Fine Motion',
            'fuji_kiseki' => 'Fuji Kiseki',
            'gold_city' => 'Gold City',
            'gold_ship' => 'Gold Ship',
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
            'biwa_hayahide' => 'Biwa Hayahide',
            'grass_wonder' => 'Grass Wonder',
        ];

        return $nameMap[$slug] ?? Str::title(str_replace('_', ' ', $slug));
    }

    /**
     * Get avatar URL for a character, preferring local images.
     */
    private function getAvatarUrl(array $charData): ?string
    {
        $characterName = $charData['name_en'];

        // Check if we have a local image for this character
        if ($this->localImageMap->has($characterName)) {
            return $this->localImageMap->get($characterName);
        }

        // Fallback to API images
        return $charData['thumb_img'] ?? $charData['sns_icon'] ?? null;
    }
}
