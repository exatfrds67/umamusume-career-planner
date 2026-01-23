<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use App\Services\DeckManagementService;
use App\Services\DeckOptimizationService;
use App\Services\FriendshipBondService;
use App\Services\SupportCardMetaService;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    /** @var SupportCardMetaService&Mockery\MockInterface $metaService */
    $metaService = Mockery::mock(SupportCardMetaService::class);
    /** @var DeckManagementService&Mockery\MockInterface $deckService */
    $deckService = Mockery::mock(DeckManagementService::class);
    /** @var FriendshipBondService&Mockery\MockInterface $friendshipService */
    $friendshipService = Mockery::mock(FriendshipBondService::class);

    $this->metaService = $metaService;
    $this->deckService = $deckService;
    $this->friendshipService = $friendshipService;

    $this->service = new DeckOptimizationService(
        $metaService,
        $deckService,
        $friendshipService
    );

    // Helper function to create mock deck collection
    $this->createMockDeck = function (array $cards): Collection {
        return collect(array_map(function ($cardData) {
            $supportCard = new stdClass;
            $supportCard->card_type = $cardData['card_type'];
            $supportCard->skill_hints_provided = $cardData['skill_hints_provided'] ?? [];
            $supportCard->meta_tier = $cardData['meta_tier'] ?? 'A';

            $card = new stdClass;
            $card->supportCard = $supportCard;
            $card->support_card_id = random_int(1, 1000);

            return $card;
        }, $cards));
    };

    // Helper function to create mock deck with specific IDs
    $this->createMockDeckWithIds = function (array $cards): Collection {
        return collect(array_map(function ($cardData, $index) {
            $supportCard = new stdClass;
            $supportCard->name = $cardData['name'];
            $supportCard->card_type = $cardData['card_type'];
            $supportCard->meta_tier = $cardData['meta_tier'] ?? 'A';
            $supportCard->skill_hints_provided = $cardData['skill_hints_provided'] ?? [];
            $supportCard->recommended_scenarios = $cardData['recommended_scenarios'] ?? [];

            $card = new stdClass;
            $card->supportCard = $supportCard;
            $card->support_card_id = $cardData['id'];
            $card->position_slot = $index + 1;

            return $card;
        }, $cards, array_keys($cards)));
    };
});

afterEach(function (): void {
    Mockery::close();
});

