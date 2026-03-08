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
    private const DEBUT_RACE_SLUG = 'debut-race';

    private const ARIMA_KINEN_SLUG = 'arima-kinen';

    public function run(): void
    {
        $races = GameRace::query()->pluck('id', 'slug');

        foreach ($this->characters() as $data) {
            $data = $this->normalizeCharacterData($data);
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
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeCharacterData(array $data): array
    {
        $targets = $data['targets'] ?? [];
        $sources = $data['goal_race_sources'] ?? [];
        $notes = $data['notes'] ?? [];

        unset($data['goal_race_sources']);

        $data['targets'] = $this->normalizeTargets(is_array($targets) ? $targets : []);
        $data['notes'] = $this->buildNotes(
            is_array($notes) ? $notes : [],
            is_array($sources) ? $sources : []
        );

        return $data;
    }

    /**
     * @param  array<int, array<int, int|string|null>>  $targets
     * @return array<int, array{0: string, 1: string, 2: int, 3: string|null}>
     */
    private function normalizeTargets(array $targets): array
    {
        $debutTarget = [
            self::DEBUT_RACE_SLUG,
            'required',
            0,
            'Junior Make Debut — career start',
        ];

        $arimaTarget = [
            self::ARIMA_KINEN_SLUG,
            'goal',
            0,
            'Terminal career goal before URA Finale or Unity Cup progression',
        ];

        $orderedTargets = [];

        foreach ($targets as $target) {
            if (! is_array($target) || count($target) < 4) {
                continue;
            }

            [$raceSlug, $raceType, $priority, $notes] = $target;

            if (! is_string($raceSlug) || ! is_string($raceType)) {
                continue;
            }

            $normalizedTarget = [
                $raceSlug,
                $raceType,
                is_int($priority) ? $priority : (int) $priority,
                is_string($notes) || $notes === null ? $notes : null,
            ];

            if ($raceSlug === self::DEBUT_RACE_SLUG) {
                $debutTarget = [
                    self::DEBUT_RACE_SLUG,
                    'required',
                    0,
                    $normalizedTarget[3] ?? $debutTarget[3],
                ];

                continue;
            }

            if ($raceSlug === self::ARIMA_KINEN_SLUG) {
                $arimaTarget = [
                    self::ARIMA_KINEN_SLUG,
                    'goal',
                    0,
                    $normalizedTarget[3] ?? $arimaTarget[3],
                ];

                continue;
            }

            if (! array_key_exists($raceSlug, $orderedTargets)) {
                $orderedTargets[$raceSlug] = $normalizedTarget;
            }
        }

        $normalizedTargets = [$debutTarget, ...array_values($orderedTargets), $arimaTarget];

        return array_map(
            fn (array $target, int $index): array => [$target[0], $target[1], $index + 1, $target[3]],
            $normalizedTargets,
            array_keys($normalizedTargets),
        );
    }

    /**
     * @param  array<string, mixed>  $notes
     * @param  array<int, array<string, mixed>>  $sources
     * @return array<string, mixed>
     */
    private function buildNotes(array $notes, array $sources): array
    {
        return array_replace_recursive($notes, [
            'goal_race_policy' => [
                'starts_with' => self::DEBUT_RACE_SLUG,
                'ends_with' => self::ARIMA_KINEN_SLUG,
                'mode_extensions' => [
                    'ura_finale' => ['ura-preliminary', 'ura-semifinal', 'ura-finals'],
                ],
            ],
            'goal_race_research' => [
                'status' => 'pending-character-goal-verification',
                'verified' => false,
                'verification_scope' => [
                    'character_goal_races' => 'pending',
                    'career_boundary' => 'community-supported',
                    'ura_finale_extension' => 'official-jp-portal',
                    'unity_cup_extension' => 'unverified',
                ],
            ],
            'goal_race_policy_sources' => [
                [
                    'label' => 'Official JP scenario index',
                    'url' => 'https://umamusume.jp/contents/game/scenario/',
                    'scope' => 'scenario-policy',
                    'language' => 'jp',
                ],
                [
                    'label' => 'Official JP URA Finals scenario page',
                    'url' => 'https://umamusume.jp/contents/game/scenario/ura/',
                    'scope' => 'ura-extension',
                    'language' => 'jp',
                ],
                [
                    'label' => 'Official JP Twinkle Legends scenario page',
                    'url' => 'https://umamusume.jp/contents/game/scenario/thetwinklelegends/',
                    'scope' => 'scenario-audit',
                    'language' => 'jp',
                    'notes' => 'Official scenario page exposes Dream Fest, not Unity Cup.',
                ],
            ],
            'goal_race_sources' => array_values($sources),
        ]);
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['hopeful-stakes', 'required', 2, 'Junior Late Dec — Win G1 medium race (fan requirement)'],
                    ['japan-derby', 'goal', 3, "Her dream — become Japan's No. 1 horse"],
                    ['japan-cup', 'goal', 4, 'Beat all of Japan and the world'],
                    ['tenno-sho-spring', 'goal', 5, 'Spring long-distance crown'],
                    ['tenno-sho-autumn', 'story', 6, 'Key rivalry event with Silence Suzuka'],
                    ['arima-kinen', 'story', 7, 'Year-end climax'],
                    ['kikuka-sho', 'recommended', 8, 'Triple Crown third leg'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['mainichi-okan', 'required', 2, 'Classic Autumn — place top 3 (G2 mile prep race)'],
                    ['takarazuka-kinen', 'goal', 3, 'Story-critical Classic race (real-life Tenno Sho accident reimagined)'],
                    ['japan-cup', 'goal', 4, 'Her ultimate dream — run freely on the world stage'],
                    ['nhk-mile-cup', 'story', 5, 'Classic year mile triumph'],
                    ['victoria-mile', 'recommended', 6, 'Spring G1 mile fit'],
                    ['mile-championship', 'recommended', 7, 'Natural mile specialist autumn target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['satsuki-sho', 'required', 2, 'Classic G1 — Triple Crown first leg (place top 3)'],
                    ['japan-derby', 'goal', 3, 'Triple Crown second leg — comeback story pivot'],
                    ['osaka-hai', 'required', 4, 'Senior — comeback race after long injury (place top 3)'],
                    ['arima-kinen', 'goal', 5, 'Legendary comeback Arima win — year-end triumph'],
                    ['tenno-sho-spring', 'recommended', 6, 'Long-distance showcase'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['satsuki-sho', 'required', 2, 'Classic G1 — Triple Crown first leg (place top 3)'],
                    ['japan-derby', 'required', 3, 'Classic G1 — Triple Crown second leg (place top 3)'],
                    ['kikuka-sho', 'goal', 4, 'Triple Crown completion — autumn stamina king'],
                    ['tenno-sho-spring', 'story', 5, 'Post-injury comeback stamina test'],
                    ['arima-kinen', 'story', 6, 'Year-end championship goal'],
                    ['takarazuka-kinen', 'recommended', 7, 'Mid-year G1 prestige'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['japan-derby', 'goal', 2, 'Sibling rivalry with Narita Brian — actual real-life win'],
                    ['kikuka-sho', 'goal', 3, 'Real-life win — stamina showcase against rivals'],
                    ['takarazuka-kinen', 'goal', 4, 'Real-life 1994 Takarazuka Kinen win'],
                    ['tenno-sho-spring', 'story', 5, 'Long-distance pinnacle stamina goal'],
                    ['arima-kinen', 'recommended', 6, 'Year-end Grand Prix target'],
                    ['satsuki-sho', 'recommended', 7, 'Narita Brian rivalry begins here'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['japan-derby', 'goal', 2, 'Historic win against males in 2007 — trailblazer moment'],
                    ['tenno-sho-autumn', 'goal', 3, 'Real-life consecutive wins — intense story rivalry with Daiwa Scarlet'],
                    ['victoria-mile', 'story', 4, 'Filly G1 showcase — feminine force of nature'],
                    ['mile-championship', 'story', 5, 'Mile specialist autumn peak'],
                    ['arima-kinen', 'recommended', 6, 'Year-end Grand Prix prestige target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['shuka-sho', 'required', 2, 'Classic Autumn filly G1 — place top 3 (1,500 fans required)'],
                    ['satsuki-sho', 'goal', 3, 'Real-life Satsuki Sho win — beating the colts'],
                    ['victoria-mile', 'goal', 4, 'Spring G1 showcasing her dominant escape style'],
                    ['tenno-sho-autumn', 'story', 5, 'Intense and defining rivalry with Vodka'],
                    ['queen-elizabeth-cup', 'story', 6, 'Autumn filly championship target'],
                    ['arima-kinen', 'recommended', 7, 'Year-end undefeated dream finish'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['satsuki-sho', 'required', 2, 'Classic G1 — Triple Crown first leg (place top 3)'],
                    ['kikuka-sho', 'goal', 3, 'Real-life win — Triple Crown stamina king'],
                    ['tenno-sho-spring', 'goal', 4, 'Real-life 2x winner — stamina god showcase'],
                    ['takarazuka-kinen', 'goal', 5, 'Real-life 2x win — wildly unpredictable chaos'],
                    ['arima-kinen', 'goal', 6, 'Real-life 3 wins — year-end legend of chaos and guts'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['kikuka-sho', 'goal', 2, 'Real-life Classic win — autumn distance specialist peak'],
                    ['tenno-sho-spring', 'goal', 3, 'Real-life 3x consecutive winner — noble supremacy'],
                    ['takarazuka-kinen', 'goal', 4, 'Real-life win — mid-year noble triumph'],
                    ['arima-kinen', 'recommended', 5, 'Year-end Grand Prix crowd favourite'],
                    ['hanshin-daishogai', 'recommended', 6, 'Senior G2 long-distance warm-up'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['japan-derby', 'goal', 2, 'Real-life win — Queen of the Turf beats the colts'],
                    ['tenno-sho-autumn', 'goal', 3, 'Real-life consecutive wins — autumn dominance'],
                    ['queen-elizabeth-cup', 'story', 4, 'Autumn filly crown story arc'],
                    ['arima-kinen', 'story', 5, 'Year-end Grand Prix prestige story'],
                    ['osaka-hai', 'recommended', 6, 'Senior medium G1 target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['nhk-mile-cup', 'required', 2, 'Classic G1 mile — place top 3 (mandatory Classic race)'],
                    ['japan-cup', 'goal', 3, 'Ultimate goal — beat the world at Japan Cup'],
                    ['takarazuka-kinen', 'story', 4, 'Mid-year story event before Japan Cup dream'],
                    ['mile-championship', 'recommended', 5, 'Natural mile specialist autumn target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['asahi-hai-futurity', 'story', 2, 'Dominant junior G1 showcase story — Silver Bullet arrives'],
                    ['takarazuka-kinen', 'goal', 3, 'Real-life win — mid-year G1 triumph'],
                    ['arima-kinen', 'goal', 4, 'Real-life 2 wins — year-end story centrepiece'],
                    ['japan-cup', 'recommended', 5, 'High-profile late season international target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['tenno-sho-autumn', 'goal', 2, "Real-life consecutive G1 win — People's Hero autumn peak"],
                    ['arima-kinen', 'goal', 3, 'Legendary retirement comeback win — year-end Grand Prix'],
                    ['nhk-mile-cup', 'story', 4, 'Versatile distance range showcase'],
                    ['mile-championship', 'recommended', 5, 'Mile fan-favourite target'],
                    ['japan-cup', 'recommended', 6, 'Prestige long-season international goal'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['satsuki-sho', 'required', 2, 'Classic G1 — Triple Crown first leg (place top 3)'],
                    ['japan-derby', 'required', 3, 'Classic G1 — Triple Crown second leg (place top 3)'],
                    ['kikuka-sho', 'goal', 4, 'Triple Crown completion — Grand Slam coronation'],
                    ['tenno-sho-autumn', 'story', 5, 'Senior dominance — undefeated Emperor'],
                    ['japan-cup', 'story', 6, 'International prestige beat — world conquered'],
                    ['arima-kinen', 'goal', 7, 'Year-end crown — Emperor seals the Grand Slam'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['nhk-mile-cup', 'required', 2, 'Classic G1 mile — place top 3 (fan requirement)'],
                    ['mile-championship', 'goal', 3, 'Real-life win — definitive mile champion'],
                    ['sprinters-stakes', 'story', 4, 'Showed sprint capability — versatile distance range'],
                    ['victoria-mile', 'recommended', 5, 'Spring G1 mile fit'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['yayoi-sho', 'story', 2, 'Classic G2 prep race — real-life dominant undefeated win'],
                    ['satsuki-sho', 'goal', 3, 'Undefeated streak peaks in glorious G1 win before retirement'],
                    ['japan-derby', 'recommended', 4, 'Dream race she never ran (retired after Satsuki Sho injury)'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['sprinters-stakes', 'goal', 2, 'Real-life 2x consecutive Sprinters Stakes winner — sprint crown'],
                    ['takamatsunomiya-kinen', 'goal', 3, 'Spring sprint G1 crown — ultimate speed showcase'],
                    ['asahi-hai-futurity', 'story', 4, 'Junior G1 mile showcase — dominated despite longer distance'],
                    ['mile-championship', 'recommended', 5, 'Tried mile range but heart is in the sprint'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['kikuka-sho', 'goal', 2, 'Upset win over Mihono Bourbon — derails rival Triple Crown'],
                    ['tenno-sho-spring', 'goal', 3, 'Real-life 2x winner — stamina pinnacle for the Black Assassin'],
                    ['arima-kinen', 'story', 4, 'Year-end long-distance Grand Prix target'],
                    ['takarazuka-kinen', 'recommended', 5, 'Medium-long summer G1 prestige target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['satsuki-sho', 'required', 2, 'Classic G1 — Triple Crown first leg (place top 3)'],
                    ['japan-derby', 'required', 3, 'Classic G1 — Triple Crown second leg (place top 3)'],
                    ['kikuka-sho', 'goal', 4, 'Triple Crown third leg — broken by Rice Shower'],
                    ['arima-kinen', 'recommended', 5, 'Year-end Grand Prix challenge'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['satsuki-sho', 'required', 2, 'Classic G1 — Millennium Grand Slam opener (place top 3)'],
                    ['japan-derby', 'required', 3, 'Classic G1 — Millennium Grand Slam race two (place top 3)'],
                    ['tenno-sho-autumn', 'goal', 4, 'Millennium sweep central race — autumn dominance'],
                    ['japan-cup', 'goal', 5, 'Millennium Grand Slam international G1 — world beat'],
                    ['arima-kinen', 'goal', 6, 'Grand Slam final win — Year 2000 year-end coronation'],
                    ['tenno-sho-spring', 'story', 7, 'Year 3 crown — defence of spring title'],
                    ['takarazuka-kinen', 'story', 8, 'Mid-year prestige win — Millennium sweep continues'],
                    ['osaka-hai', 'recommended', 9, 'Senior year opener target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['asahi-hai-futurity', 'goal', 2, 'Junior G1 — undefeated showcase in mile sprint'],
                    ['nhk-mile-cup', 'goal', 3, 'Classic G1 mile mastery — uncrowned king claims crown'],
                    ['hopeful-stakes', 'story', 4, 'Junior G1 medium — dual Classic year strength'],
                    ['mile-championship', 'recommended', 5, 'Autumn G1 mile natural fit'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['satsuki-sho', 'story', 2, 'Classic journey begins — star of the stars enters the stage'],
                    ['japan-derby', 'goal', 3, 'Real-life win — her defining moment as Child of the Stars'],
                    ['kikuka-sho', 'recommended', 4, 'Triple Crown final leg — the unfinished dream'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['mile-championship', 'goal', 2, 'Real-life win — all-rounder all-surface peak'],
                    ['sprinters-stakes', 'story', 3, 'Real-life sprint G1 win — speed versatility proven'],
                    ['nhk-mile-cup', 'story', 4, 'Classic year mile showcase — idols shine bright'],
                    ['victoria-mile', 'recommended', 5, 'Spring G1 mile fit'],
                    ['tenno-sho-autumn', 'recommended', 6, 'Versatile medium distance longer aim'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['mile-championship', 'goal', 2, 'Real-life dramatic comeback win — eccentric king prevails'],
                    ['nhk-mile-cup', 'story', 3, 'Classic year mile target — proving mile mastery'],
                    ['satsuki-sho', 'story', 4, 'Early story arc — trying to be a classic horse'],
                    ['japan-derby', 'recommended', 5, 'His attempt at big medium-distance races'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['yayoi-sho', 'story', 2, 'Classic G2 prep — important lead-up showcasing frontrunner style'],
                    ['satsuki-sho', 'goal', 3, 'Real-life win — frontrunner dominance in Classic opener'],
                    ['kikuka-sho', 'goal', 4, 'Real-life win — Triple Crown two-thirds complete'],
                    ['tenno-sho-spring', 'recommended', 5, 'Stamina-heavy target fitting his frontrunner style'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['satsuki-sho', 'goal', 2, 'Real-life upset win over Biwa Hayahide — underdog triumph'],
                    ['arima-kinen', 'story', 3, 'Real-life Arima Kinen win — underdog legend confirmed'],
                    ['japan-derby', 'recommended', 4, 'Classic season second leg aim'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['takarazuka-kinen', 'goal', 2, 'Real-life win — her crowning achievement in the sun'],
                    ['arima-kinen', 'story', 3, 'Year-end Grand Prix — chasing Mejiro McQueen'],
                    ['osaka-hai', 'recommended', 4, 'Senior medium G1 distance target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['arima-kinen', 'goal', 2, '3rd place 3 consecutive years — legendary bronze collector story'],
                    ['satsuki-sho', 'story', 3, 'Classic year rivalry race — chasing the big names'],
                    ['japan-derby', 'recommended', 4, 'Classic second leg target'],
                    ['kikuka-sho', 'recommended', 5, 'Third leg long-distance stamina aim'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['kikuka-sho', 'goal', 2, 'Real-life win — stamina king of the Classic year'],
                    ['tenno-sho-spring', 'goal', 3, 'Real-life win — long-distance specialist proves dominance'],
                    ['arima-kinen', 'story', 4, 'Year-end Grand Prix night wanderer story'],
                    ['takarazuka-kinen', 'recommended', 5, 'Medium-long prestige G1 summer target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['kikuka-sho', 'story', 2, 'Classic stamina base — builds toward senior greatness'],
                    ['tenno-sho-spring', 'goal', 3, 'Real-life win — epic Wild One frontrunner dominance'],
                    ['takarazuka-kinen', 'goal', 4, 'Real-life win — chaos and guts in mid-year showdown'],
                    ['arima-kinen', 'goal', 5, 'Real-life win — year-end Wild One legend cemented'],
                    ['osaka-hai', 'recommended', 6, 'Senior medium G1 opener target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['tenno-sho-autumn', 'goal', 2, 'Finally beat T.M. Opera O — defining win of the Eternal Second'],
                    ['arima-kinen', 'story', 3, 'Year-end rivalry conclusion with T.M. Opera O'],
                    ['japan-cup', 'story', 4, 'Millennium era prestige race — close but never quite there'],
                    ['osaka-hai', 'recommended', 5, 'Senior G1 revenge story fuel'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['tenno-sho-spring', 'goal', 2, 'Legendary frontrunner upset win — berserker breaks free'],
                    ['takarazuka-kinen', 'story', 3, 'Mid-year escape strategy showcase — all or nothing'],
                    ['osaka-hai', 'recommended', 4, 'Senior medium G1 — frontrunner warmup target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['queen-cup', 'required', 2, 'Classic Feb — place top 3 (G3 filly mile prep)'],
                    ['mile-championship', 'goal', 3, 'Real-life dominant win — Prairie Wind blows them away'],
                    ['queen-elizabeth-cup', 'story', 4, 'Autumn filly championship target — queen claims throne'],
                    ['victoria-mile', 'recommended', 5, 'Spring G1 mile fit'],
                    ['shuka-sho', 'recommended', 6, 'Classic autumn filly G1 alternative target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['shuka-sho', 'story', 2, 'Classic autumn filly G1 target — building to the upset'],
                    ['queen-elizabeth-cup', 'goal', 3, 'Real-life win — Midsummer Princess upset classic victory'],
                    ['takarazuka-kinen', 'recommended', 4, 'Mid-year G1 prestige against mixed field'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['queen-elizabeth-cup', 'goal', 2, 'Real-life win — Iron Mare stamina greatness'],
                    ['takarazuka-kinen', 'story', 3, 'Medium-long summer G1 prestige target'],
                    ['tenno-sho-autumn', 'recommended', 4, 'Medium G1 longer-range fit'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['satsuki-sho', 'story', 2, 'Classic year — People\'s Champion first Classic appearance'],
                    ['osaka-hai', 'goal', 3, 'Real-life win — Senior opener dominance'],
                    ['tenno-sho-spring', 'goal', 4, 'Real-life win — long stamina peak'],
                    ['tenno-sho-autumn', 'story', 5, 'Real-life win — dominant autumn run'],
                    ['japan-cup', 'goal', 6, 'Real-life win — international stage glory'],
                    ['arima-kinen', 'goal', 7, 'Real-life win — legendary retirement finale'],
                    ['takarazuka-kinen', 'story', 8, 'Mid-year prestige — People\'s Champion mid-year story'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['kikuka-sho', 'story', 2, 'Classic G1 long stamina — rivalry with Kitasan Black begins'],
                    ['tenno-sho-spring', 'goal', 3, 'Real-life win — Diamond shines in spring stamina showcase'],
                    ['japan-cup', 'goal', 4, 'Real-life win — international glory on the world stage'],
                    ['arima-kinen', 'story', 5, 'Year-end rivalry climax with Kitasan Black'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['takarazuka-kinen', 'goal', 2, 'Real-life surprise win — Nakayama Specialist conquers Hanshin'],
                    ['arima-kinen', 'story', 3, 'Real-life strong performance — Nakayama home-track pride'],
                    ['japan-cup', 'recommended', 4, 'International challenger story target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['mainichi-okan', 'required', 2, 'Classic Autumn G2 mile prep — place top 3 (fan requirement)'],
                    ['tenno-sho-autumn', 'goal', 3, 'Real-life dominant autumn sweep — Autumn Champion peak'],
                    ['mile-championship', 'story', 4, 'Real-life win — mile versatility story'],
                    ['arima-kinen', 'story', 5, 'Year-end Grand Prix story arc'],
                    ['japan-cup', 'recommended', 6, 'Prestigious autumn international target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['mile-championship', 'goal', 2, 'Real-life win — Tactician\'s career peak'],
                    ['tenno-sho-autumn', 'goal', 3, 'Real-life win — medium distance autumn dominance'],
                    ['japan-cup', 'story', 4, 'International prestige target — Tactician on world stage'],
                    ['osaka-hai', 'recommended', 5, 'Senior G1 medium fit'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['shuka-sho', 'required', 2, 'Classic Autumn filly G1 — place top 3 (fan requirement)'],
                    ['takarazuka-kinen', 'goal', 3, 'Real-life win — Indomitable Amazon beats the colts'],
                    ['japan-derby', 'story', 4, 'Bold challenge — filly storms the Classic against the colts'],
                    ['arima-kinen', 'story', 5, 'Year-end Grand Prix aspirations — Amazon refuses to yield'],
                    ['queen-elizabeth-cup', 'recommended', 6, 'Autumn filly crown — natural home race'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['spring-stakes', 'required', 2, 'Classic G2 mile prep — place top 3 (fan requirement)'],
                    ['satsuki-sho', 'story', 3, 'Favoured Triple Crown contender — story ends before Classic begins'],
                    ['nhk-mile-cup', 'goal', 4, 'Real-life win — definitive career peak before retirement'],
                    ['mile-championship', 'recommended', 5, 'Natural mile specialist autumn target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['queen-cup', 'required', 2, 'Classic Feb G3 filly mile prep — place top 3 (fan requirement)'],
                    ['mile-championship', 'goal', 3, 'Real-life 3 consecutive wins — Resilient Contender peak'],
                    ['nhk-mile-cup', 'story', 4, 'Classic year G1 mile win story'],
                    ['victoria-mile', 'recommended', 5, 'Spring senior G1 mile target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['satsuki-sho', 'required', 2, 'Classic G1 — Triple Crown first leg (place top 3)'],
                    ['japan-derby', 'required', 3, 'Classic G1 — Triple Crown second leg (place top 3)'],
                    ['kikuka-sho', 'goal', 4, 'Triple Crown third leg — Rebel wins it from behind'],
                    ['tenno-sho-autumn', 'recommended', 5, 'Senior medium G1 — Rebel range extended'],
                    ['japan-cup', 'recommended', 6, 'Prestigious international senior target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['tulip-sho', 'required', 2, 'Classic Mar G3 filly sprint — place top 3 (fan requirement)'],
                    ['nhk-mile-cup', 'goal', 3, 'Real-life Classic G1 win — Blooming Flower in full bloom'],
                    ['mile-championship', 'story', 4, 'Autumn mile championship aim — flower petal storm'],
                    ['victoria-mile', 'recommended', 5, 'Spring senior G1 mile natural fit'],
                    ['sprinters-stakes', 'recommended', 6, 'Sprint range viable for speed-heavy build'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['antares-stakes', 'goal', 2, 'Best available dirt target in the catalog — JBC King at home'],
                    ['hyacinth-stakes', 'story', 3, 'Dirt mile Classic year story opener'],
                    ['sprinters-stakes', 'recommended', 4, 'Most accessible G1 for non-standard dirt runs'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['niiza-kinenkai', 'goal', 2, "Her dream — win at least one race at Kochi's Niiza track"],
                    ['baba-kinenkai', 'goal', 3, 'Second dream — prove herself on a new track against all odds'],
                    ['kokura-nisai-stakes', 'story', 4, 'Sprint race in her range — Never Give Up hearts on'],
                    ['sapporo-nisai-stakes', 'story', 5, 'Her story is about never giving up, not winning'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['takarazuka-kinen', 'goal', 2, 'Real-life win — dominant frontrunner style mid-year triumph'],
                    ['arima-kinen', 'goal', 3, 'Real-life 1991 Arima Kinen win — legendary escape victory'],
                    ['mile-championship', 'recommended', 4, 'Mile specialist autumn G1 aim'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['japan-derby', 'story', 2, 'Classic year medium aim — Strong Finisher tests his range'],
                    ['mile-championship', 'goal', 3, 'Real-life win — ultimate mile goal achieved'],
                    ['arima-kinen', 'goal', 4, 'His defining pursuit — near-miss that became a legend'],
                    ['tenno-sho-spring', 'recommended', 5, 'Long-distance stamina showcase option'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['tulip-sho', 'required', 2, 'Classic Mar G3 filly sprint — place top 3 (fan requirement)'],
                    ['victoria-mile', 'goal', 3, 'Real-life Victoria Mile win — Wind Runner at her finest'],
                    ['mile-championship', 'story', 4, 'Autumn mile crown story arc — wind carries her home'],
                    ['nhk-mile-cup', 'recommended', 5, 'Classic year G1 mile natural fit'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['fillies-revue', 'required', 2, 'Classic Year Early Mar — place top 5 (1,750 fans required)'],
                    ['aoi-stakes', 'required', 3, 'Classic Year Late May — place top 5 (1,250 fans required)'],
                    ['hakodate-sprint-stakes', 'required', 4, 'Classic Year Late Jun — place top 3 (1,250 fans required)'],
                    ['sprinters-stakes', 'goal', 5, 'Sprint G1 goal — top 3 Classic Year Late Sep, then win Senior Year Late Sep'],
                    ['ocean-stakes', 'required', 6, 'Senior Year Early Mar — place top 3 (1,500 fans required)'],
                    ['takamatsunomiya-kinen', 'goal', 7, 'Senior Year Late Mar — place 1st (15,000 fans required)'],
                    ['mile-championship', 'story', 8, 'Extended sprint range target'],
                    ['nhk-mile-cup', 'recommended', 9, 'Classic year mile option'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['satsuki-sho', 'story', 2, 'Classic journey highlight — Yaeno Muteki steps onto the G1 stage'],
                    ['arima-kinen', 'goal', 3, 'Real-life win — Invincible year-end warrior'],
                    ['tenno-sho-autumn', 'recommended', 4, 'Senior medium G1 range target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['nhk-mile-cup', 'goal', 2, 'Real-life Classic G1 win — Rising Sun shines brightest'],
                    ['japan-derby', 'story', 3, 'Classic second leg aim — Rising Sun refuses to back down'],
                    ['tenno-sho-autumn', 'recommended', 4, 'Senior medium G1 distance target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['aoi-stakes', 'required', 2, 'Classic Late May G3 sprint — place top 3 (fan requirement)'],
                    ['sprinters-stakes', 'goal', 3, 'Sprint specialist G1 peak race — Wind Goddess speed peak'],
                    ['takamatsunomiya-kinen', 'goal', 4, 'Spring sprint G1 crown — Wind Goddess sweeps the sprint scene'],
                    ['nhk-mile-cup', 'recommended', 5, 'Classic year mile option for longer range build'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['nhk-mile-cup', 'goal', 2, 'Real-life career-peak win — Good Luck Charm delivers fortune'],
                    ['tenno-sho-autumn', 'goal', 3, 'Medium G1 dream target — autumn classics fortune strikes'],
                    ['satsuki-sho', 'story', 4, 'Classic era highlight — lucky charm enters the big stage'],
                    ['arima-kinen', 'recommended', 5, 'Year-end Grand Prix target'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['takarazuka-kinen', 'goal', 2, 'Real-life 1994 Takarazuka Kinen win — blessed mid-year triumph'],
                    ['arima-kinen', 'goal', 3, 'Real-life 1994 Arima Kinen win — Blessed Runner year-end triumph'],
                    ['tenno-sho-autumn', 'story', 4, 'Senior autumn medium G1 — blessed journey continues'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['spring-stakes', 'required', 2, 'Classic Mar G2 mile prep — place top 3 (fan requirement)'],
                    ['nhk-mile-cup', 'goal', 3, 'Real-life Classic G1 win — Cherry Blossom blooms in May'],
                    ['mile-championship', 'recommended', 4, 'Autumn G1 mile natural target for Classic winner'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['mainichi-okan', 'required', 2, 'Classic Autumn G2 mile prep — place top 3 (fan requirement)'],
                    ['tenno-sho-spring', 'goal', 3, 'Real-life win — beloved countryside farm horse legend peaks'],
                    ['takarazuka-kinen', 'goal', 4, 'Real-life win — crowd favourite mid-year triumph'],
                    ['arima-kinen', 'recommended', 5, 'Year-end Grand Prix endurance goal'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['aoi-stakes', 'required', 2, 'Classic Late May G3 sprint — place top 3 (fan requirement)'],
                    ['sprinters-stakes', 'goal', 3, 'Real-life sprint G1 target — Gold City speed pays off'],
                    ['takamatsunomiya-kinen', 'goal', 4, 'Spring sprint G1 crown — Gold City shines brightest'],
                    ['asahi-hai-futurity', 'story', 5, 'Junior G1 sprint showcase — gold gleams early'],
                    ['mile-championship', 'recommended', 6, 'Extended range attempt for gold-themed versatility'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['tenno-sho-spring', 'goal', 2, 'Real-life win — 1993 Tenno Sho Spring champion — Snow Beauty prevails'],
                    ['sapporo-kinen', 'goal', 3, 'Summer G2 goal — Snow Beauty shines at Hokkaido namesake track'],
                    ['arima-kinen', 'recommended', 4, 'Year-end Grand Prix endurance test'],
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
                    ['debut-race', 'required', 1, 'Junior Make Debut — career start'],
                    ['spring-stakes', 'required', 2, 'Classic Mar G2 mile prep — place top 3 (fan requirement)'],
                    ['satsuki-sho', 'story', 3, 'Narita Brian rivalry begins — Lucky Star vs Shadow of Century'],
                    ['japan-derby', 'goal', 4, "Real-life win — Lucky Star's defining race on largest stage"],
                    ['kikuka-sho', 'recommended', 5, 'Triple Crown final leg aim — lucky streak continues'],
                    ['arima-kinen', 'recommended', 6, 'Year-end Grand Prix fan favourite goal'],
                ],
            ],

        ];
    }
}
