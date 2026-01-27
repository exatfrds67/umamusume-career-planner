<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SupportCardDefinition;
use App\Services\CharacterMappingService;
use App\Services\ExternalDataService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * UCP Support Cards Seeder
 *
 * Comprehensive seeder that fetches all support cards from umapyoi.net API
 * and enriches top-tier SSR cards with meta data from Game8.co tier lists.
 *
 * Data Sources:
 * - Primary: umapyoi.net API (complete card list)
 * - Enrichment: Game8.co tier list data (meta tiers, strategic notes)
 */
class UcpSupportCardsSeeder extends Seeder
{
    protected ExternalDataService $externalDataService;

    protected CharacterMappingService $characterMapping;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->characterMapping = new CharacterMappingService;
        $this->externalDataService = new ExternalDataService($this->characterMapping);

        $this->command->info('Fetching support cards from umapyoi.net API...');

        // Fetch all cards from API
        $apiCards = $this->fetchApiCards();

        if (empty($apiCards)) {
            $this->command->error('Failed to fetch cards from API. Using fallback meta cards only.');
            $this->seedMetaCardsOnly();

            return;
        }

        $this->command->info('Found '.\count($apiCards).' cards from API.');

        // Get meta enrichment data
        $metaData = $this->getMetaEnrichmentData();

        // Process and seed cards
        $synced = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->command->getOutput()->createProgressBar(\count($apiCards));

