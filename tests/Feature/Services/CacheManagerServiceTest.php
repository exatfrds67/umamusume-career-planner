<?php

use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->service = new CacheManagerService;
    Cache::flush();
});

it('stores and retrieves data from cache', function () {
    $data = ['name' => 'Test Character', 'speed' => 800];

    $result = $this->service->put('character_data:test_1', $data);
    expect($result)->toBeTrue();

    $cached = $this->service->get('character_data:test_1');
    expect($cached)->not->toBeNull()
        ->and($cached['name'])->toBe('Test Character')
        ->and($cached['speed'])->toBe(800)
        ->and($cached)->toHaveKey('_cache');
});

it('returns null for missing cache keys', function () {
    $result = $this->service->get('nonexistent:key');
    expect($result)->toBeNull();
});

it('deletes cache entries', function () {
    $this->service->put('character_data:delete_test', ['test' => true]);
    expect($this->service->has('character_data:delete_test'))->toBeTrue();

    $this->service->delete('character_data:delete_test');
    expect($this->service->has('character_data:delete_test'))->toBeFalse();
});

it('checks cache existence', function () {
    expect($this->service->has('missing:key'))->toBeFalse();

    $this->service->put('skills:exists_test', ['skill' => 'test']);
    expect($this->service->has('skills:exists_test'))->toBeTrue();
});

it('returns correct TTL for different data types', function () {
    $ttlConfig = $this->service->getTTLConfig();

    expect($ttlConfig)->toHaveKey('character_data')
        ->and($ttlConfig)->toHaveKey('support_cards')
        ->and($ttlConfig)->toHaveKey('meta_rankings')
        ->and($ttlConfig)->toHaveKey('skills')
        ->and($ttlConfig['character_data'])->toBe(86400)
        ->and($ttlConfig['support_cards'])->toBe(43200);
});

it('returns cache statistics', function () {
    $this->service->resetStatistics();

    $this->service->put('skills:stat_test', ['data' => 'value']);
    $this->service->get('skills:stat_test');
    $this->service->get('missing:key');

    $stats = $this->service->getStatistics();
    expect($stats)->toHaveKey('hits')
        ->and($stats)->toHaveKey('misses')
        ->and($stats)->toHaveKey('hit_rate')
        ->and($stats)->toHaveKey('total_requests');
});

it('includes staleness metadata in cached data', function () {
    $this->service->put('character_data:stale_test', ['name' => 'Test']);

    $cached = $this->service->get('character_data:stale_test');

    expect($cached)->toHaveKey('_cache')
        ->and($cached['_cache'])->toHaveKey('cached_at')
        ->and($cached['_cache'])->toHaveKey('age_seconds')
        ->and($cached['_cache'])->toHaveKey('ttl_seconds')
        ->and($cached['_cache'])->toHaveKey('is_stale')
        ->and($cached['_cache'])->toHaveKey('staleness_percentage')
        ->and($cached['_cache']['is_stale'])->toBeFalse();
});

it('warms cache by priority', function () {
    $result = $this->service->warmCache('high');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('warmed_items')
        ->and($result)->toHaveKey('failed_items')
        ->and($result)->toHaveKey('success')
        ->and($result)->toHaveKey('duration_ms');
});

it('returns cache info', function () {
    $info = $this->service->getCacheInfo();

    expect($info)->toBeArray()
        ->and($info)->toHaveKey('statistics')
        ->and($info)->toHaveKey('ttl_config');
});

it('supports custom TTL values', function () {
    $customTtl = 300;
    $this->service->put('custom:ttl_test', ['data' => 'test'], $customTtl);

    expect($this->service->has('custom:ttl_test'))->toBeTrue();
});

it('flushes all external API cache', function () {
    $this->service->put('character_data:flush_1', ['data' => 1]);
    $this->service->put('skills:flush_2', ['data' => 2]);

    $result = $this->service->flush();
    expect($result)->toBeTrue();
});

it('resets statistics', function () {
    $this->service->put('skills:reset_test', ['data' => true]);
    $this->service->get('skills:reset_test');

    $this->service->resetStatistics();

    $stats = $this->service->getStatistics();
    expect($stats['hits'])->toBe(0)
        ->and($stats['misses'])->toBe(0);
});
