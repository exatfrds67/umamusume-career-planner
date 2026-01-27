<?php

namespace Database\Seeders;

use App\Models\ExternalData;
use Illuminate\Database\Seeder;

class ExternalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample characters data for offline testing
        $charactersData = [
            [
                'id' => 1,
                'name' => 'Special Week',
                'name_jp' => 'スペシャルウィーク',
                'rarity' => 3,
                'image_url' => '/images/characters/special_week.png',
                'stats' => [
                    'speed' => 'A',
                    'stamina' => 'B',
                    'power' => 'A',
                    'guts' => 'B',
                    'wisdom' => 'C',
                ],
            ],
            [
                'id' => 2,
                'name' => 'Silence Suzuka',
                'name_jp' => 'サイレンススズカ',
                'rarity' => 3,
                'image_url' => '/images/characters/silence_suzuka.png',
                'stats' => [
                    'speed' => 'A',
                    'stamina' => 'A',
                    'power' => 'B',
                    'guts' => 'C',
                    'wisdom' => 'B',
                ],
            ],
            [
                'id' => 3,
                'name' => 'Tokai Teio',
                'name_jp' => 'トウカイテイオー',
                'rarity' => 3,
                'image_url' => '/images/characters/tokai_teio.png',
                'stats' => [
                    'speed' => 'A',
                    'stamina' => 'B',
                    'power' => 'A',
                    'guts' => 'A',
                    'wisdom' => 'C',
                ],
            ],
        ];

        // Sample support cards data
        $supportCardsData = [
            [
                'id' => 1,
                'name' => '[Starting Gate] Special Week',
                'name_jp' => '[スタートライン]スペシャルウィーク',
                'type' => 'speed',
                'rarity' => 'SSR',
                'image_url' => '/images/support_cards/special_week_ssr.png',
                'effects' => [
                    'speed_bonus' => 10,
                    'training_effect' => 15,
                ],
            ],
            [
                'id' => 2,
                'name' => '[Unmei no Deai] Silence Suzuka',
                'name_jp' => '[運命の出会い]サイレンススズカ',
                'type' => 'stamina',
                'rarity' => 'SSR',
                'image_url' => '/images/support_cards/silence_suzuka_ssr.png',
                'effects' => [
                    'stamina_bonus' => 10,
                    'training_effect' => 15,
                ],
            ],
        ];

        // Sample news data
        $newsData = [
            [
                'id' => 1,
                'title' => 'New Character Release: Rice Shower',
                'title_jp' => '新キャラクター実装：ライスシャワー',
                'content' => 'Rice Shower has been added to the game with new storylines and events.',
                'date' => '2024-01-15',
                'category' => 'character_release',
            ],
            [
                'id' => 2,
                'title' => 'Training Event: Winter Cup',
                'title_jp' => 'トレーニングイベント：ウィンターカップ',
                'content' => 'Special winter training event with bonus rewards.',
                'date' => '2024-01-10',
                'category' => 'event',
            ],
        ];

        // Create external data records
        ExternalData::updateOrCreate(
            [
                'data_source' => 'umapyoi',
                'data_type' => 'characters',
                'data_key' => 'api_cache',
            ],
            [
                'data_content' => $charactersData,
                'data_version' => '1.0',
                'data_description' => 'Cached characters data from umapyoi.net API',
                'last_fetched_at' => now(),
                'expires_at' => now()->addDay(),
                'is_active' => true,
                'is_deprecated' => false,
                'is_validated' => true,
                'confidence_score' => 1.0,
                'data_quality' => 'good',
                'fetch_metadata' => [
                    'records_count' => count($charactersData),
                    'cached_by' => 'ExternalDataSeeder',
                    'cache_timestamp' => now()->toISOString(),
                ],
            ]
        );

        ExternalData::updateOrCreate(
            [
                'data_source' => 'umapyoi',
                'data_type' => 'support_cards',
                'data_key' => 'api_cache',
            ],
            [
                'data_content' => $supportCardsData,
                'data_version' => '1.0',
                'data_description' => 'Cached support cards data from umapyoi.net API',
                'last_fetched_at' => now(),
                'expires_at' => now()->addDay(),
                'is_active' => true,
                'is_deprecated' => false,
                'is_validated' => true,
                'confidence_score' => 1.0,
                'data_quality' => 'good',
                'fetch_metadata' => [
                    'records_count' => count($supportCardsData),
                    'cached_by' => 'ExternalDataSeeder',
                    'cache_timestamp' => now()->toISOString(),
                ],
            ]
        );

        ExternalData::updateOrCreate(
            [
                'data_source' => 'umapyoi',
                'data_type' => 'news',
                'data_key' => 'api_cache',
            ],
            [
                'data_content' => $newsData,
                'data_version' => '1.0',
                'data_description' => 'Cached news data from umapyoi.net API',
                'last_fetched_at' => now(),
                'expires_at' => now()->addDay(),
                'is_active' => true,
                'is_deprecated' => false,
                'is_validated' => true,
                'confidence_score' => 1.0,
                'data_quality' => 'good',
                'fetch_metadata' => [
                    'records_count' => count($newsData),
                    'cached_by' => 'ExternalDataSeeder',
                    'cache_timestamp' => now()->toISOString(),
                ],
            ]
        );

        $this->command->info('External data seeded successfully!');
        $this->command->info('- Characters: '.count($charactersData).' records');
        $this->command->info('- Support Cards: '.count($supportCardsData).' records');
        $this->command->info('- News: '.count($newsData).' records');
    }
}
