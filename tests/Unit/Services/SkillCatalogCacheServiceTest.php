<?php

declare(strict_types=1);

use App\Models\Skill;
use App\Services\SkillCatalogCacheService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->cacheService = new SkillCatalogCacheService;

    // Clear any existing cache before each test
    Cache::flush();
});

describe('SkillCatalogCacheService', function () {
    describe('cache key format', function () {
        it('uses correct cache key format with version', function () {
            $stats = $this->cacheService->getCacheStats();

            expect($stats['cache_key'])->toStartWith('advisory:skill_catalog:');
            expect($stats['cache_key'])->toContain($this->cacheService->getCurrentVersion());
        });

        it('allows custom version in cache key', function () {
            $customVersion = '2.0.0';
            $stats = $this->cacheService->getCacheStats($customVersion);

            expect($stats['cache_key'])->toBe('advisory:skill_catalog:'.$customVersion);
            expect($stats['version'])->toBe($customVersion);
        });
    });

    describe('cache TTL', function () {
        it('has 24-hour TTL (86400 seconds)', function () {
            $ttl = $this->cacheService->getCacheTtl();

            expect($ttl)->toBe(86400);
        });
    });

    describe('getSkillCatalog', function () {
        it('returns collection of skills', function () {
            // Create test skills using factory defaults
            Skill::factory()->count(3)->create(['is_active' => true]);

            $catalog = $this->cacheService->getSkillCatalog();

            expect($catalog)->toBeInstanceOf(\Illuminate\Support\Collection::class);
            expect($catalog)->toHaveCount(3);
        });

        it('caches the skill catalog', function () {
            Skill::factory()->count(2)->create(['is_active' => true]);

            // First call should cache
            $this->cacheService->getSkillCatalog();

            expect($this->cacheService->isCached())->toBeTrue();
        });

        it('returns cached data on subsequent calls', function () {
            Skill::factory()->count(2)->create(['is_active' => true]);

            // First call
            $firstCall = $this->cacheService->getSkillCatalog();

            // Add more skills to database (should not affect cached result)
            Skill::factory()->count(3)->create(['is_active' => true]);

            // Second call should return cached data
            $secondCall = $this->cacheService->getSkillCatalog();

            expect($secondCall)->toHaveCount($firstCall->count());
        });

        it('only includes active skills', function () {
            Skill::factory()->count(2)->create(['is_active' => true]);
            Skill::factory()->count(3)->create(['is_active' => false]);

            $catalog = $this->cacheService->getSkillCatalog();

            expect($catalog)->toHaveCount(2);
        });

        it('includes required fields for advisory', function () {
            // Use factory with valid values (normal, rare, unique for rarity)
            Skill::factory()->rare()->create([
                'is_active' => true,
                'name' => 'Test Skill',
                'skill_type' => 'speed',
                'base_sp_cost' => 180,
                'meta_tier' => 'S',
            ]);

            $catalog = $this->cacheService->getSkillCatalog();
            $skill = $catalog->first();

            expect($skill)->toHaveKeys([
                'id',
                'name',
                'skill_type',
                'rarity',
                'base_sp_cost',
                'meta_tier',
                'can_evolve',
                'is_evolution',
            ]);
        });
    });

    describe('getSkillsByType', function () {
        it('filters skills by type', function () {
            // Use valid skill_type values: speed, passive, recovery, debuff, unique
            Skill::factory()->ofType('speed')->create(['is_active' => true]);
            Skill::factory()->ofType('passive')->create(['is_active' => true]);
            Skill::factory()->ofType('speed')->create(['is_active' => true]);

            $speedSkills = $this->cacheService->getSkillsByType('speed');

            expect($speedSkills)->toHaveCount(2);
            expect($speedSkills->every(fn ($s) => $s['skill_type'] === 'speed'))->toBeTrue();
        });
    });

    describe('getSkillsByRarity', function () {
        it('filters skills by rarity', function () {
            // Use valid rarity values: normal, rare, unique
            Skill::factory()->unique()->create(['is_active' => true]);
            Skill::factory()->rare()->create(['is_active' => true]);
            Skill::factory()->unique()->create(['is_active' => true]);

            $uniqueSkills = $this->cacheService->getSkillsByRarity('unique');

            expect($uniqueSkills)->toHaveCount(2);
            expect($uniqueSkills->every(fn ($s) => $s['rarity'] === 'unique'))->toBeTrue();
        });
    });

    describe('getSkillsByMetaTier', function () {
        it('filters skills by meta tier', function () {
            Skill::factory()->metaTier('S')->create(['is_active' => true]);
            Skill::factory()->metaTier('A')->create(['is_active' => true]);
            Skill::factory()->metaTier('S')->create(['is_active' => true]);

            $sSkills = $this->cacheService->getSkillsByMetaTier('S');

            expect($sSkills)->toHaveCount(2);
            expect($sSkills->every(fn ($s) => $s['meta_tier'] === 'S'))->toBeTrue();
        });
    });

    describe('getGoldSkillsForAdvisory', function () {
        it('returns high-tier skills (unique and rare with S/A meta tier) sorted by meta tier', function () {
            // Unique rarity skills should be included
            Skill::factory()->unique()->metaTier('B')->create(['is_active' => true]);
            Skill::factory()->unique()->metaTier('S')->create(['is_active' => true]);
            Skill::factory()->unique()->metaTier('A')->create(['is_active' => true]);
            // Rare with S meta tier should be included
            Skill::factory()->rare()->metaTier('S')->create(['is_active' => true]);
            // Rare with A meta tier should be included
            Skill::factory()->rare()->metaTier('A')->create(['is_active' => true]);
            // Rare with B meta tier should NOT be included
            Skill::factory()->rare()->metaTier('B')->create(['is_active' => true]);
            // Normal skills should NOT be included
            Skill::factory()->normal()->metaTier('S')->create(['is_active' => true]);

            $goldSkills = $this->cacheService->getGoldSkillsForAdvisory();

            // Should include: 3 unique + 2 rare (S and A tier) = 5 skills
            expect($goldSkills)->toHaveCount(5);
            // First should be S tier
            expect($goldSkills->first()['meta_tier'])->toBe('S');
        });

        it('sorts gold skills by meta tier priority', function () {
            Skill::factory()->unique()->metaTier('C')->create(['is_active' => true]);
            Skill::factory()->unique()->metaTier('S')->create(['is_active' => true]);
            Skill::factory()->unique()->metaTier('A')->create(['is_active' => true]);
            Skill::factory()->unique()->metaTier('B')->create(['is_active' => true]);

            $goldSkills = $this->cacheService->getGoldSkillsForAdvisory();

            $tiers = $goldSkills->pluck('meta_tier')->toArray();
            expect($tiers[0])->toBe('S');
            expect($tiers[1])->toBe('A');
            expect($tiers[2])->toBe('B');
            expect($tiers[3])->toBe('C');
        });
    });

    describe('getRecoverySkills', function () {
        it('returns recovery type skills', function () {
            Skill::factory()->ofType('recovery')->create(['is_active' => true]);
            Skill::factory()->ofType('speed')->create(['is_active' => true]);
            Skill::factory()->ofType('recovery')->create(['is_active' => true]);

            $recoverySkills = $this->cacheService->getRecoverySkills();

            expect($recoverySkills)->toHaveCount(2);
            expect($recoverySkills->every(fn ($s) => $s['skill_type'] === 'recovery'))->toBeTrue();
        });
    });

    describe('getSkillById', function () {
        it('returns skill by ID from cache', function () {
            $skill = Skill::factory()->create([
                'is_active' => true,
                'name' => 'Specific Skill',
            ]);

            $result = $this->cacheService->getSkillById($skill->id);

            expect($result)->not->toBeNull();
            expect($result['name'])->toBe('Specific Skill');
        });

        it('returns null for non-existent skill', function () {
            Skill::factory()->create(['is_active' => true]);

            $result = $this->cacheService->getSkillById(99999);

            expect($result)->toBeNull();
        });
    });

    describe('searchSkillsByName', function () {
        it('searches skills by name case-insensitively', function () {
            Skill::factory()->create(['is_active' => true, 'name' => 'Swinging Maestro']);
            Skill::factory()->create(['is_active' => true, 'name' => 'Lane Legerdemain']);
            Skill::factory()->create(['is_active' => true, 'name' => 'Furious Feat']);

            $results = $this->cacheService->searchSkillsByName('swing');

            expect($results)->toHaveCount(1);
            expect($results->first()['name'])->toBe('Swinging Maestro');
        });

        it('searches in both name and name_en fields', function () {
            Skill::factory()->create([
                'is_active' => true,
                'name' => 'Japanese Name',
                'name_en' => 'English Name',
            ]);

            $resultsByName = $this->cacheService->searchSkillsByName('Japanese');
            $resultsByEnglish = $this->cacheService->searchSkillsByName('English');

            expect($resultsByName)->toHaveCount(1);
            expect($resultsByEnglish)->toHaveCount(1);
        });
    });

    describe('invalidateCache', function () {
        it('removes cached skill catalog', function () {
            Skill::factory()->count(2)->create(['is_active' => true]);

            // Cache the catalog
            $this->cacheService->getSkillCatalog();
            expect($this->cacheService->isCached())->toBeTrue();

            // Invalidate
            $result = $this->cacheService->invalidateCache();

            expect($result)->toBeTrue();
            expect($this->cacheService->isCached())->toBeFalse();
        });

        it('can invalidate specific version', function () {
            $customVersion = '2.0.0';

            // Cache with custom version
            $this->cacheService->getSkillCatalog($customVersion);
            expect($this->cacheService->isCached($customVersion))->toBeTrue();

            // Invalidate custom version
            $this->cacheService->invalidateCache($customVersion);

            expect($this->cacheService->isCached($customVersion))->toBeFalse();
        });
    });

    describe('isCached', function () {
        it('returns false when not cached', function () {
            expect($this->cacheService->isCached())->toBeFalse();
        });

        it('returns true when cached', function () {
            Skill::factory()->create(['is_active' => true]);

            $this->cacheService->getSkillCatalog();

            expect($this->cacheService->isCached())->toBeTrue();
        });
    });

    describe('getCacheStats', function () {
        it('returns cache statistics', function () {
            $stats = $this->cacheService->getCacheStats();

            expect($stats)->toHaveKeys(['is_cached', 'cache_key', 'version', 'ttl_seconds']);
            expect($stats['ttl_seconds'])->toBe(86400);
        });
    });

    describe('getCurrentVersion', function () {
        it('returns current catalog version', function () {
            $version = $this->cacheService->getCurrentVersion();

            expect($version)->toBeString();
            expect($version)->toMatch('/^\d+\.\d+\.\d+$/');
        });
    });
});