describe('DeckOptimizationService', function (): void {
    describe('analyzeDeckComposition', function (): void {
        it('returns empty analysis for empty deck', function (): void {
            $this->deckService->shouldReceive('getDeck')
                ->with(1)
                ->andReturn(collect([]));

            $result = $this->service->analyzeDeckComposition(1);

            expect($result)->toHaveKey('stat_coverage')
                ->and($result)->toHaveKey('skill_provision_coverage')
                ->and($result)->toHaveKey('gaps')
                ->and($result)->toHaveKey('coverage_score')
                ->and($result['coverage_score'])->toBe(0)
                ->and($result['gaps']['missing_stats'])->toContain('speed', 'stamina', 'power', 'guts', 'wit');
        });

        it('calculates stat coverage correctly', function (): void {
            $deck = ($this->createMockDeck)([
                ['card_type' => 'speed', 'skill_hints_provided' => ['Speed Boost']],
                ['card_type' => 'speed', 'skill_hints_provided' => ['Acceleration']],
                ['card_type' => 'stamina', 'skill_hints_provided' => ['Recovery']],
                ['card_type' => 'power', 'skill_hints_provided' => ['Power Up']],
                ['card_type' => 'guts', 'skill_hints_provided' => ['Guts Boost']],
                ['card_type' => 'friend', 'skill_hints_provided' => ['Friendship']],
            ]);

            $this->deckService->shouldReceive('getDeck')
                ->with(1)
                ->andReturn($deck);

            $result = $this->service->analyzeDeckComposition(1);

            expect($result['stat_coverage']['counts']['speed'])->toBe(2)
                ->and($result['stat_coverage']['counts']['stamina'])->toBe(1)
                ->and($result['stat_coverage']['counts']['power'])->toBe(1)
                ->and($result['stat_coverage']['counts']['guts'])->toBe(1)
                ->and($result['stat_coverage']['counts']['friend'])->toBe(1)
                ->and($result['stat_coverage']['is_balanced'])->toBeTrue();
        });

        it('identifies missing stats as gaps', function (): void {
            $deck = ($this->createMockDeck)([
                ['card_type' => 'speed', 'skill_hints_provided' => []],
                ['card_type' => 'speed', 'skill_hints_provided' => []],
                ['card_type' => 'speed', 'skill_hints_provided' => []],
            ]);

            $this->deckService->shouldReceive('getDeck')
                ->with(1)
                ->andReturn($deck);

            $result = $this->service->analyzeDeckComposition(1);

            expect($result['gaps']['missing_stats'])->toContain('stamina', 'power', 'guts', 'wit')
                ->and($result['gaps']['has_critical_gaps'])->toBeTrue();
        });
    });

    describe('analyzeDeckSynergy', function (): void {
        it('returns empty synergy for empty deck', function (): void {
            $this->deckService->shouldReceive('getDeck')
                ->with(1)
                ->andReturn(collect([]));

            $result = $this->service->analyzeDeckSynergy(1);

            expect($result['synergy_score'])->toBe(0)
                ->and($result['synergy_pairs'])->toBeEmpty()
                ->and($result['recommendations'])->toContain('Add cards to analyze synergy');
        });

        it('identifies synergy pairs correctly', function (): void {
            $deck = ($this->createMockDeckWithIds)([
                ['id' => 1, 'name' => 'Card A', 'card_type' => 'speed'],
                ['id' => 2, 'name' => 'Card B', 'card_type' => 'speed'],
            ]);

            $this->deckService->shouldReceive('getDeck')
                ->with(1)
                ->andReturn($deck);

            $this->metaService->shouldReceive('getCardSynergies')
                ->with(1)
                ->andReturn(['synergy_cards' => [['id' => 2, 'name' => 'Card B']]]);

            $this->metaService->shouldReceive('getCardSynergies')
                ->with(2)
                ->andReturn(['synergy_cards' => []]);

            $result = $this->service->analyzeDeckSynergy(1);

            expect($result['synergy_pairs'])->toHaveCount(1)
                ->and($result['synergy_pairs'][0]['card1'])->toBe('Card A')
                ->and($result['synergy_pairs'][0]['card2'])->toBe('Card B');
        });

        it('analyzes strategic alignment', function (): void {
            $deck = ($this->createMockDeckWithIds)([
                ['id' => 1, 'name' => 'Card A', 'card_type' => 'speed', 'meta_tier' => 'S'],
                ['id' => 2, 'name' => 'Card B', 'card_type' => 'speed', 'meta_tier' => 'S'],
                ['id' => 3, 'name' => 'Card C', 'card_type' => 'speed', 'meta_tier' => 'A'],
                ['id' => 4, 'name' => 'Card D', 'card_type' => 'speed', 'meta_tier' => 'A'],
                ['id' => 5, 'name' => 'Card E', 'card_type' => 'speed', 'meta_tier' => 'A'],
                ['id' => 6, 'name' => 'Card F', 'card_type' => 'friend', 'meta_tier' => 'S'],
            ]);

            $this->deckService->shouldReceive('getDeck')
                ->with(1)
                ->andReturn($deck);

            $this->metaService->shouldReceive('getCardSynergies')
                ->andReturn(['synergy_cards' => []]);

            $result = $this->service->analyzeDeckSynergy(1);

            expect($result['strategic_alignment']['primary_strategy'])->toBe('Speed Focus')
                ->and($result['strategic_alignment']['is_well_aligned'])->toBeTrue();
        });
    });

    describe('optimizeForMetaTier', function (): void {
        it('calculates meta score correctly', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create([
                'user_id' => $user->id,
                'scenario_type' => 'ura_finale',
                'stat_priorities' => ['speed' => 1, 'stamina' => 2],
            ]);

            $deck = ($this->createMockDeckWithIds)([
                ['id' => 1, 'name' => 'Card A', 'card_type' => 'speed', 'meta_tier' => 'S+'],
                ['id' => 2, 'name' => 'Card B', 'card_type' => 'speed', 'meta_tier' => 'S'],
                ['id' => 3, 'name' => 'Card C', 'card_type' => 'stamina', 'meta_tier' => 'A'],
            ]);

            $this->deckService->shouldReceive('getDeck')
                ->with($character->id)
                ->andReturn($deck);

            $this->metaService->shouldReceive('getTopCardsByType')
                ->andReturn(collect([]));

            $result = $this->service->optimizeForMetaTier($character->id);

            expect($result)->toHaveKey('meta_score')
                ->and($result)->toHaveKey('build_compatibility')
                ->and($result)->toHaveKey('optimization_suggestions')
                ->and($result['meta_score']['total_score'])->toBeGreaterThan(0);
        });

        it('suggests replacements for low-tier cards', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create([
                'user_id' => $user->id,
                'scenario_type' => 'ura_finale',
            ]);

            $deck = ($this->createMockDeckWithIds)([
                ['id' => 1, 'name' => 'Low Tier Card', 'card_type' => 'speed', 'meta_tier' => 'C'],
            ]);

            $betterCards = collect([
                (object) ['id' => 10, 'name' => 'Better Card', 'meta_tier' => 'S', 'rarity' => 'SSR'],
            ]);

            $this->deckService->shouldReceive('getDeck')
                ->with($character->id)
                ->andReturn($deck);

            $this->metaService->shouldReceive('getTopCardsByType')
                ->with('speed', 3)
                ->andReturn($betterCards);

            $result = $this->service->optimizeForMetaTier($character->id);

            expect($result['recommended_replacements'])->not->toBeEmpty()
                ->and($result['recommended_replacements'][0]['current_card'])->toBe('Low Tier Card')
                ->and($result['recommended_replacements'][0]['current_tier'])->toBe('C');
        });
    });

    describe('recommendDeck', function (): void {
        it('generates deck recommendations for character', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create([
                'user_id' => $user->id,
                'scenario_type' => 'ura_finale',
                'stat_priorities' => ['speed' => 1, 'stamina' => 2, 'power' => 3],
            ]);

            $speedCards = collect([
                (object) ['id' => 1, 'name' => 'Speed Card 1', 'card_type' => 'speed', 'meta_tier' => 'S+', 'rarity' => 'SSR'],
                (object) ['id' => 2, 'name' => 'Speed Card 2', 'card_type' => 'speed', 'meta_tier' => 'S', 'rarity' => 'SSR'],
            ]);

            $staminaCards = collect([
                (object) ['id' => 3, 'name' => 'Stamina Card', 'card_type' => 'stamina', 'meta_tier' => 'S', 'rarity' => 'SSR'],
            ]);

            $powerCards = collect([
                (object) ['id' => 4, 'name' => 'Power Card', 'card_type' => 'power', 'meta_tier' => 'A', 'rarity' => 'SSR'],
            ]);

            $friendCards = collect([
                (object) ['id' => 5, 'name' => 'Friend Card', 'card_type' => 'friend', 'meta_tier' => 'S', 'rarity' => 'SSR'],
            ]);

            $this->metaService->shouldReceive('getTopCardsByType')
                ->with('speed', 5)
                ->andReturn($speedCards);

            $this->metaService->shouldReceive('getTopCardsByType')
                ->with('stamina', 5)
                ->andReturn($staminaCards);

            $this->metaService->shouldReceive('getTopCardsByType')
                ->with('power', 5)
                ->andReturn($powerCards);

            $this->metaService->shouldReceive('getTopCardsByType')
                ->with('friend', 1)
                ->andReturn($friendCards);

            $result = $this->service->recommendDeck($character->id);

            expect($result)->toHaveKey('character_id')
                ->and($result)->toHaveKey('recommended_deck')
                ->and($result)->toHaveKey('expected_performance')
                ->and($result)->toHaveKey('reasoning')
                ->and($result['character_id'])->toBe($character->id);
        });
    });

    describe('getComprehensiveAnalysis', function (): void {
        it('returns all analysis components', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create([
                'user_id' => $user->id,
            ]);

            $this->deckService->shouldReceive('getDeck')
                ->andReturn(collect([]));

            $this->deckService->shouldReceive('getDeckStatistics')
                ->with($character->id)
                ->andReturn(['total_cards' => 0]);

            $this->friendshipService->shouldReceive('getDeckFriendshipOverview')
                ->with($character->id)
                ->andReturn(['average_friendship' => 0]);

            $this->metaService->shouldReceive('getTopCardsByType')
                ->andReturn(collect([]));

            $result = $this->service->getComprehensiveAnalysis($character->id);

            expect($result)->toHaveKey('composition')
                ->and($result)->toHaveKey('synergy')
                ->and($result)->toHaveKey('meta_optimization')
                ->and($result)->toHaveKey('friendship_overview')
                ->and($result)->toHaveKey('deck_statistics');
        });
    });
});
