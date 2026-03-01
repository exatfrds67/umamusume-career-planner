<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GameCharacter;
use App\Models\GameRace;
use Illuminate\Database\Seeder;

/**
 * Seeds the game character catalog and each character's target race associations.
 *
 * Race type legend:
 *  required    — game forces the run (e.g. debut)
 *  goal        — character's dream race; winning triggers major story content
 *  story       — unlocks character-specific cutscenes or events
 *  recommended — historically fits the character's distance/style archetype
 *
 * Target format: [race_slug, race_type, priority, notes|null]
 *  Lower priority = more important (1 = highest).
 */
class GameCharacterSeeder extends Seeder
{
    public function run(): void
    {
        $races = GameRace::query()->pluck('id', 'slug');

        foreach ($this->characters() as $data) {
            $targets = $data['targets'] ?? [];
            unset($data['targets']);

            $character = GameCharacter::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            $pivotData = [];
            foreach ($targets as [$raceSlug, $raceType, $priority, $notes]) {
                if (! $races->has($raceSlug)) {
                    continue;
                }
                $pivotData[$races[$raceSlug]] = [
                    'race_type' => $raceType,
                    'priority' => $priority,
                    'notes' => $notes,
                ];
            }

            $character->targetRaces()->sync($pivotData);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function characters(): array
    {
        return [

            // ── SPECIAL WEEK ─────────────────────────────────────────────
            [
                'slug' => 'special-week',
                'name_en' => 'Special Week',
                'name_jp' => 'スペシャルウィーク',
                'title' => 'Our June Bug',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Special Week',
                'targets' => [
                    ['japan-derby', 'goal', 1, "Her dream — become Japan's No. 1 horse"],
                    ['japan-cup', 'goal', 2, 'Beat all of Japan and the world'],
                    ['tenno-sho-autumn', 'story', 3, 'Key rivalry event'],
                    ['arima-kinen', 'story', 4, 'Year-end climax'],
                    ['kikuka-sho', 'recommended', 5, 'Triple Crown third leg'],
                    ['takarazuka-kinen', 'recommended', 6, 'Mid-year prestige target'],
                ],
            ],

            // ── SILENCE SUZUKA ────────────────────────────────────────────
            [
                'slug' => 'silence-suzuka',
                'name_en' => 'Silence Suzuka',
                'name_jp' => 'サイレンススズカ',
                'title' => 'The Indomitable Spirit',
                'primary_distance' => 'mile',
                'preferred_style' => 'escape',
                'real_horse_name' => 'Silence Suzuka',
                'targets' => [
                    ['takarazuka-kinen', 'goal', 1, 'Story-critical race (real-life Tenno Sho accident reimagined)'],
                    ['nhk-mile-cup', 'story', 2, 'Classic mile triumph'],
                    ['japan-cup', 'story', 3, 'Her ultimate dream confrontation'],
                    ['mile-championship', 'recommended', 4, 'Natural mile specialist target'],
                    ['victoria-mile', 'recommended', 5, 'Spring G1 mile fit'],
                ],
            ],

            // ── TOKAI TEIO ────────────────────────────────────────────────
            [
                'slug' => 'tokai-teio',
                'name_en' => 'Tokai Teio',
                'name_jp' => 'トウカイテイオー',
                'title' => 'The Indomitable Warrior',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Tokai Teio',
                'targets' => [
                    ['satsuki-sho', 'goal', 1, 'Triple Crown attempt begins here'],
                    ['japan-derby', 'goal', 2, 'Triple Crown second leg — comeback story pivot'],
                    ['osaka-hai', 'goal', 3, 'Comeback race after long injury'],
                    ['arima-kinen', 'story', 4, 'Legendary comeback Arima win'],
                    ['tenno-sho-spring', 'recommended', 5, 'Long-distance showcase'],
                ],
            ],

            // ── NARITA BRIAN ─────────────────────────────────────────────
            [
                'slug' => 'narita-brian',
                'name_en' => 'Narita Brian',
                'name_jp' => 'ナリタブライアン',
                'title' => 'Shadow of the Century',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Narita Brian',
                'targets' => [
                    ['satsuki-sho', 'goal', 1, 'Triple Crown leg one'],
                    ['japan-derby', 'goal', 2, 'Triple Crown leg two'],
                    ['kikuka-sho', 'goal', 3, 'Triple Crown completion'],
                    ['tenno-sho-spring', 'story', 4, 'Post-injury comeback stamina test'],
                    ['arima-kinen', 'story', 5, 'Year-end championship goal'],
                    ['takarazuka-kinen', 'recommended', 6, 'Mid-year G1 prestige'],
                ],
            ],

            // ── BIWA HAYAHIDE ─────────────────────────────────────────────
            [
                'slug' => 'biwa-hayahide',
                'name_en' => 'Biwa Hayahide',
                'name_jp' => 'ビワハヤヒデ',
                'title' => 'Twin Star',
                'primary_distance' => 'long',
                'preferred_style' => 'insert',
                'real_horse_name' => 'Biwa Hayahide',
                'targets' => [
                    ['japan-derby', 'goal', 1, 'Sibling rivalry with Narita Brian'],
                    ['kikuka-sho', 'goal', 2, 'Real-life win — stamina showcase'],
                    ['tenno-sho-spring', 'story', 3, 'Long-distance pinnacle'],
                    ['takarazuka-kinen', 'story', 4, 'Real-life win'],
                    ['arima-kinen', 'recommended', 5, 'Year-end goal'],
                    ['satsuki-sho', 'recommended', 6, 'Narita Brian rivalry begins'],
                ],
            ],

            // ── VODKA ─────────────────────────────────────────────────────
            [
                'slug' => 'vodka',
                'name_en' => 'Vodka',
                'name_jp' => 'ウオッカ',
                'title' => 'The Brave One',
                'primary_distance' => 'medium',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Vodka',
                'targets' => [
                    ['japan-derby', 'goal', 1, 'Historic win against males in 2007'],
                    ['tenno-sho-autumn', 'goal', 2, 'Real-life consecutive wins — story rivalry with Daiwa Scarlet'],
                    ['victoria-mile', 'story', 3, 'Filly G1 showcase'],
                    ['mile-championship', 'story', 4, 'Mile specialist peak'],
                    ['arima-kinen', 'recommended', 5, 'Year-end prestige target'],
                ],
            ],

            // ── DAIWA SCARLET ─────────────────────────────────────────────
            [
                'slug' => 'daiwa-scarlet',
                'name_en' => 'Daiwa Scarlet',
                'name_jp' => 'ダイワスカーレット',
                'title' => 'The Red Challenge',
                'primary_distance' => 'medium',
                'preferred_style' => 'escape',
                'real_horse_name' => 'Daiwa Scarlet',
                'targets' => [
                    ['satsuki-sho', 'goal', 1, 'Real-life Satsuki Sho win vs Vodka'],
                    ['victoria-mile', 'goal', 2, 'Key story race showcasing her style'],
                    ['tenno-sho-autumn', 'story', 3, 'Intense rivalry with Vodka'],
                    ['queen-elizabeth-cup', 'story', 4, 'Autumn filly championship'],
                    ['arima-kinen', 'recommended', 5, 'Year-end undefeated dream'],
                ],
            ],

            // ── GOLD SHIP ─────────────────────────────────────────────────
            [
                'slug' => 'gold-ship',
                'name_en' => 'Gold Ship',
                'name_jp' => 'ゴールドシップ',
                'title' => 'The Unruly God',
                'primary_distance' => 'long',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Gold Ship',
                'targets' => [
                    ['satsuki-sho', 'goal', 1, 'Real-life win by 5 lengths'],
                    ['kikuka-sho', 'goal', 2, 'Real-life win'],
                    ['tenno-sho-spring', 'story', 3, 'Real-life 2x winner — unpredictable fashion'],
                    ['takarazuka-kinen', 'story', 4, 'Real-life win — chaos included'],
                    ['arima-kinen', 'story', 5, 'Real-life 3 wins — year-end legend'],
                ],
            ],

            // ── MEJIRO MCQUEEN ────────────────────────────────────────────
            [
                'slug' => 'mejiro-mcqueen',
                'name_en' => 'Mejiro McQueen',
                'name_jp' => 'メジロマックイーン',
                'title' => 'Noble Lineage',
                'primary_distance' => 'long',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Mejiro McQueen',
                'targets' => [
                    ['tenno-sho-spring', 'goal', 1, 'Real-life 3x consecutive winner'],
                    ['kikuka-sho', 'story', 2, 'Classic triumph'],
                    ['takarazuka-kinen', 'story', 3, 'Mid-year G1 win'],
                    ['arima-kinen', 'recommended', 4, 'Year-end crowd favourite'],
                    ['hanshin-daishogai', 'recommended', 5, 'Long-distance warm-up in senior year'],
                ],
            ],

            // ── AIR GROOVE ────────────────────────────────────────────────
            [
                'slug' => 'air-groove',
                'name_en' => 'Air Groove',
                'name_jp' => 'エアグルーヴ',
                'title' => 'Queen of the Turf',
                'primary_distance' => 'medium',
                'preferred_style' => 'insert',
                'real_horse_name' => 'Air Groove',
                'targets' => [
                    ['japan-derby', 'goal', 1, 'Real-life win — she beat the colts'],
                    ['tenno-sho-autumn', 'goal', 2, 'Real-life consecutive wins'],
                    ['queen-elizabeth-cup', 'story', 3, 'Autumn filly crown'],
                    ['arima-kinen', 'story', 4, 'Year-end prestige'],
                    ['osaka-hai', 'recommended', 5, 'Senior medium G1'],
                ],
            ],

            // ── EL CONDOR PASA ────────────────────────────────────────────
            [
                'slug' => 'el-condor-pasa',
                'name_en' => 'El Condor Pasa',
                'name_jp' => 'エルコンドルパサー',
                'title' => 'The Unbeatable King',
                'primary_distance' => 'medium',
                'preferred_style' => 'insert',
                'real_horse_name' => 'El Condor Pasa',
                'targets' => [
                    ['nhk-mile-cup', 'goal', 1, 'Classic G1 mile dominance'],
                    ['japan-cup', 'goal', 2, 'Ultimate goal — beat the world at Japan Cup'],
                    ['takarazuka-kinen', 'story', 3, 'Mid-year story event'],
                    ['mile-championship', 'recommended', 4, 'Natural mile target'],
                ],
            ],

            // ── GRASS WONDER ─────────────────────────────────────────────
            [
                'slug' => 'grass-wonder',
                'name_en' => 'Grass Wonder',
                'name_jp' => 'グラスワンダー',
                'title' => 'Silver Bullet',
                'primary_distance' => 'medium',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Grass Wonder',
                'targets' => [
                    ['arima-kinen', 'goal', 1, 'Real-life 2 wins — story centrepiece'],
                    ['asahi-hai-futurity', 'story', 2, 'Dominant junior G1 debut story'],
                    ['takarazuka-kinen', 'story', 3, 'Mid-year rivalry with Special Week'],
                    ['japan-cup', 'recommended', 4, 'High-profile late season target'],
                ],
            ],

            // ── OGURI CAP ─────────────────────────────────────────────────
            [
                'slug' => 'oguri-cap',
                'name_en' => 'Oguri Cap',
                'name_jp' => 'オグリキャップ',
                'title' => "The People's Hero",
                'primary_distance' => 'medium',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Oguri Cap',
                'targets' => [
                    ['arima-kinen', 'goal', 1, 'Legendary retirement comeback win'],
                    ['tenno-sho-autumn', 'story', 2, 'Major career G1 win'],
                    ['nhk-mile-cup', 'story', 3, 'Versatile distance showcase'],
                    ['mile-championship', 'recommended', 4, 'Mile fan-favourite target'],
                    ['japan-cup', 'recommended', 5, 'Prestige long-season goal'],
                ],
            ],

            // ── SYMBOLI RUDOLF ────────────────────────────────────────────
            [
                'slug' => 'symboli-rudolf',
                'name_en' => 'Symboli Rudolf',
                'name_jp' => 'シンボリルドルフ',
                'title' => 'Emperor of the Turf',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Symboli Rudolf',
                'targets' => [
                    ['satsuki-sho', 'goal', 1, 'Triple Crown first leg — undefeated run'],
                    ['japan-derby', 'goal', 2, 'Triple Crown second leg'],
                    ['kikuka-sho', 'goal', 3, 'Triple Crown completion — Grand Slam goal'],
                    ['tenno-sho-autumn', 'story', 4, 'Senior dominance'],
                    ['japan-cup', 'story', 5, 'International prestige beat'],
                    ['arima-kinen', 'story', 6, 'Year-end crown'],
                ],
            ],

            // ── TAIKI SHUTTLE ─────────────────────────────────────────────
            [
                'slug' => 'taiki-shuttle',
                'name_en' => 'Taiki Shuttle',
                'name_jp' => 'タイキシャトル',
                'title' => 'The Tactician',
                'primary_distance' => 'mile',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Taiki Shuttle',
                'targets' => [
                    ['nhk-mile-cup', 'goal', 1, 'Classic mile G1 target'],
                    ['mile-championship', 'goal', 2, 'Real-life win — definitive mile champion'],
                    ['sprinters-stakes', 'story', 3, 'Showed sprint capability too'],
                    ['victoria-mile', 'recommended', 4, 'Spring G1 mile fit'],
                ],
            ],

            // ── AGNES TACHYON ─────────────────────────────────────────────
            [
                'slug' => 'agnes-tachyon',
                'name_en' => 'Agnes Tachyon',
                'name_jp' => 'アグネスタキオン',
                'title' => 'Mad Scientist',
                'primary_distance' => 'medium',
                'preferred_style' => 'escape',
                'real_horse_name' => 'Agnes Tachyon',
                'targets' => [
                    ['satsuki-sho', 'goal', 1, 'Undefeated streak ends in glorious win before retirement'],
                    ['yayoi-sho', 'story', 2, 'Important Classic prep race — real-life dominant win'],
                    ['japan-derby', 'recommended', 3, 'Dream race she never ran (retired after Satsuki Sho)'],
                ],
            ],

            // ── SAKURA BAKUSHIN O ─────────────────────────────────────────
            [
                'slug' => 'sakura-bakushin-o',
                'name_en' => 'Sakura Bakushin O',
                'name_jp' => 'サクラバクシンオー',
                'title' => 'Sprint King',
                'primary_distance' => 'sprint',
                'preferred_style' => 'escape',
                'real_horse_name' => 'Sakura Bakushin O',
                'targets' => [
                    ['sprinters-stakes', 'goal', 1, 'Real-life 2x consecutive Sprinters Stakes winner'],
                    ['asahi-hai-futurity', 'story', 2, 'Junior G1 showcase — dominated the mile sprint'],
                    ['mile-championship', 'recommended', 3, 'Tried but heart is in the sprint'],
                ],
            ],

            // ── RICE SHOWER ───────────────────────────────────────────────
            [
                'slug' => 'rice-shower',
                'name_en' => 'Rice Shower',
                'name_jp' => 'ライスシャワー',
                'title' => 'The Black Assassin',
                'primary_distance' => 'long',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Rice Shower',
                'targets' => [
                    ['kikuka-sho', 'goal', 1, 'Upset win over Mihono Bourbon — Triple Crown derailed'],
                    ['tenno-sho-spring', 'goal', 2, 'Real-life 2x winner — stamina pinnacle'],
                    ['arima-kinen', 'story', 3, 'Long-distance year-end target'],
                    ['takarazuka-kinen', 'recommended', 4, 'Medium-long prestige target'],
                ],
            ],

            // ── MIHONO BOURBON ────────────────────────────────────────────
            [
                'slug' => 'mihono-bourbon',
                'name_en' => 'Mihono Bourbon',
                'name_jp' => 'ミホノブルボン',
                'title' => 'The Machine',
                'primary_distance' => 'medium',
                'preferred_style' => 'escape',
                'real_horse_name' => 'Mihono Bourbon',
                'targets' => [
                    ['satsuki-sho', 'goal', 1, 'Triple Crown leg one — perfect pace'],
                    ['japan-derby', 'goal', 2, 'Triple Crown leg two'],
                    ['kikuka-sho', 'goal', 3, 'Triple Crown broken by Rice Shower'],
                    ['arima-kinen', 'recommended', 4, 'Year-end challenge'],
                ],
            ],

            // ── T.M. OPERA O ─────────────────────────────────────────────
            [
                'slug' => 't-m-opera-o',
                'name_en' => 'T.M. Opera O',
                'name_jp' => 'T.M.オペラオー',
                'title' => 'Emperor of the Millennium',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'T.M. Opera O',
                'targets' => [
                    ['satsuki-sho', 'goal', 1, 'Classic G1 — Millennium Grand Slam opener'],
                    ['japan-derby', 'goal', 2, 'Millennium Grand Slam race two'],
                    ['tenno-sho-autumn', 'goal', 3, 'Millennium sweep central race'],
                    ['japan-cup', 'goal', 4, 'Millennium Grand Slam G1'],
                    ['arima-kinen', 'goal', 5, 'Grand Slam final win — Year 2000'],
                    ['tenno-sho-spring', 'story', 6, 'Year 3 crown — defense of spring title'],
                    ['takarazuka-kinen', 'story', 7, 'Mid-year prestige win'],
                    ['osaka-hai', 'recommended', 8, 'Senior year opener'],
                ],
            ],

            // ── MARUZENSKY ────────────────────────────────────────────────
            [
                'slug' => 'maruzensky',
                'name_en' => 'Maruzensky',
                'name_jp' => 'マルゼンスキー',
                'title' => 'The Uncrowned King',
                'primary_distance' => 'mile',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Maruzensky',
                'targets' => [
                    ['asahi-hai-futurity', 'goal', 1, 'Junior G1 — undefeated showcase'],
                    ['hopeful-stakes', 'story', 2, 'Junior G1 medium distance win'],
                    ['nhk-mile-cup', 'recommended', 3, 'Classic mile mastery'],
                    ['mile-championship', 'recommended', 4, 'Natural distance fit'],
                ],
            ],

            // ── ADMIRE VEGA ───────────────────────────────────────────────
            [
                'slug' => 'admire-vega',
                'name_en' => 'Admire Vega',
                'name_jp' => 'アドマイヤベガ',
                'title' => 'Child of the Stars',
                'primary_distance' => 'medium',
                'preferred_style' => 'insert',
                'real_horse_name' => 'Admire Vega',
                'targets' => [
                    ['japan-derby', 'goal', 1, 'Real-life win — her defining moment'],
                    ['satsuki-sho', 'story', 2, 'Classic journey begins'],
                    ['kikuka-sho', 'recommended', 3, 'Triple Crown final leg aim'],
                ],
            ],

            // ── AGNES DIGITAL ─────────────────────────────────────────────
            [
                'slug' => 'agnes-digital',
                'name_en' => 'Agnes Digital',
                'name_jp' => 'アグネスデジタル',
                'title' => 'All-Surface Idol',
                'primary_distance' => 'mile',
                'preferred_style' => 'insert',
                'real_horse_name' => 'Agnes Digital',
                'targets' => [
                    ['mile-championship', 'goal', 1, 'Real-life win — all-rounder peak'],
                    ['sprinters-stakes', 'story', 2, 'Real-life sprint G1 win'],
                    ['nhk-mile-cup', 'story', 3, 'Classic year mile showcase'],
                    ['victoria-mile', 'recommended', 4, 'Spring G1 mile fit'],
                    ['tenno-sho-autumn', 'recommended', 5, 'Versatile medium distance aim'],
                ],
            ],

            // ── KING HALO ─────────────────────────────────────────────────
            [
                'slug' => 'king-halo',
                'name_en' => 'King Halo',
                'name_jp' => 'キングヘイロー',
                'title' => 'The Eccentric King',
                'primary_distance' => 'mile',
                'preferred_style' => 'escape',
                'real_horse_name' => 'King Halo',
                'targets' => [
                    ['mile-championship', 'goal', 1, 'Real-life dramatic comeback win'],
                    ['nhk-mile-cup', 'story', 2, 'Classic year mile target'],
                    ['satsuki-sho', 'story', 3, 'Early story arc — trying to be a classic horse'],
                    ['japan-derby', 'recommended', 4, 'His attempt at the big medium-distance races'],
                ],
            ],

            // ── SEIUN SKY ─────────────────────────────────────────────────
            [
                'slug' => 'seiun-sky',
                'name_en' => 'Seiun Sky',
                'name_jp' => 'セイウンスカイ',
                'title' => 'Clear Blue Sky',
                'primary_distance' => 'medium',
                'preferred_style' => 'escape',
                'real_horse_name' => 'Seiun Sky',
                'targets' => [
                    ['satsuki-sho', 'goal', 1, 'Real-life win — frontrunner dominance'],
                    ['kikuka-sho', 'goal', 2, 'Real-life win — Triple Crown two-thirds complete'],
                    ['yayoi-sho', 'story', 3, 'Classic prep — important lead-up'],
                    ['tenno-sho-spring', 'recommended', 4, 'Stamina-heavy target for his style'],
                ],
            ],

            // ── NARITA TAISHIN ────────────────────────────────────────────
            [
                'slug' => 'narita-taishin',
                'name_en' => 'Narita Taishin',
                'name_jp' => 'ナリタタイシン',
                'title' => 'The Underdog',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Narita Taishin',
                'targets' => [
                    ['satsuki-sho', 'goal', 1, 'Real-life upset win over Biwa Hayahide'],
                    ['arima-kinen', 'story', 2, 'Real-life Arima win — underdog triumph'],
                    ['japan-derby', 'recommended', 3, 'Classic season second leg aim'],
                ],
            ],

            // ── MEJIRO RYAN ───────────────────────────────────────────────
            [
                'slug' => 'mejiro-ryan',
                'name_en' => 'Mejiro Ryan',
                'name_jp' => 'メジロライアン',
                'title' => 'Chasing the Sun',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Mejiro Ryan',
                'targets' => [
                    ['takarazuka-kinen', 'goal', 1, 'Real-life win — her crowning achievement'],
                    ['arima-kinen', 'story', 2, 'Chasing Mejiro McQueen here'],
                    ['osaka-hai', 'recommended', 3, 'Senior medium distance target'],
                ],
            ],

            // ── NICE NATURE ───────────────────────────────────────────────
            [
                'slug' => 'nice-nature',
                'name_en' => 'Nice Nature',
                'name_jp' => 'ナイスネイチャー',
                'title' => 'Bronze Collector',
                'primary_distance' => 'medium',
                'preferred_style' => 'insert',
                'real_horse_name' => 'Nice Nature',
                'targets' => [
                    ['arima-kinen', 'goal', 1, '3rd place 3 consecutive years — legendary bronze story'],
                    ['satsuki-sho', 'story', 2, 'Classic rivalry race'],
                    ['japan-derby', 'recommended', 3, 'Classic target'],
                    ['kikuka-sho', 'recommended', 4, 'Third leg long-distance aim'],
                ],
            ],

            // ── MANHATTAN CAFE ────────────────────────────────────────────
            [
                'slug' => 'manhattan-cafe',
                'name_en' => 'Manhattan Cafe',
                'name_jp' => 'マンハッテンカフェ',
                'title' => 'The Night Wanderer',
                'primary_distance' => 'long',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Manhattan Cafe',
                'targets' => [
                    ['kikuka-sho', 'goal', 1, 'Real-life win — stamina king of her year'],
                    ['tenno-sho-spring', 'goal', 2, 'Real-life win — long-distance specialist'],
                    ['arima-kinen', 'story', 3, 'Year-end championship goal'],
                    ['takarazuka-kinen', 'recommended', 4, 'Medium-long G1 fit'],
                ],
            ],

            // ── MAYANO TOP GUN ────────────────────────────────────────────
            [
                'slug' => 'mayano-top-gun',
                'name_en' => 'Mayano Top Gun',
                'name_jp' => 'マヤノトップガン',
                'title' => 'The Wild One',
                'primary_distance' => 'long',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Mayano Top Gun',
                'targets' => [
                    ['tenno-sho-spring', 'goal', 1, 'Real-life win — epic frontrunner dominance'],
                    ['takarazuka-kinen', 'goal', 2, 'Real-life win'],
                    ['arima-kinen', 'goal', 3, 'Real-life win — year-end legend'],
                    ['kikuka-sho', 'story', 4, 'Classic stamina base'],
                    ['osaka-hai', 'recommended', 5, 'Senior medium G1'],
                ],
            ],

            // ── MEISHO DOTO ───────────────────────────────────────────────
            [
                'slug' => 'meisho-doto',
                'name_en' => 'Meisho Doto',
                'name_jp' => 'メイショウドトウ',
                'title' => 'The Eternal Second',
                'primary_distance' => 'medium',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Meisho Doto',
                'targets' => [
                    ['tenno-sho-autumn', 'goal', 1, 'Finally beat T.M. Opera O — defining win'],
                    ['arima-kinen', 'story', 2, 'Year-end rivalry conclusion'],
                    ['japan-cup', 'story', 3, 'T.M. Opera O era prestige race'],
                    ['osaka-hai', 'recommended', 4, 'Senior G1 — revenge story fuel'],
                ],
            ],

            // ── TWIN TURBO ────────────────────────────────────────────────
            [
                'slug' => 'twin-turbo',
                'name_en' => 'Twin Turbo',
                'name_jp' => 'ツインターボ',
                'title' => 'The Berserker',
                'primary_distance' => 'medium',
                'preferred_style' => 'escape',
                'real_horse_name' => 'Twin Turbo',
                'targets' => [
                    ['tenno-sho-spring', 'goal', 1, 'Legendary frontrunner upset win'],
                    ['takarazuka-kinen', 'story', 2, 'Mid-year escape strategy showcase'],
                    ['osaka-hai', 'recommended', 3, 'Senior medium G1 target'],
                ],
            ],

            // ── FINE MOTION ───────────────────────────────────────────────
            [
                'slug' => 'fine-motion',
                'name_en' => 'Fine Motion',
                'name_jp' => 'ファインモーション',
                'title' => 'The Prairie Wind',
                'primary_distance' => 'mile',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Fine Motion',
                'targets' => [
                    ['mile-championship', 'goal', 1, 'Real-life dominant win'],
                    ['queen-elizabeth-cup', 'story', 2, 'Autumn filly championship target'],
                    ['victoria-mile', 'recommended', 3, 'Spring G1 mile fit'],
                    ['shuka-sho', 'recommended', 4, 'Autumn classic filly race'],
                ],
            ],

            // ── KAWAKAMI PRINCESS ─────────────────────────────────────────
            [
                'slug' => 'kawakami-princess',
                'name_en' => 'Kawakami Princess',
                'name_jp' => 'カワカミプリンセス',
                'title' => 'The Midsummer Princess',
                'primary_distance' => 'medium',
                'preferred_style' => 'insert',
                'real_horse_name' => 'Kawakami Princess',
                'targets' => [
                    ['queen-elizabeth-cup', 'goal', 1, 'Real-life win — upset classic'],
                    ['shuka-sho', 'story', 2, 'Autumn filly classic target'],
                    ['takarazuka-kinen', 'recommended', 3, 'Mid-year prestige'],
                ],
            ],

            // ── IKUNO DICTUS ─────────────────────────────────────────────
            [
                'slug' => 'ikuno-dictus',
                'name_en' => 'Ikuno Dictus',
                'name_jp' => 'イクノディクタス',
                'title' => 'The Iron Mare',
                'primary_distance' => 'long',
                'preferred_style' => 'insert',
                'real_horse_name' => 'Ikuno Dictus',
                'targets' => [
                    ['queen-elizabeth-cup', 'goal', 1, 'Real-life win — stamina mare'],
                    ['takarazuka-kinen', 'story', 2, 'Medium-long prestige target'],
                    ['tenno-sho-autumn', 'recommended', 3, 'Medium G1 long-range fit'],
                ],
            ],

            // ── KITASAN BLACK ─────────────────────────────────────────────
            [
                'slug' => 'kitasan-black',
                'name_en' => 'Kitasan Black',
                'name_jp' => 'キタサンブラック',
                'title' => "The People's Champion",
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Kitasan Black',
                'targets' => [
                    ['osaka-hai', 'goal', 1, 'Real-life win — Senior opener'],
                    ['tenno-sho-spring', 'goal', 2, 'Real-life win — long stamina peak'],
                    ['japan-cup', 'goal', 3, 'Real-life win — international stage'],
                    ['arima-kinen', 'goal', 4, 'Real-life win — retirement finale'],
                    ['tenno-sho-autumn', 'story', 5, 'Real-life win — dominant run'],
                    ['takarazuka-kinen', 'story', 6, 'Mid-year prestige story'],
                    ['satsuki-sho', 'recommended', 7, 'Classic year opener'],
                ],
            ],

            // ── SATONO DIAMOND ────────────────────────────────────────────
            [
                'slug' => 'satono-diamond',
                'name_en' => 'Satono Diamond',
                'name_jp' => 'サトノダイヤモンド',
                'title' => 'The Diamond',
                'primary_distance' => 'long',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Satono Diamond',
                'targets' => [
                    ['tenno-sho-spring', 'goal', 1, 'Real-life win — stamina showcase'],
                    ['japan-cup', 'goal', 2, 'Real-life win — international glory'],
                    ['arima-kinen', 'story', 3, 'Year-end rivalry with Kitasan Black'],
                    ['kikuka-sho', 'recommended', 4, 'Classic stamina long race'],
                ],
            ],

            // ── NAKAYAMA FESTA ────────────────────────────────────────────
            [
                'slug' => 'nakayama-festa',
                'name_en' => 'Nakayama Festa',
                'name_jp' => 'ナカヤマフェスタ',
                'title' => 'Nakayama Specialist',
                'primary_distance' => 'medium',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Nakayama Festa',
                'targets' => [
                    ['takarazuka-kinen', 'goal', 1, 'Real-life surprise win'],
                    ['arima-kinen', 'story', 2, 'Real-life strong performance — Nakayama home track'],
                    ['japan-cup', 'recommended', 3, 'International challenger story'],
                ],
            ],

            // ── ZENNO ROB ROY ─────────────────────────────────────────────
            [
                'slug' => 'zenno-rob-roy',
                'name_en' => 'Zenno Rob Roy',
                'name_jp' => 'ゼンノロブロイ',
                'title' => 'Autumn Champion',
                'primary_distance' => 'medium',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Zenno Rob Roy',
                'targets' => [
                    ['tenno-sho-autumn', 'goal', 1, 'Real-life dominant autumn sweep'],
                    ['mile-championship', 'story', 2, 'Real-life win — mile versatility'],
                    ['arima-kinen', 'story', 3, 'Year-end story arc'],
                    ['japan-cup', 'recommended', 4, 'Prestigious autumn target'],
                ],
            ],

            // ── TOSEN JORDAN ─────────────────────────────────────────────
            [
                'slug' => 'tosen-jordan',
                'name_en' => 'Tosen Jordan',
                'name_jp' => 'トーセンジョーダン',
                'title' => 'The Tactician',
                'primary_distance' => 'mile',
                'preferred_style' => 'insert',
                'real_horse_name' => 'Tosen Jordan',
                'targets' => [
                    ['mile-championship', 'goal', 1, 'Real-life win — career peak'],
                    ['tenno-sho-autumn', 'goal', 2, 'Real-life win — medium distance range'],
                    ['japan-cup', 'story', 3, 'International prestige target'],
                    ['osaka-hai', 'recommended', 4, 'Senior G1 fit'],
                ],
            ],

            // ── HISHI AMAZON ─────────────────────────────────────────────
            [
                'slug' => 'hishi-amazon',
                'name_en' => 'Hishi Amazon',
                'name_jp' => 'ヒシアマゾン',
                'title' => 'The Indomitable Amazon',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Hishi Amazon',
                'targets' => [
                    ['takarazuka-kinen', 'goal', 1, 'Real-life win — filly vs males'],
                    ['arima-kinen', 'story', 2, 'Year-end aspirations'],
                    ['japan-derby', 'story', 3, 'Bold challenge — filly vs the colts'],
                    ['queen-elizabeth-cup', 'recommended', 4, 'Autumn filly crown'],
                ],
            ],

            // ── FUJI KISEKI ───────────────────────────────────────────────
            [
                'slug' => 'fuji-kiseki',
                'name_en' => 'Fuji Kiseki',
                'name_jp' => 'フジキセキ',
                'title' => 'The Destined One',
                'primary_distance' => 'mile',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Fuji Kiseki',
                'targets' => [
                    ['nhk-mile-cup', 'goal', 1, 'Real-life win — career peak before retirement'],
                    ['satsuki-sho', 'story', 2, 'Favoured Triple Crown contender before injury'],
                    ['mile-championship', 'recommended', 3, 'Natural mile specialist target'],
                ],
            ],

            // ── MEJIRO DOBER ─────────────────────────────────────────────
            [
                'slug' => 'mejiro-dober',
                'name_en' => 'Mejiro Dober',
                'name_jp' => 'メジロドーベル',
                'title' => 'The Resilient Contender',
                'primary_distance' => 'mile',
                'preferred_style' => 'insert',
                'real_horse_name' => 'Mejiro Dober',
                'targets' => [
                    ['mile-championship', 'goal', 1, 'Real-life 3 consecutive wins'],
                    ['nhk-mile-cup', 'story', 2, 'Classic year win'],
                    ['victoria-mile', 'recommended', 3, 'Spring G1 mile target'],
                ],
            ],

            // ── MR. C.B. ─────────────────────────────────────────────────
            [
                'slug' => 'mr-c-b',
                'name_en' => 'Mr. C.B.',
                'name_jp' => 'ミスターシービー',
                'title' => 'The Rebel',
                'primary_distance' => 'medium',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Mister C.B.',
                'targets' => [
                    ['satsuki-sho', 'goal', 1, 'Triple Crown first leg'],
                    ['japan-derby', 'goal', 2, 'Triple Crown second leg'],
                    ['kikuka-sho', 'goal', 3, 'Triple Crown third leg — Mihono Bourbon rival era'],
                    ['tenno-sho-autumn', 'recommended', 4, 'Medium G1 senior aim'],
                    ['japan-cup', 'recommended', 5, 'Prestigious senior target'],
                ],
            ],

            // ── NISHINO FLOWER ────────────────────────────────────────────
            [
                'slug' => 'nishino-flower',
                'name_en' => 'Nishino Flower',
                'name_jp' => 'ニシノフラワー',
                'title' => 'The Blooming Flower',
                'primary_distance' => 'mile',
                'preferred_style' => 'escape',
                'real_horse_name' => 'Nishino Flower',
                'targets' => [
                    ['nhk-mile-cup', 'goal', 1, 'Real-life win — classic mile'],
                    ['mile-championship', 'story', 2, 'Autumn mile championship aim'],
                    ['victoria-mile', 'recommended', 3, 'Spring G1 mile fit'],
                    ['sprinters-stakes', 'recommended', 4, 'Sprint range viable'],
                ],
            ],

            // ── SMART FALCON ─────────────────────────────────────────────
            [
                'slug' => 'smart-falcon',
                'name_en' => 'Smart Falcon',
                'name_jp' => 'スマートファルコン',
                'title' => 'JBC Dirt King',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Smart Falcon',
                'targets' => [
                    ['antares-stakes', 'goal', 1, 'Best available dirt target in the catalog'],
                    ['hyacinth-stakes', 'story', 2, 'Dirt mile Classic year opener'],
                    ['sprinters-stakes', 'recommended', 3, 'Most accessible G1 for non-standard runs'],
                ],
            ],

            // ── HARU URARA ────────────────────────────────────────────────
            [
                'slug' => 'haru-urara',
                'name_en' => 'Haru Urara',
                'name_jp' => 'ハルウララ',
                'title' => 'Never Give Up',
                'primary_distance' => 'sprint',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Haru Urara',
                'targets' => [
                    ['niiza-kinenkai', 'story', 1, 'She tries her best and bonds with supporters'],
                    ['baba-kinenkai', 'story', 2, 'Another brave attempt at Baba'],
                    ['kokura-nisai-stakes', 'recommended', 3, 'Sprint race in her range'],
                    ['sapporo-nisai-stakes', 'recommended', 4, 'Her story is about participating, not winning'],
                ],
            ],

            // ── MEJIRO PALMER ─────────────────────────────────────────────
            [
                'slug' => 'mejiro-palmer',
                'name_en' => 'Mejiro Palmer',
                'name_jp' => 'メジロパーマー',
                'title' => 'Front Running Guts',
                'primary_distance' => 'mile',
                'preferred_style' => 'escape',
                'real_horse_name' => 'Mejiro Palmer',
                'targets' => [
                    ['takarazuka-kinen', 'goal', 1, 'Real-life win in dominant frontrunner style'],
                    ['arima-kinen', 'story', 2, 'Escape artist at year end'],
                    ['mile-championship', 'recommended', 3, 'Mile specialist aim'],
                ],
            ],

            // ── MEJIRO ARDAN ─────────────────────────────────────────────
            [
                'slug' => 'mejiro-ardan',
                'name_en' => 'Mejiro Ardan',
                'name_jp' => 'メジロアルダン',
                'title' => 'Strong Finisher',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Mejiro Ardan',
                'targets' => [
                    ['arima-kinen', 'story', 1, 'His claim to fame — near-miss at Arima'],
                    ['mile-championship', 'story', 2, 'Real-life win — mile talent'],
                    ['japan-derby', 'recommended', 3, 'Classic aim'],
                    ['tenno-sho-spring', 'recommended', 4, 'Long-distance showcase'],
                ],
            ],

            // ── SHINKO WINDY ─────────────────────────────────────────────
            [
                'slug' => 'shinko-windy',
                'name_en' => 'Shinko Windy',
                'name_jp' => 'シンコウウインディ',
                'title' => 'The Wind Runner',
                'primary_distance' => 'mile',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Shinko Windy',
                'targets' => [
                    ['victoria-mile', 'goal', 1, 'Real-life Victoria Mile win'],
                    ['mile-championship', 'story', 2, 'Autumn mile crown aim'],
                    ['nhk-mile-cup', 'recommended', 3, 'Classic year mile fit'],
                ],
            ],

            // ── CURREN CHAN ───────────────────────────────────────────────
            [
                'slug' => 'curren-chan',
                'name_en' => 'Curren Chan',
                'name_jp' => 'カレンチャン',
                'title' => 'Sprint Idol',
                'primary_distance' => 'sprint',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Curren Chan',
                'targets' => [
                    ['sprinters-stakes', 'goal', 1, 'Real-life sprint G1 win'],
                    ['mile-championship', 'story', 2, 'Extended sprint range target'],
                    ['nhk-mile-cup', 'recommended', 3, 'Classic year option'],
                ],
            ],

            // ── YAENO MUTEKI ─────────────────────────────────────────────
            [
                'slug' => 'yaeno-muteki',
                'name_en' => 'Yaeno Muteki',
                'name_jp' => 'ヤエノムテキ',
                'title' => 'Invincible',
                'primary_distance' => 'medium',
                'preferred_style' => 'tracking',
                'real_horse_name' => 'Yaeno Muteki',
                'targets' => [
                    ['arima-kinen', 'goal', 1, 'Real-life win — year-end warrior'],
                    ['japan-derby', 'story', 2, 'Classic journey highlight'],
                    ['tenno-sho-autumn', 'recommended', 3, 'Medium G1 senior target'],
                ],
            ],

            // ── HISHI AKEBONO ─────────────────────────────────────────────
            [
                'slug' => 'hishi-akebono',
                'name_en' => 'Hishi Akebono',
                'name_jp' => 'ヒシアケボノ',
                'title' => 'The Rising Sun',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Hishi Akebono',
                'targets' => [
                    ['nhk-mile-cup', 'goal', 1, 'Real-life Classic win'],
                    ['japan-derby', 'story', 2, 'Classic second leg aim'],
                    ['tenno-sho-autumn', 'recommended', 3, 'Medium senior G1'],
                ],
            ],

            // ── INES FUJIN ────────────────────────────────────────────────
            [
                'slug' => 'ines-fujin',
                'name_en' => 'Ines Fujin',
                'name_jp' => 'イネスフジン',
                'title' => 'The Wind Goddess',
                'primary_distance' => 'sprint',
                'preferred_style' => 'escape',
                'real_horse_name' => 'Ines Fujin',
                'targets' => [
                    ['sprinters-stakes', 'goal', 1, 'Sprint specialist peak race'],
                    ['nhk-mile-cup', 'recommended', 2, 'Classic year longer option'],
                ],
            ],

            // ── MATIKANE FUKUKITARU ───────────────────────────────────────
            [
                'slug' => 'matikane-fukukitaru',
                'name_en' => 'Matikane Fukukitaru',
                'name_jp' => 'マチカネフクキタル',
                'title' => 'Good Luck Charm',
                'primary_distance' => 'medium',
                'preferred_style' => 'insert',
                'real_horse_name' => 'Matikane Fukukitaru',
                'targets' => [
                    ['satsuki-sho', 'story', 1, 'Classic era highlight'],
                    ['tenno-sho-autumn', 'recommended', 2, 'Medium G1 target'],
                    ['arima-kinen', 'recommended', 3, 'Year-end goal'],
                ],
            ],

            // ── MATIKANE TANNHAUSER ───────────────────────────────────────
            [
                'slug' => 'matikane-tannhauser',
                'name_en' => 'Matikane Tannhauser',
                'name_jp' => 'マチカネタンホイザ',
                'title' => 'Blessed Runner',
                'primary_distance' => 'medium',
                'preferred_style' => 'insert',
                'real_horse_name' => 'Matikane Tannhauser',
                'targets' => [
                    ['takarazuka-kinen', 'story', 1, 'Mid-year prestige target'],
                    ['tenno-sho-autumn', 'recommended', 2, 'Autumn medium G1'],
                    ['arima-kinen', 'recommended', 3, 'Year-end aim'],
                ],
            ],

            // ── SAKURA CHIYONO O ──────────────────────────────────────────
            [
                'slug' => 'sakura-chiyono-o',
                'name_en' => 'Sakura Chiyono O',
                'name_jp' => 'サクラチヨノオー',
                'title' => 'Cherry Blossom',
                'primary_distance' => 'mile',
                'preferred_style' => 'escape',
                'real_horse_name' => 'Sakura Chiyono O',
                'targets' => [
                    ['nhk-mile-cup', 'goal', 1, 'Real-life Classic win'],
                    ['mile-championship', 'recommended', 2, 'Autumn mile target'],
                ],
            ],

            // ── TAMAMO CROSS ─────────────────────────────────────────────
            [
                'slug' => 'tamamo-cross',
                'name_en' => 'Tamamo Cross',
                'name_jp' => 'タマモクロス',
                'title' => 'Countryside Legend',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Tamamo Cross',
                'targets' => [
                    ['tenno-sho-spring', 'goal', 1, 'Real-life win — beloved farm horse legend'],
                    ['takarazuka-kinen', 'story', 2, 'Real-life win — crowd favourite'],
                    ['arima-kinen', 'recommended', 3, 'Year-end goal'],
                ],
            ],

            // ── GOLD CITY ─────────────────────────────────────────────────
            [
                'slug' => 'gold-city',
                'name_en' => 'Gold City',
                'name_jp' => 'ゴールドシチー',
                'title' => 'Sprint Specialist',
                'primary_distance' => 'sprint',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Gold City',
                'targets' => [
                    ['sprinters-stakes', 'goal', 1, 'Real-life sprint G1 target'],
                    ['asahi-hai-futurity', 'story', 2, 'Junior sprint showcase'],
                    ['mile-championship', 'recommended', 3, 'Extended range attempt'],
                ],
            ],

            // ── YUKINO BIJIN ─────────────────────────────────────────────
            [
                'slug' => 'yukino-bijin',
                'name_en' => 'Yukino Bijin',
                'name_jp' => 'ユキノビジン',
                'title' => 'Snow Beauty',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Yukino Bijin',
                'targets' => [
                    ['tenno-sho-spring', 'story', 1, 'Long stamina race for her style'],
                    ['sapporo-kinen', 'recommended', 2, 'Sapporo summer — fits her snow namesake'],
                    ['arima-kinen', 'recommended', 3, 'Year-end endurance goal'],
                ],
            ],

            // ── WINNING TICKET ────────────────────────────────────────────
            [
                'slug' => 'winning-ticket',
                'name_en' => 'Winning Ticket',
                'name_jp' => 'ウイニングチケット',
                'title' => 'The Lucky Star',
                'primary_distance' => 'medium',
                'preferred_style' => 'leader',
                'real_horse_name' => 'Winning Ticket',
                'targets' => [
                    ['japan-derby', 'goal', 1, "Real-life win — the lucky star's defining race"],
                    ['satsuki-sho', 'story', 2, 'Narita Brian rivalry begins'],
                    ['kikuka-sho', 'recommended', 3, 'Triple Crown final leg aim'],
                    ['arima-kinen', 'recommended', 4, 'Year-end fan favourite goal'],
                ],
            ],

        ];
    }
}
