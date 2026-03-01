# Character-Exclusive Unique Skills Expansion

**Date**: 2026-03-01 (updated 2026-03-01, Phase 4)
**Status**: Complete
**Branch**: develop

## Summary

Expanded character-exclusive unique skills from 12 to 67, covering all trainable cards on the Global server. This includes 48 card 01 primary unique skills and 19 card 02 alternate unique skills. Fixed 12 naming errors in existing skills. All data sourced from [uma.guide](https://uma.guide/skills/).

## Changes

### Data Source

| Source | URL | Notes |
| --- | --- | --- |
| uma.guide Skills | <https://uma.guide/skills/> | 521 skills, authoritative EN Global names |
| uma.guide Characters | <https://uma.guide/characters/> | 67 trainable cards (48 characters, 19 with card 02) |
| Character Detail Pages | <https://uma.guide/characters/detail.html?card=XXXXXX> | Individual skill verification per card |

### Card Variant Architecture

48 unique Global characters exist. 19 of those characters have a second training card (card 02) with a different unique skill. This gives 48 + 19 = 67 total cards, matching uma.guide's "67 of 67" count exactly.

### Naming Corrections (12 fixes)

#### Phase 1 — Initial Corrections (2026-03-01, 2 fixes)

| Character | Old Name (Wrong) | New Name (Correct) | Reason |
| --- | --- | --- | --- |
| Special Week | All-Seeing Eyes | Shooting Star | All-Seeing Eyes is a debuff skill on Nice Nature's card |
| Daiwa Scarlet | The View from the Lead Is Mine! | Resplendent Red Ace | TVFTLIM belongs to Silence Suzuka's base card |

#### Phase 2 — Card 01 Name Corrections (2026-03-02, 10 fixes)

| Character | Internal ID | Old Name (Wrong) | New Name (Correct) |
| --- | --- | --- | --- |
| Vodka | unique_001 | Xceleration | Cut and Drive! |
| Silence Suzuka | unique_004 | Towards the Scenery I Seek | The View from the Lead Is Mine! |
| Tokai Teio | unique_005 | Miracles of the Emperor | Sky-High Teio Step |
| Gold Ship | unique_006 | Gold Ship's Rampage | Anchors Aweigh! |
| Mejiro McQueen | unique_008 | Unchanging | The Duty of Dignity Calls |
| El Condor Pasa | unique_010 | Chin Up, Derby Umamusume! | Victoria por plancha ☆ |
| Narita Brian | unique_011 | Shadow of the Fierce Tiger | Shadow Break |
| T.M. Opera O | unique_012 | Operatic Victory | This Dance Is for Vittoria! |
| Mihono Bourbon | unique_026 | Operation Cacao | G00 1st. F∞; |
| Haru Urara | unique_041 | 114th Time's the Charm | Super-Duper Climax |

#### Additional Fix — Inherited Skill Rename

| Skill | Internal ID | Old Name | New Name | Reason |
| --- | --- | --- | --- | --- |
| Cut and Drive! (Inherited) | unique_inherit_002 | Cut and Drive! | Cut and Drive! (Inherited) | UNIQUE constraint collision with Vodka card 01 |

### New Card 01 Skills Added (36 entries, 2026-03-01)

| ID | Character | Skill Name | Primary Effect |
| --- | --- | --- | --- |
| unique_013 | Maruzensky | Red Shift/LP1211-M | Acceleration +0.4 |
| unique_014 | Fuji Kiseki | Lights of Vaudeville | Target Speed +0.45 |
| unique_015 | Taiki Shuttle | Shooting for Victory! | Acceleration +0.4 |
| unique_016 | Hishi Amazon | You and Me! One-on-One! | Target Speed +0.35 |
| unique_017 | Symboli Rudolf | Behold Thine Emperor's Divine Might | Target Speed +0.45 |
| unique_018 | Air Groove | Blazing Pride | Target Speed +0.35 |
| unique_019 | Agnes Digital | OMG! (ﾟ∀ﾟ) The Final Sprint! ☆ | Target Speed +0.35 |
| unique_020 | Seiun Sky | Angling and Scheming | Acceleration +0.4 |
| unique_021 | Tamamo Cross | White Lightning Comin' Through! | Target Speed +0.35 |
| unique_022 | Fine Motion | Fairy Tale | Target Speed +0.35 |
| unique_023 | Biwa Hayahide | ∴win Q.E.D. | Target Speed +0.35 |
| unique_024 | Mayano Top Gun | Flashy☆Landing | Target Speed +0.25 |
| unique_025 | Manhattan Cafe | Chasing After You | Target Speed +0.25 |
| unique_026 | Mihono Bourbon | G00 1st. F∞; | Target Speed +0.35 |
| unique_027 | Mejiro Ryan | Let's Pump Some Iron! | Acceleration +0.4 |
| unique_028 | Hishi Akebono | YUMMY☆SPEED! | Target Speed +0.25 |
| unique_029 | Rice Shower | Blue Rose Closer | Target Speed +0.35 |
| unique_030 | Agnes Tachyon | U=ma² | Stamina Recovery +0.055 |
| unique_031 | Winning Ticket | Our Ticket to Win! | Target Speed +0.35 |
| unique_032 | Eishin Flash | Schwarzes Schwert | Target Speed +0.35 |
| unique_033 | Curren Chan | #LookatCurren | Target Speed +0.25 |
| unique_034 | Kawakami Princess | A Princess Must Seize Victory! | Target Speed +0.35 |
| unique_035 | Gold City | KEEP IT REAL. | Acceleration +0.3 |
| unique_036 | Sakura Bakushin O | Genius x Bakushin = Victory | Target Speed +0.35 |
| unique_037 | Super Creek | Pure Heart | Stamina Recovery +0.075 |
| unique_038 | Smart Falcon | SPARKLY☆STARDOM | Target Speed +0.25 |
| unique_039 | Tosen Jordan | Pop & Polish | Target Speed +0.35 |
| unique_040 | Narita Taishin | Nemesis | Target Speed +0.35 |
| unique_041 | Haru Urara | Super-Duper Climax | Target Speed +0.25 |
| unique_042 | Matikanefukukitaru | I See Victory in My Future! | Target Speed +0.35 |
| unique_043 | Meisho Doto | I Never Goof Up! | Target Speed +0.25 |
| unique_044 | Mejiro Dober | Moving Past, and Beyond | Acceleration +0.4 |
| unique_045 | Nice Nature | Just a Little Farther! | Target Speed +0.35 |
| unique_046 | King Halo | Prideful King | Target Speed +0.45 |
| unique_047 | Sakura Chiyono O | Ambition to Surpass the Sakura | Target Speed +0.35 |
| unique_048 | Mejiro Ardan | A Lifelong Dream, A Moment's Flight | Target Speed +0.45 |

### New Card 02 Alternate Unique Skills Added (19 entries, 2026-03-02)

| ID | Character | Card 02 Skill Name | Primary Effect |
| --- | --- | --- | --- |
| unique_049 | Special Week | Dazzl'n ♪ Diver | Stamina Recovery +0.055 |
| unique_050 | Tokai Teio | Certain Victory | Target Speed +0.45 |
| unique_051 | Maruzensky | A Kiss for Courage | Target Speed +0.35 |
| unique_052 | Oguri Cap | Festive Miracle | Speed +0.25, Accel +0.3 |
| unique_053 | Grass Wonder | Superior Heal | Stamina Recovery +0.075 |
| unique_054 | Mejiro McQueen | Legacy of the Strong | Target Speed +0.35 |
| unique_055 | El Condor Pasa | Condor's Fury | Acceleration +0.4 |
| unique_056 | T.M. Opera O | Barcarole of Blessings | Target Speed +0.45 |
| unique_057 | Symboli Rudolf | Arrows Whistle, Shadows Disperse | Target Speed +0.35 |
| unique_058 | Air Groove | Eternal Moments | Target Speed +0.35 |
| unique_059 | Biwa Hayahide | Presents from X | Target Speed +0.35 |
| unique_060 | Mayano Top Gun | Flowery☆Maneuver | Target Speed +0.35 |
| unique_061 | Mihono Bourbon | Operation Cacao | Speed +0.35, Recovery |
| unique_062 | Rice Shower | Every Rose Has Its Fangs | Recovery +0.055, Drain |
| unique_063 | Eishin Flash | Guten Appetit ♪ | Target Speed +0.35 |
| unique_064 | Gold City | Dancing in the Leaves | Speed +0.25, Accel +0.3 |
| unique_065 | Super Creek | Give Mummy a Hug ♡ | Speed +0.25, Accel +0.3 |
| unique_066 | Haru Urara | 114th Time's the Charm | Target Speed +0.25 |
| unique_067 | Matikanefukukitaru | Bountiful Harvest | Target Speed +0.35 |

### Phase 3 — Unique Skill Star Level Upgrade System (2026-03-01)

Researched and implemented the star level upgrade system for all 67 character-exclusive unique skills.

#### Star Level Mechanics (sourced from uma.guide)

| Star Level | Unique Skill State |
| --- | --- |
| Star 1–2 | Base (weaker) version — `unique_base_effects` |
| Star 3+ | Full-power version — `effects` (starts at level 1) |
| Star 6 | Full-power version starts at level 3 instead of 1 |

Every unique skill appears twice on uma.guide: a full-power entry and a weaker **200 SP** base entry. The 200 SP version is the star 1–2 skill.

#### Effect Reduction Pattern (base vs full-power)

| Full-Power Effect | Base Effect |
| --- | --- |
| Target Speed ≥ +0.20 | Reduced by exactly −0.20 |
| Small secondary effects (+0.10, +0.05) | Halved |
| Stamina Recovery 0.075 | 0.035 |
| Stamina Recovery 0.055 | 0.015 |

#### New DB Columns Added (all nullable)

| Column | Type | Purpose |
| --- | --- | --- |
| `unique_star_upgrade` | boolean | `true` for all 67 upgradeable unique skills; `null` for non-unique |
| `unique_star6_initial_level` | tinyint | Starting level at star 6 (always `3` for upgradeable skills) |
| `unique_base_effects` | JSON | Weaker effects used at star 1–2 |

### Phase 4 — Star Level on Career Plans (2026-03-01)

Added `star_level` (1★–5★) to `ucp_careers` so users can record which star level their character is trained at during planning. Affects which unique skill version is active (base at 1–2★, full-power at 3–5★).

#### Star Level Mechanics — Career Planning (sourced from uma.guide)

| Star Level | Base Stats | Unique Skill State |
| --- | --- | --- |
| 1★ | Lowest | Base/weaker version (`unique_base_effects`) |
| 2★ | Improved | Base/weaker version (`unique_base_effects`) |
| 3★ | Improved | **Full-power version** (`effects`) ★ threshold |
| 4★ | Higher | Full-power version |
| 5★ | Maximum | Full-power version |

- Characters scout at 1★, 2★, or 3★ based on gacha rarity
- **3★ is the key threshold**: unique skill upgrades + Green Spark eligibility for veterans
- Growth rates remain fixed per character; only base starting stats scale with star level

#### New DB Column Added

| Column | Type | Table | Default | Purpose |
| --- | --- | --- | --- | --- |
| `star_level` | `unsignedTinyInteger` | `ucp_careers` | `3` | Character star level at time of career planning (1–5) |

### Files Modified

| File | Change |
| --- | --- |
| database/seeders/data/curated_skills.php | +19 card 02 skills, 10 name corrections, 1 inherited rename; all 67 unique skills updated with star upgrade data |
| database/migrations/2026_03_01_031050_add_unique_star_upgrade_columns_to_ucp_skills.php | New migration — 3 nullable columns: `unique_star_upgrade`, `unique_star6_initial_level`, `unique_base_effects` |
| app/Models/Skill.php | Updated `@property` docblock, `$fillable`, and `casts()` for 3 new fields |
| database/seeders/UcpSkillsSeeder.php | Updated `Skill::create()` to include 3 new fields |
| tests/Feature/SkillManagementTest.php | Updated assertions, new card 02 test |
| tests/Unit/Models/SkillModelTest.php | +4 tests for unique star upgrade model attributes |
| tests/Unit/Seeders/UcpSkillsSeederTest.php | +4 tests: 67 upgrade count, base effects structure, DB seeding validation |
| .agents/memory.instruction.md | Updated curated skills architecture notes |
| docs/implementation-summaries/character-exclusive-unique-skills-expansion-2026-03-01.md | Updated with card 02 expansion and Phase 3 star upgrade system |
| database/migrations/2026_03_01_034238_add_star_level_to_careers_table.php | New migration — adds `star_level` column to `ucp_careers` (Phase 4) |
| app/Models/Career.php | Added `star_level` to `@property` docblock, `$fillable`, and `casts()` (Phase 4) |
| database/factories/CareerFactory.php | Added `star_level` to `definition()` + `withStarLevel(int)` factory state (Phase 4) |
| resources/js/components/plan-wizard.js | Added `star_level: 3` default, `setStarLevel()` method, `starLevelLabel` getter, validation 1–5 (Phase 4) |
| resources/views/plans/create.blade.php | Interactive ★ star selector in Step 1, unique skill status badge (Phase 4) |
| resources/views/plans/edit.blade.php | Same ★ selector (editable even with locked character) (Phase 4) |
| resources/views/plans/show.blade.php | Star display in character sidebar and Overview tab grid (Phase 4) |
| tests/Unit/Models/CareerModelTest.php | +10 star level tests: default, dataset 1–5, int cast, fillable, factory state, clamping (Phase 4) |

### Database Impact

| Metric | Before (Phase 1) | After Phase 1 | After Phase 2 | After Phase 3 | After Phase 4 |
| --- | --- | --- | --- | --- | --- |
| Total skills | 121 | 157 | 176 | 176 | 176 |
| Character-exclusive unique skills | 12 | 48 (card 01) | 67 (48 card 01 + 19 card 02) | 67 | 67 |
| Skills with star upgrade data | 0 | 0 | 0 | 67 | 67 |
| Evolution pairs | 19 | 19 | 19 | 19 | 19 |
| New DB columns on `ucp_careers` | 0 | 0 | 0 | 0 | 1 (`star_level`) |

## Test Results

- **41 tests passed** (4,858 assertions) in `SkillModelTest` and `UcpSkillsSeederTest` (Phase 3)
- **44 tests passing** across full skill management and seeder test files
- **0 errors** in database seeding
- **Pint formatting**: 81 dirty files passed

## Notes

- `meta_tier` ENUM only allows `S+`, `S`, `A`, `B`, `C` — never use `A+`
- Characters in `ucp_characters` (141+) exceed Global trainable cards (67); many are JP-only
- 48 unique characters on Global; 19 have a second training card with different unique skill
- `ucp_skills.name` has UNIQUE constraint — inherited "Cut and Drive!" renamed to avoid collision
- Seeder `upsertSkill()` matches by `internal_id`, only updates NULL/empty fields
- Star upgrade columns are nullable so seeder's update-only-nulls logic fills them correctly
- uma.guide URL: `https://uma.guide/characters/detail.html?card=XXXXXX`
- uma.guide lists each unique skill twice — full-power version and 200 SP base version (star 1–2)
