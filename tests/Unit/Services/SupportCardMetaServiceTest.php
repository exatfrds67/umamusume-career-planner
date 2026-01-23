<?php

declare(strict_types=1);

use App\Models\SupportCardDefinition;
use App\Services\SupportCardMetaService;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->service = new SupportCardMetaService;
    Cache::flush();
});

describe('SupportCardMetaService', function (): void {
    describe('getTopCardsByType', function (): void {
        it('returns top cards for a given type', function (): void {
            SupportCardDefinition::factory()->count(5)->create([
                'card_type' => 'speed',
                'meta_tier' => 'S+',
                'is_active' => true,
                'usage_rate' => 90.0,
            ]);

            SupportCardDefinition::factory()->count(3)->create([
                'card_type' => 'speed',
                'meta_tier' => 'A',
                'is_active' => true,
                'usage_rate' => 50.0,
            ]);

            $result = $this->service->getTopCardsByType('speed', 3);

            expect($result)->toHaveCount(3);
        });

        it('returns empty collection for non-existent type', function (): void {
            $result = $this->service->getTopCardsByType('nonexistent', 5);

            expect($result)->toBeEmpty();
        });

        it('respects limit parameter', function (): void {
            SupportCardDefinition::factory()->count(10)->create([
                'card_type' => 'stamina',
                'is_active' => true,
            ]);

            $result = $this->service->getTopCardsByType('stamina', 5);

            expect($result)->toHaveCount(5);
        });
    });

    describe('getCardsByTier', function (): void {
        it('returns cards grouped by meta tier', function (): void {
            SupportCardDefinition::factory()->create(['meta_tier' => 'S+', 'is_active' => true]);
            SupportCardDefinition::factory()->create(['meta_tier' => 'S', 'is_active' => true]);
            SupportCardDefinition::factory()->create(['meta_tier' => 'A', 'is_active' => true]);
            SupportCardDefinition::factory()->create(['meta_tier' => 'B', 'is_active' => true]);
            SupportCardDefinition::factory()->create(['meta_tier' => 'C', 'is_active' => true]);

            $result = $this->service->getCardsByTier();

            expect($result)->toHaveKey('S+')
                ->and($result)->toHaveKey('S')
                ->and($result)->toHaveKey('A')
                ->and($result)->toHaveKey('B')
                ->and($result)->toHaveKey('C');
        });
    });

    describe('getCardSynergies', function (): void {
        it('returns synergy data for a card', function (): void {
            $synergyCard = SupportCardDefinition::factory()->create([
                'name' => 'Synergy Card',
                'is_active' => true,
            ]);

            $card = SupportCardDefinition::factory()->create([
                'deck_synergies' => [$synergyCard->name],
            ]);

            $result = $this->service->getCardSynergies($card->id);

            expect($result)->toHaveKey('card_name')
                ->and($result)->toHaveKey('synergy_cards');
        });

        it('returns empty synergies for card without synergies', function (): void {
            $card = SupportCardDefinition::factory()->create([
                'deck_synergies' => [],
            ]);

            $result = $this->service->getCardSynergies($card->id);

            expect($result['synergy_cards'])->toBeEmpty();
        });
    });

    describe('updateMetaTier', function (): void {
        it('updates meta tier for a card', function (): void {
            $card = SupportCardDefinition::factory()->create([
                'meta_tier' => 'B',
            ]);

            $result = $this->service->updateMetaTier($card->id, 'S');

            expect($result)->toBeTrue();

            $card->refresh();
            expect($card->meta_tier)->toBe('S');
        });

        it('updates meta tier with metadata', function (): void {
            $card = SupportCardDefinition::factory()->create([
                'meta_tier' => 'B',
                'usage_rate' => 50.0,
            ]);

            $result = $this->service->updateMetaTier($card->id, 'S+', [
                'usage_rate' => 95.0,
                'win_rate_contribution' => 85.0,
            ]);

            expect($result)->toBeTrue();

            $card->refresh();
            expect($card->meta_tier)->toBe('S+');
            expect((float) $card->usage_rate)->toEqual(95.0);
            expect((float) $card->win_rate_contribution)->toEqual(85.0);
        });
    });

    describe('getSkillProvisionMapping', function (): void {
        it('returns skill provision mapping for a card', function (): void {
            $card = SupportCardDefinition::factory()->create([
                'skill_hints_provided' => ['Speed Boost', 'Stamina Up'],
                'guaranteed_events' => ['Event 1'],
                'special_conditions' => ['Condition 1'],
            ]);

            $result = $this->service->getSkillProvisionMapping($card->id);

            expect($result)->toHaveKey('card_name')
                ->and($result)->toHaveKey('card_type')
                ->and($result)->toHaveKey('skills_provided')
                ->and($result['skills_provided'])->toContain('Speed Boost');
        });
    });

    describe('getAllSkillProvisionMappings', function (): void {
        it('returns all skill provision mappings', function (): void {
            SupportCardDefinition::factory()->count(3)->create([
                'is_active' => true,
                'skill_hints_provided' => ['Skill 1', 'Skill 2'],
            ]);

            $result = $this->service->getAllSkillProvisionMappings();

            expect($result)->toHaveCount(3);
            expect($result->first())->toHaveKey('skills_provided');
        });
    });

    describe('findCardsBySkill', function (): void {
        it('finds cards that provide a specific skill', function (): void {
            SupportCardDefinition::factory()->count(2)->create([
                'is_active' => true,
                'skill_hints_provided' => ['Speed Boost', 'Other Skill'],
            ]);

            SupportCardDefinition::factory()->create([
                'is_active' => true,
                'skill_hints_provided' => ['Different Skill'],
            ]);

            $result = $this->service->findCardsBySkill('Speed Boost');

            expect($result)->toHaveCount(2);
        });
    });

    describe('getRecommendedCardsForScenario', function (): void {
        it('returns recommended cards for a scenario', function (): void {
            SupportCardDefinition::factory()->count(3)->create([
                'is_active' => true,
                'recommended_scenarios' => ['ura_finale'],
            ]);

            SupportCardDefinition::factory()->count(2)->create([
                'is_active' => true,
                'recommended_scenarios' => ['unity_cup'],
            ]);

            $result = $this->service->getRecommendedCardsForScenario('ura_finale');

            expect($result)->toHaveCount(3);
        });
    });

    describe('bulkUpdateMetaTiers', function (): void {
        it('bulk updates meta tiers', function (): void {
            $card1 = SupportCardDefinition::factory()->create(['meta_tier' => 'B']);
            $card2 = SupportCardDefinition::factory()->create(['meta_tier' => 'C']);

            $updates = [
                ['internal_id' => $card1->internal_id, 'meta_tier' => 'S'],
                ['internal_id' => $card2->internal_id, 'meta_tier' => 'A'],
            ];

            $result = $this->service->bulkUpdateMetaTiers($updates);

            expect($result['success'])->toBe(2);
            expect($result['failed'])->toBe(0);

            $card1->refresh();
            $card2->refresh();
            expect($card1->meta_tier)->toBe('S');
            expect($card2->meta_tier)->toBe('A');
        });

        it('handles missing cards gracefully', function (): void {
            $updates = [
                ['internal_id' => 'nonexistent-id', 'meta_tier' => 'S'],
            ];

            $result = $this->service->bulkUpdateMetaTiers($updates);

            expect($result['failed'])->toBe(1);
            expect($result['errors'])->not->toBeEmpty();
        });
    });

    describe('caching', function (): void {
        it('caches top cards results', function (): void {
            SupportCardDefinition::factory()->count(5)->create([
                'card_type' => 'power',
                'is_active' => true,
            ]);

            // First call
            $result1 = $this->service->getTopCardsByType('power', 3);

            // Second call should use cache
            $result2 = $this->service->getTopCardsByType('power', 3);

            expect($result1->pluck('id')->toArray())
                ->toEqual($result2->pluck('id')->toArray());
        });
    });
});
