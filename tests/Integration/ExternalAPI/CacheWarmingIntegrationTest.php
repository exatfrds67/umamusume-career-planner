<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Race;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    $this->cacheManager = new CacheManagerService;
});

afterEach(function () {
    Cache::flush();
});

describe('Cache Warming Integration', function () {
    describe('Cache Warming Operations', function () {
        it('warms cache with all priority levels', function () {
            $result = $this->cacheManager->warmCache('all');

            expect($result)->toHaveKey('success')
                ->and($result)->toHaveKey('warmed_items')
                ->and($result)->toHaveKey('failed_items')
                ->and($result)->toHaveKey('duration_ms')
                ->and($result)->toHaveKey('items')
                ->and($result['warmed_items'])->toBeGreaterThan(0);
        });

        it('warms cache with high priority only', function () {
            $result = $this->cacheManager->warmCache('high');

            expect($result['success'])->toBeTrue()
                ->and($result['items'])->toHaveKey('top_characters')
                ->and($result['items'])->toHaveKey('top_support_cards');
        });

        it('warms cache with medium priority', function () {
            $result = $this->cacheManager->warmCache('medium');

            expect($result['success'])->toBeTrue()
                ->and($result['items'])->toHaveKey('race_definitions')
                ->and($result['items'])->toHaveKey('popular_skills');
        });

        it('warms cache with low priority', function () {
            $result = $this->cacheManager->warmCache('low');

            expect($result['success'])->toBeTrue()
                ->and($result['items'])->toHaveKey('meta_rankings')
                ->and($result['items'])->toHaveKey('game_mechanics');
        });

        it('tracks warming duration', function () {
            $result = $this->cacheManager->warmCache('high');

            expect($result['duration_ms'])->toBeGreaterThanOrEqual(0)
                ->and($result['duration_ms'])->toBeFloat();
        });

        it('reports warming statistics', function () {
            $this->cacheManager->warmCache('all');

            $stats = $this->cacheManager->getWarmingStatistics();

            expect($stats)->toBeArray()
                ->and($stats)->toHaveKey('last_run')
                ->and($stats)->toHaveKey('warmed_items')
                ->and($stats)->toHaveKey('failed_items')
                ->and($stats)->toHaveKey('duration_ms')
                ->and($stats)->toHaveKey('success');
        });
    });

    describe('Cache Warming Data Types', function () {
        it('warms top characters data', function () {
            Character::factory()->create(['name' => 'Silence Suzuka']);

            $result = $this->cacheManager->warmCache('high');

            // Check that character data keys were created
            expect($result['items']['top_characters'])->toBe('success');

            // Verify some character data exists in cache
            $characterData = $this->cacheManager->get('character_data:Silence Suzuka');
            expect($characterData)->toBeArray();
        });

        it('warms top support cards data', function () {
            $card = SupportCard::factory()->create(['is_active' => true]);

            $result = $this->cacheManager->warmCache('high');

            expect($result['items']['top_support_cards'])->toBe('success');

            // Verify support card data exists
            $cardData = $this->cacheManager->get("support_cards:{$card->id}");
            expect($cardData)->toBeArray();
        });

        it('warms race definitions data', function () {
            $race = Race::factory()->create();

            $result = $this->cacheManager->warmCache('medium');

            expect($result['items']['race_definitions'])->toBe('success');

            // Verify race data exists (cache key uses race ID)
            $raceData = $this->cacheManager->get("race_data:{$race->id}");
            expect($raceData)->toBeArray();
        });

        it('warms popular skills data', function () {
            $skill = Skill::factory()->create(['is_active' => true]);

            $result = $this->cacheManager->warmCache('medium');

            expect($result['items']['popular_skills'])->toBe('success');

            // Verify skill data exists
            $skillData = $this->cacheManager->get("skills:{$skill->id}");
            expect($skillData)->toBeArray();
        });

        it('warms meta rankings data', function () {
            $result = $this->cacheManager->warmCache('low');

            expect($result['items']['meta_rankings'])->toBe('success');

            // Verify meta ranking data exists
            $rankingData = $this->cacheManager->get('meta_rankings:speed');
            expect($rankingData)->toBeArray();
        });

        it('warms game mechanics data', function () {
            $result = $this->cacheManager->warmCache('low');

            expect($result['items']['game_mechanics'])->toBe('success');

            // Verify game mechanics data exists
            $mechanicsData = $this->cacheManager->get('game_mechanics:stat_breakpoints');
            expect($mechanicsData)->toBeArray();
        });
    });

    describe('Cache Warming Idempotency', function () {
        it('skips already cached items on subsequent warming', function () {
            // First warming
            $firstResult = $this->cacheManager->warmCache('high');
            $firstWarmedCount = $firstResult['warmed_items'];

            // Second warming should still succeed but items already exist
            $secondResult = $this->cacheManager->warmCache('high');

            expect($secondResult['success'])->toBeTrue()
                ->and($secondResult['warmed_items'])->toBe($firstWarmedCount);
        });

        it('handles partial cache state', function () {
            // Pre-populate some cache entries
            $this->cacheManager->put('character_data:Silence Suzuka', [
                'name' => 'Silence Suzuka',
                'pre_existing' => true,
            ]);

            $result = $this->cacheManager->warmCache('high');

            expect($result['success'])->toBeTrue();

            // Verify pre-existing data is preserved
            $cached = $this->cacheManager->get('character_data:Silence Suzuka');
            expect($cached['pre_existing'])->toBeTrue();
        });
    });

    describe('Cache Warming Error Handling', function () {
        it('continues warming after individual task failure', function () {
            // Even if some tasks fail, others should complete
            $result = $this->cacheManager->warmCache('all');

            expect($result)->toHaveKey('items')
                ->and($result['items'])->toBeArray();

            // At least some items should have succeeded
            $successCount = count(array_filter(
                $result['items'],
                fn ($status) => $status === 'success'
            ));

            expect($successCount)->toBeGreaterThan(0);
        });

        it('reports failed items count', function () {
            $result = $this->cacheManager->warmCache('all');

            expect($result)->toHaveKey('failed_items')
                ->and($result['failed_items'])->toBeInt();
        });
    });
});