        foreach ($apiCards as $apiCard) {
            try {
                $transformed = $this->transformCard($apiCard, $metaData);

                if ($transformed === null) {
                    $skipped++;
                    $progressBar->advance();

                    continue;
                }

                SupportCardDefinition::updateOrCreate(
                    ['external_source_id' => $transformed['external_source_id']],
                    $transformed
                );

                $synced++;
            } catch (\Exception $e) {
                $errors++;
                Log::warning('[UcpSupportCardsSeeder] Failed to seed card', [
                    'card_id' => $apiCard['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine(2);

        // Summary
        $this->command->info('Seeding complete!');
        $this->command->info("  - Synced: {$synced}");
        $this->command->info("  - Skipped: {$skipped}");
        $this->command->info("  - Errors: {$errors}");

        // Show breakdown by rarity
        $this->showRarityBreakdown();
    }

    /**
     * Fetch cards from umapyoi.net API
     *
     * @return array<int, array<string, mixed>>
     */
    protected function fetchApiCards(): array
    {
        try {
            $response = Http::timeout(60)->get('https://www.umapyoi.net/api/v1/support');

            if ($response->successful()) {
                $data = $response->json();

                return \is_array($data) ? $data : [];
            }
        } catch (\Exception $e) {
            Log::error('[UcpSupportCardsSeeder] API fetch failed', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Transform API card data with meta enrichment
     *
     * @param  array<string, mixed>  $apiCard
     * @param  array<string, array<string, mixed>>  $metaData
     * @return array<string, mixed>|null
     */
    protected function transformCard(array $apiCard, array $metaData): ?array
    {
        $id = $apiCard['id'] ?? null;
        $charaId = $apiCard['chara_id'] ?? null;
        $gametoraId = $apiCard['gametora'] ?? null;
        $titleEn = $apiCard['title_en'] ?? null;

        if ($id === null) {
            return null;
        }

        $characterName = $charaId !== null
            ? $this->characterMapping->getCharacterName((int) $charaId)
            : ($gametoraId !== null ? $this->characterMapping->extractNameFromGametoraId((string) $gametoraId) : 'Unknown');

        $rarity = $this->determineRarity((int) $id);
        $cardType = $this->determineCardType((int) ($charaId ?? 0), (string) ($gametoraId ?? ''));

        // Build card name
        $cardName = $titleEn !== null
            ? "{$characterName} {$titleEn}"
            : "{$characterName} [{$rarity}]";

        // Generate internal ID
        $internalId = $gametoraId !== null
            ? 'UMAPYOI_'.strtoupper(str_replace('-', '_', (string) $gametoraId))
            : "UMAPYOI_{$id}";

        // Check for meta enrichment
        $enrichment = $metaData[$internalId] ?? $metaData[$cardName] ?? null;

        $baseData = [
            'name' => $cardName,
            'internal_id' => $internalId,
            'external_source_id' => (string) $id,
            'external_source' => 'umapyoi',
            'gametora_id' => $gametoraId,
            'chara_id' => $charaId,
            'card_type' => $cardType,
            'rarity' => $rarity,
            'character_name' => $characterName,
            'character_internal_id' => $charaId !== null ? "CHAR_{$charaId}" : null,
            'is_active' => $titleEn !== null,
            'server_availability' => $titleEn !== null ? 'both' : 'jp',
            'meta_tier' => $this->getDefaultMetaTier($rarity),
            'unique_effects' => [],
            'skill_hints_provided' => [],
            'deck_synergies' => [],
            'recommended_scenarios' => [],
            'strategic_notes' => [],
        ];

        // Apply enrichment if available
        if ($enrichment !== null) {
            $baseData = array_merge($baseData, $enrichment);
        }

        return $baseData;
    }

    /**
     * Get meta enrichment data for top-tier cards
     *
     * @return array<string, array<string, mixed>>
     */
    protected function getMetaEnrichmentData(): array
    {
        return [
            // SS-Tier Cards
            'Kitasan Black [Pushed by the Approaching Passion]' => [
                'meta_tier' => 'S+',
                'card_type' => 'speed',
                'training_effect_bonus' => 15,
                'friendship_bonus' => 25,
                'skill_hints_provided' => ['Professor of Curvature', 'Corner Adept ◯', 'Focus'],
                'unique_effects' => ['Highest Specialty Priority for Speed cards', 'Training Effectiveness boost'],
                'strategic_notes' => ['Best all-around Speed Card', 'Essential for competitive play'],
                'usage_rate' => 98.5,
                'win_rate_contribution' => 96.2,
            ],
            'Super Creek [A Grain of Peace]' => [
                'meta_tier' => 'S+',
                'card_type' => 'stamina',
                'training_effect_bonus' => 10,
                'friendship_bonus' => 20,
                'skill_hints_provided' => ['Swinging Maestro', 'Stamina recovery skills'],
                'unique_effects' => ['Gives Swinging Maestro - best gold recovery skill'],
                'strategic_notes' => ['All-around best Stamina card', 'Essential for competitive play'],
                'usage_rate' => 96.8,
                'win_rate_contribution' => 94.5,
            ],
            'Fine Motion [Gratitude Up to One\'s Fingertips]' => [
                'meta_tier' => 'S+',
                'card_type' => 'speed',
                'training_effect_bonus' => 10,
                'friendship_bonus' => 20,
                'wit_bonus' => 25,
                'skill_hints_provided' => ['Speed Star', 'Right-Handed ◯', 'Corner Adept ◯'],
                'unique_effects' => ['Initial Wit 25 at MLB', 'Race Bonus 5%'],
                'strategic_notes' => ['Flexible for any deck', 'Has skills for Pace Chasers'],
                'usage_rate' => 95.3,
                'win_rate_contribution' => 93.1,
            ],
            'Tazuna Hayakawa [Welcome to Tracen Academy!]' => [
                'meta_tier' => 'S+',
                'card_type' => 'friend',
                'friendship_bonus' => 20,
                'event_recovery_bonus' => 15,
                'event_effect_bonus' => 15,
                'skill_hints_provided' => ['Tail Held High', 'Concentration'],
                'unique_effects' => ['Great Energy recovery', 'Reduces failure rates'],
                'strategic_notes' => ['Essential for training management', 'Best Friend card'],
                'usage_rate' => 94.7,
                'win_rate_contribution' => 92.8,
            ],
            'Biko Pegasus [Special Move! W Carrot Punch!]' => [
                'meta_tier' => 'S+',
                'card_type' => 'speed',
                'training_effect_bonus' => 15,
                'friendship_bonus' => 20,
                'skill_hints_provided' => ['Mile and Sprint oriented skills'],
                'unique_effects' => ['Very high Training Effectiveness'],
                'strategic_notes' => ['Acts as stat stick for non-Speed training'],
                'usage_rate' => 91.2,
                'win_rate_contribution' => 89.5,
            ],

            // S-Tier Cards
            'Rice Shower [When Happiness Dances]' => [
                'meta_tier' => 'S',
                'card_type' => 'power',
                'training_effect_bonus' => 10,
                'friendship_bonus' => 20,
                'skill_hints_provided' => ['Swinging Maestro', 'Cooldown'],
                'unique_effects' => ['Power card that gives Swinging Maestro'],
                'strategic_notes' => ['Option for stamina management at shorter distances'],
                'usage_rate' => 88.4,
                'win_rate_contribution' => 86.7,
            ],
            'Riko Kashimoto [Thorough Management]' => [
                'meta_tier' => 'S',
                'card_type' => 'friend',
                'friendship_bonus' => 20,
                'event_recovery_bonus' => 15,
                'skill_hints_provided' => ['Energy recovery skills'],
                'unique_effects' => ['Great Energy recovery and Mood events'],
                'strategic_notes' => ['Essential Pal card for consistency'],
                'usage_rate' => 87.9,
                'win_rate_contribution' => 85.8,
            ],
            'Silence Suzuka [Beyond the Brilliant Scenery]' => [
                'meta_tier' => 'S',
                'card_type' => 'speed',
                'training_effect_bonus' => 8,
                'friendship_bonus' => 20,
                'skill_hints_provided' => ['Front Runner Savvy', 'Unrestrained', 'Focus'],
                'unique_effects' => ['Large amount of Front Runner yellow skills'],
                'strategic_notes' => ['Specialized for Front Runner style'],
                'usage_rate' => 84.1,
                'win_rate_contribution' => 82.4,
            ],
            'Narita Brian [Two Pieces]' => [
                'meta_tier' => 'S',
                'card_type' => 'speed',
                'training_effect_bonus' => 8,
                'friendship_bonus' => 18,
                'skill_hints_provided' => ['Lone Wolf', 'Medium-Long distance skills'],
                'unique_effects' => ['Easy-to-get story Speed Card'],
                'strategic_notes' => ['Gives Lone Wolf', 'Good for Pace Chasers'],
                'usage_rate' => 82.3,
                'win_rate_contribution' => 80.7,
            ],

            // A-Tier Cards
            'Special Week [Japan\'s Number 1 Stage]' => [
                'meta_tier' => 'A',
                'card_type' => 'speed',
                'training_effect_bonus' => 7,
                'friendship_bonus' => 18,
                'skill_hints_provided' => ['Gourmand', 'Recovery skills'],
                'strategic_notes' => ['All-rounder card', 'Good for Pace Chasers'],
                'usage_rate' => 76.8,
                'win_rate_contribution' => 74.5,
            ],
            'Tokai Teio [Let Your Dreams Be Known!]' => [
                'meta_tier' => 'A',
                'card_type' => 'speed',
                'training_effect_bonus' => 7,
                'friendship_bonus' => 18,
                'skill_hints_provided' => ['Rushing Gale!', 'Pace Chaser skills'],
                'strategic_notes' => ['Good for Pace Chaser style'],
                'usage_rate' => 75.2,
                'win_rate_contribution' => 73.1,
            ],
            'El Condor Pasa [Passion Campeóna!]' => [
                'meta_tier' => 'A',
                'card_type' => 'power',
                'training_effect_bonus' => 8,
                'friendship_bonus' => 18,
                'skill_hints_provided' => ['Killer Tunes', 'Hawkeye'],
                'strategic_notes' => ['Good for Medium-focused Pace Chasers'],
                'usage_rate' => 74.6,
                'win_rate_contribution' => 72.8,
            ],
            'Mejiro McQueen [As the Ace]' => [
                'meta_tier' => 'A',
                'card_type' => 'stamina',
                'training_effect_bonus' => 7,
                'friendship_bonus' => 18,
                'skill_hints_provided' => ['Cooldown', 'Recovery skills', 'Long distance skills'],
                'strategic_notes' => ['Essential for Long distance builds'],
                'usage_rate' => 73.4,
                'win_rate_contribution' => 71.6,
            ],
            'Twin Turbo [Turbo Engine, Full Power!]' => [
                'meta_tier' => 'A',
                'card_type' => 'speed',
                'training_effect_bonus' => 7,
                'friendship_bonus' => 18,
                'skill_hints_provided' => ['Moxie', 'Leader\'s Pride', 'Taking the Lead'],
                'strategic_notes' => ['Useful event skills for Front Runners'],
                'usage_rate' => 72.1,
                'win_rate_contribution' => 70.3,
            ],
        ];
    }

    /**
     * Seed only meta cards (fallback when API fails)
     */
    protected function seedMetaCardsOnly(): void
    {
        $this->command->info('Seeding meta cards from static data...');

        $metaCards = $this->getMetaEnrichmentData();
        $synced = 0;

        foreach ($metaCards as $cardName => $data) {
            try {
                $internalId = 'META_'.strtoupper(preg_replace('/[^a-zA-Z0-9]/', '_', $cardName) ?? '');

                SupportCardDefinition::updateOrCreate(
                    ['internal_id' => $internalId],
                    array_merge([
                        'name' => $cardName,
                        'internal_id' => $internalId,
                        'rarity' => 'SSR',
                        'is_active' => true,
                        'server_availability' => 'global',
                    ], $data)
                );

                $synced++;
            } catch (\Exception $e) {
                Log::warning('[UcpSupportCardsSeeder] Failed to seed meta card', [
                    'card' => $cardName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->command->info("Seeded {$synced} meta cards.");
    }

    /**
     * Show breakdown by rarity
     */
    protected function showRarityBreakdown(): void
    {
        $counts = SupportCardDefinition::query()
            ->selectRaw('rarity, COUNT(*) as count')
            ->groupBy('rarity')
            ->pluck('count', 'rarity')
            ->toArray();

        $this->command->newLine();
        $this->command->info('Cards by Rarity:');
        foreach ($counts as $rarity => $count) {
            $this->command->info("  - {$rarity}: {$count}");
        }
    }

    /**
     * Determine rarity from card ID
     */
    protected function determineRarity(int $cardId): string
    {
        if ($cardId >= 30000) {
            return 'SSR';
        }
        if ($cardId >= 20000) {
            return 'SR';
        }

        return 'R';
    }

    /**
     * Determine card type from character ID
     */
    protected function determineCardType(int $charaId, string $gametoraId): string
    {
        // Support characters are Friend type
        if ($charaId >= 9000) {
            return 'friend';
        }

        // Character-based type inference
        $speedCharacters = [1002, 1003, 1006, 1041, 1046, 1066, 1068];
        $staminaCharacters = [1013, 1025, 1030, 1045, 1058];
        $powerCharacters = [1007, 1008, 1014, 1050];
        $gutsCharacters = [1052, 1055, 1065];
        $witCharacters = [1017, 1022, 1032];

        if (\in_array($charaId, $speedCharacters, true)) {
            return 'speed';
        }
        if (\in_array($charaId, $staminaCharacters, true)) {
            return 'stamina';
        }
        if (\in_array($charaId, $powerCharacters, true)) {
            return 'power';
        }
        if (\in_array($charaId, $gutsCharacters, true)) {
            return 'guts';
        }
        if (\in_array($charaId, $witCharacters, true)) {
            return 'wit';
        }

        return 'speed';
    }

    /**
     * Get default meta tier based on rarity
     */
    protected function getDefaultMetaTier(string $rarity): string
    {
        return match ($rarity) {
            'SSR' => 'B',
            'SR' => 'C',
            default => 'C',
        };
    }
}
