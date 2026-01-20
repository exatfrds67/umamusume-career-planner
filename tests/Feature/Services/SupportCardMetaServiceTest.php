<?php

use App\Models\SupportCardDefinition;
use App\Services\SupportCardMetaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;


beforeEach(function () {
    $this->service = new SupportCardMetaService;

    // Create test support cards
    $this->ssPlusCard = SupportCardDefinition::create([
        'name' => 'Test Card S+',
        'internal_id' => 'TEST_SC_SPLUS',
        'card_type' => 'speed',
        'rarity' => 'SSR',
        'meta_tier' => 'S+',
        'usage_rate' => 95.5,
        'win_rate_contribution' => 93.2,
        'is_active' => true,
        'skill_hints_provided' => ['Test Skill 1', 'Test Skill 2'],
        'recommended_scenarios' => ['URA Finale', 'Unity Cup'],
        'deck_synergies' => ['Test Card S', 'Test Card A'],
    ]);

    $this->sCard = SupportCardDefinition::create([
        'name' => 'Test Card S',
        'internal_id' => 'TEST_SC_S',
        'card_type' => 'stamina',
        'rarity' => 'SSR',
        'meta_tier' => 'S',
        'usage_rate' => 88.3,
        'win_rate_contribution' => 86.1,
        'is_active' => true,
        'skill_hints_provided' => ['Test Skill 2', 'Test Skill 3'],
        'recommended_scenarios' => ['URA Finale'],
    ]);

    $this->aCard = SupportCardDefinition::create([
        'name' => 'Test Card A',
        'internal_id' => 'TEST_SC_A',
        'card_type' => 'power',
        'rarity' => 'SSR',
        'meta_tier' => 'A',
        'usage_rate' => 75.8,
        'win_rate_contribution' => 73.5,
        'is_active' => true,
        'skill_hints_provided' => ['Test Skill 3', 'Test Skill 4'],
        'recommended_scenarios' => ['Unity Cup'],
    ]);
});

it('can get cards grouped by tier', function () {
    $cardsByTier = $this->service->getCardsByTier();

    expect($cardsByTier)->toBeArray()
        ->and($cardsByTier)->toHaveKeys(['S+', 'S', 'A', 'B', 'C'])
        ->and($cardsByTier['S+'])->toHaveCount(1)
        ->and($cardsByTier['S'])->toHaveCount(1)
        ->and($cardsByTier['A'])->toHaveCount(1);
});

it('can get top cards by type', function () {
    $topSpeedCards = $this->service->getTopCardsByType('speed', 5);

    expect($topSpeedCards)->toHaveCount(1)
        ->and($topSpeedCards->first()->name)->toBe('Test Card S+');
});

it('can update meta tier for a card', function () {
    $result = $this->service->updateMetaTier($this->aCard->id, 'S', [
        'usage_rate' => 85.0,
        'win_rate_contribution' => 83.0,
    ]);

    expect($result)->toBeTrue();

    $this->aCard->refresh();

    expect($this->aCard->meta_tier)->toBe('S')
        ->and((float) $this->aCard->usage_rate)->toBe(85.0)
        ->and((float) $this->aCard->win_rate_contribution)->toBe(83.0);
});

it('clears cache when updating meta tier', function () {
    Cache::shouldReceive('forget')->with('support_cards_by_tier')->once();
    Cache::shouldReceive('forget')->with('top_cards_power_5')->once();

    $this->service->updateMetaTier($this->aCard->id, 'S');
});

it('can get skill provision mapping for a card', function () {
    $mapping = $this->service->getSkillProvisionMapping($this->ssPlusCard->id);

    expect($mapping)->toBeArray()
        ->and($mapping)->toHaveKeys(['card_name', 'card_type', 'skills_provided', 'guaranteed_events', 'special_conditions'])
        ->and($mapping['card_name'])->toBe('Test Card S+')
        ->and($mapping['card_type'])->toBe('speed')
        ->and($mapping['skills_provided'])->toBe(['Test Skill 1', 'Test Skill 2']);
});

it('can get all skill provision mappings', function () {
    $mappings = $this->service->getAllSkillProvisionMappings();

    expect($mappings)->toHaveCount(3)
        ->and($mappings->first())->toHaveKeys(['id', 'name', 'card_type', 'skills_provided', 'meta_tier']);
});

it('can find cards by skill', function () {
    $cards = $this->service->findCardsBySkill('Test Skill 2');

    expect($cards)->toHaveCount(2)
        ->and($cards->pluck('name')->toArray())->toContain('Test Card S+', 'Test Card S');
});

it('can get recommended cards for scenario', function () {
    $uraCards = $this->service->getRecommendedCardsForScenario('URA Finale');

    expect($uraCards)->toHaveCount(2)
        ->and($uraCards->pluck('name')->toArray())->toContain('Test Card S+', 'Test Card S');

    $unityCards = $this->service->getRecommendedCardsForScenario('Unity Cup');

    expect($unityCards)->toHaveCount(2)
        ->and($unityCards->pluck('name')->toArray())->toContain('Test Card S+', 'Test Card A');
});

it('can get card synergies', function () {
    $synergies = $this->service->getCardSynergies($this->ssPlusCard->id);

    expect($synergies)->toBeArray()
        ->and($synergies)->toHaveKeys(['card_name', 'synergy_cards'])
        ->and($synergies['card_name'])->toBe('Test Card S+')
        ->and($synergies['synergy_cards'])->toHaveCount(2);
});

it('can bulk update meta tiers', function () {
    $updates = [
        [
            'internal_id' => 'TEST_SC_A',
            'meta_tier' => 'S',
            'metadata' => [
                'usage_rate' => 85.0,
            ],
        ],
        [
            'internal_id' => 'TEST_SC_S',
            'meta_tier' => 'S+',
            'metadata' => [
                'usage_rate' => 92.0,
            ],
        ],
    ];

    $results = $this->service->bulkUpdateMetaTiers($updates);

    expect($results['success'])->toBe(2)
        ->and($results['failed'])->toBe(0);

    $this->aCard->refresh();
    $this->sCard->refresh();

    expect($this->aCard->meta_tier)->toBe('S')
        ->and($this->sCard->meta_tier)->toBe('S+');
});

it('handles bulk update errors gracefully', function () {
    $updates = [
        [
            'internal_id' => 'NONEXISTENT_CARD',
            'meta_tier' => 'S',
        ],
        [
            'internal_id' => 'TEST_SC_A',
            'meta_tier' => 'S',
        ],
    ];

    $results = $this->service->bulkUpdateMetaTiers($updates);

    expect($results['success'])->toBe(1)
        ->and($results['failed'])->toBe(1)
        ->and($results['errors'])->toHaveCount(1);
});

it('can clear meta cache', function () {
    Cache::shouldReceive('forget')->times(10); // 1 main + 6 card types + 2 scenarios + 1 skill mappings

    $this->service->clearMetaCache();
});
