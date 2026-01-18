<?php

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use App\Models\User;
use App\Services\DeckManagementService;
use App\Services\DeckOptimizationService;
use App\Services\FriendshipBondService;
use App\Services\SupportCardMetaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'scenario_type' => 'ura_finale',
        'stat_priorities' => [
            'speed' => 5,
            'stamina' => 4,
            'power' => 3,
        ],
    ]);

    $this->metaService = app(SupportCardMetaService::class);
    $this->deckService = app(DeckManagementService::class);
    $this->friendshipService = app(FriendshipBondService::class);
    $this->service = app(DeckOptimizationService::class);
});

describe('Deck Composition Analysis', function () {
    it('analyzes empty deck composition', function () {
        $analysis = $this->service->analyzeDeckComposition($this->character->id);

        expect($analysis)->toHaveKeys(['stat_coverage', 'skill_provision_coverage', 'gaps', 'coverage_score', 'recommendations']);
        expect($analysis['coverage_score'])->toBe(0);
        expect($analysis['gaps']['missing_stats'])->toContain('speed', 'stamina', 'power', 'guts', 'wit');
    });

    it('analyzes balanced deck composition', function () {
        // Create support cards with different types
        $speedCard = SupportCardDefinition::factory()->create(['card_type' => 'speed', 'meta_tier' => 'S']);
        $staminaCard = SupportCardDefinition::factory()->create(['card_type' => 'stamina', 'meta_tier' => 'S']);
        $powerCard = SupportCardDefinition::factory()->create(['card_type' => 'power', 'meta_tier' => 'A']);
        $gutsCard = SupportCardDefinition::factory()->create(['card_type' => 'guts', 'meta_tier' => 'A']);
        $witCard = SupportCardDefinition::factory()->create(['card_type' => 'wit', 'meta_tier' => 'B']);
        $friendCard = SupportCardDefinition::factory()->create(['card_type' => 'friend', 'meta_tier' => 'S']);

        // Add cards to deck
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $speedCard->id,
            'position_slot' => 1,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $staminaCard->id,
            'position_slot' => 2,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $powerCard->id,
            'position_slot' => 3,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $gutsCard->id,
            'position_slot' => 4,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $witCard->id,
            'position_slot' => 5,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $friendCard->id,
            'position_slot' => 6,
            'is_friend_card' => true,
        ]);

        $analysis = $this->service->analyzeDeckComposition($this->character->id);

        expect($analysis['stat_coverage']['is_balanced'])->toBeTrue();
        expect($analysis['coverage_score'])->toBeGreaterThanOrEqual(60);
        expect($analysis['gaps']['missing_stats'])->toBeEmpty();
    });

    it('identifies stat coverage gaps', function () {
        // Create deck with only speed cards
        $speedCard1 = SupportCardDefinition::factory()->create(['card_type' => 'speed', 'meta_tier' => 'S']);
        $speedCard2 = SupportCardDefinition::factory()->create(['card_type' => 'speed', 'meta_tier' => 'S']);
        $speedCard3 = SupportCardDefinition::factory()->create(['card_type' => 'speed', 'meta_tier' => 'A']);

        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $speedCard1->id,
            'position_slot' => 1,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $speedCard2->id,
            'position_slot' => 2,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $speedCard3->id,
            'position_slot' => 3,
        ]);

        $analysis = $this->service->analyzeDeckComposition($this->character->id);

        expect($analysis['stat_coverage']['is_balanced'])->toBeFalse();
        expect($analysis['gaps']['missing_stats'])->toContain('stamina', 'power', 'guts', 'wit');
        expect($analysis['gaps']['has_critical_gaps'])->toBeTrue();
    });

    it('calculates skill provision coverage', function () {
        $card1 = SupportCardDefinition::factory()->create([
            'card_type' => 'speed',
            'skill_hints_provided' => ['Speed Boost', 'Acceleration'],
        ]);
        $card2 = SupportCardDefinition::factory()->create([
            'card_type' => 'stamina',
            'skill_hints_provided' => ['Stamina Recovery', 'Endurance'],
        ]);

        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $card1->id,
            'position_slot' => 1,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $card2->id,
            'position_slot' => 2,
        ]);

        $analysis = $this->service->analyzeDeckComposition($this->character->id);

        expect($analysis['skill_provision_coverage']['total_skills'])->toBe(4);
        expect($analysis['skill_provision_coverage']['skill_diversity_score'])->toBeGreaterThan(0);
    });
});

describe('Deck Synergy Analysis', function () {
    it('analyzes empty deck synergy', function () {
        $analysis = $this->service->analyzeDeckSynergy($this->character->id);

        expect($analysis)->toHaveKeys(['synergy_score', 'synergy_pairs', 'strategic_alignment', 'recommendations']);
        expect($analysis['synergy_score'])->toBe(0);
        expect($analysis['synergy_pairs'])->toBeEmpty();
    });

    it('identifies synergy pairs in deck', function () {
        $card1 = SupportCardDefinition::factory()->create([
            'name' => 'Kitasan Black',
            'card_type' => 'speed',
            'deck_synergies' => ['Narita Brian'],
        ]);
        $card2 = SupportCardDefinition::factory()->create([
            'name' => 'Narita Brian',
            'card_type' => 'power',
            'deck_synergies' => ['Kitasan Black'],
        ]);

        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $card1->id,
            'position_slot' => 1,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $card2->id,
            'position_slot' => 2,
        ]);

        $analysis = $this->service->analyzeDeckSynergy($this->character->id);

        expect($analysis['synergy_pairs'])->not->toBeEmpty();
        expect($analysis['synergy_score'])->toBeGreaterThan(0);
    });

    it('analyzes strategic alignment for focused deck', function () {
        // Create speed-focused deck
        for ($i = 1; $i <= 4; $i++) {
            $card = SupportCardDefinition::factory()->create(['card_type' => 'speed']);
            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $card->id,
                'position_slot' => $i,
            ]);
        }

        $analysis = $this->service->analyzeDeckSynergy($this->character->id);

        expect($analysis['strategic_alignment']['primary_strategy'])->toContain('Speed Focus');
        expect($analysis['strategic_alignment']['alignment_score'])->toBeGreaterThan(50);
    });

    it('analyzes strategic alignment for balanced deck', function () {
        $types = ['speed', 'stamina', 'power', 'guts'];

        foreach ($types as $index => $type) {
            $card = SupportCardDefinition::factory()->create(['card_type' => $type]);
            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $card->id,
                'position_slot' => $index + 1,
            ]);
        }

        $analysis = $this->service->analyzeDeckSynergy($this->character->id);

        expect($analysis['strategic_alignment']['primary_strategy'])->toBe('Balanced');
        expect($analysis['strategic_alignment']['is_well_aligned'])->toBeTrue();
    });
});

describe('Meta Tier Optimization', function () {
    it('calculates meta score for deck', function () {
        $splusCard = SupportCardDefinition::factory()->create(['meta_tier' => 'S+']);
        $sCard = SupportCardDefinition::factory()->create(['meta_tier' => 'S']);
        $aCard = SupportCardDefinition::factory()->create(['meta_tier' => 'A']);

        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $splusCard->id,
            'position_slot' => 1,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $sCard->id,
            'position_slot' => 2,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $aCard->id,
            'position_slot' => 3,
        ]);

        $optimization = $this->service->optimizeForMetaTier($this->character->id);

        expect($optimization['meta_score']['total_score'])->toBeGreaterThan(60);
        expect($optimization['meta_score']['tier_breakdown'])->toHaveKeys(['S+', 'S', 'A']);
    });

    it('analyzes build compatibility with character priorities', function () {
        // Create cards matching character stat priorities
        $speedCard = SupportCardDefinition::factory()->create([
            'card_type' => 'speed',
            'meta_tier' => 'S',
            'recommended_scenarios' => ['ura_finale'],
        ]);
        $staminaCard = SupportCardDefinition::factory()->create([
            'card_type' => 'stamina',
            'meta_tier' => 'S',
            'recommended_scenarios' => ['ura_finale'],
        ]);

        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $speedCard->id,
            'position_slot' => 1,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $staminaCard->id,
            'position_slot' => 2,
        ]);

        $optimization = $this->service->optimizeForMetaTier($this->character->id);

        expect($optimization['build_compatibility']['is_compatible'])->toBeTrue();
        expect($optimization['build_compatibility']['matches'])->not->toBeEmpty();
    });

    it('suggests replacements for low-tier cards', function () {
        // Create low-tier card
        $lowTierCard = SupportCardDefinition::factory()->create([
            'card_type' => 'speed',
            'meta_tier' => 'C',
        ]);

        // Create high-tier alternatives
        SupportCardDefinition::factory()->count(3)->create([
            'card_type' => 'speed',
            'meta_tier' => 'S+',
        ]);

        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $lowTierCard->id,
            'position_slot' => 1,
        ]);

        $optimization = $this->service->optimizeForMetaTier($this->character->id);

        expect($optimization['recommended_replacements'])->not->toBeEmpty();
        expect($optimization['recommended_replacements'][0]['current_tier'])->toBe('C');
        expect($optimization['recommended_replacements'][0]['suggested_replacements'])->not->toBeEmpty();
    });

    it('generates optimization suggestions based on meta score', function () {
        // Create low meta score deck
        for ($i = 1; $i <= 3; $i++) {
            $card = SupportCardDefinition::factory()->create(['meta_tier' => 'C']);
            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $card->id,
                'position_slot' => $i,
            ]);
        }

        $optimization = $this->service->optimizeForMetaTier($this->character->id);

        expect($optimization['optimization_suggestions'])->not->toBeEmpty();
        expect($optimization['meta_score']['total_score'])->toBeLessThan(60);
    });
});

describe('Deck Recommendations', function () {
    it('generates deck recommendations for character', function () {
        // Create various support cards - ensure enough for all slots
        SupportCardDefinition::factory()->count(3)->create(['card_type' => 'speed', 'meta_tier' => 'S+']);
        SupportCardDefinition::factory()->count(3)->create(['card_type' => 'stamina', 'meta_tier' => 'S']);
        SupportCardDefinition::factory()->count(3)->create(['card_type' => 'power', 'meta_tier' => 'A']);
        SupportCardDefinition::factory()->create(['card_type' => 'friend', 'meta_tier' => 'S']);

        $recommendations = $this->service->recommendDeck($this->character->id);

        expect($recommendations)->toHaveKeys([
            'character_id',
            'character_name',
            'scenario',
            'stat_priorities',
            'recommended_deck',
            'expected_performance',
            'reasoning',
        ]);
        expect($recommendations['recommended_deck'])->toHaveCount(6);
    });

    it('prioritizes stat priorities in recommendations', function () {
        // Create cards for priority stats
        SupportCardDefinition::factory()->count(3)->create(['card_type' => 'speed', 'meta_tier' => 'S+']);
        SupportCardDefinition::factory()->count(2)->create(['card_type' => 'stamina', 'meta_tier' => 'S']);
        SupportCardDefinition::factory()->create(['card_type' => 'friend', 'meta_tier' => 'S']);

        $recommendations = $this->service->recommendDeck($this->character->id);

        $speedCards = array_filter($recommendations['recommended_deck'], fn ($card) => $card['card_type'] === 'speed');
        $staminaCards = array_filter($recommendations['recommended_deck'], fn ($card) => $card['card_type'] === 'stamina');

        expect(count($speedCards))->toBeGreaterThanOrEqual(2); // Highest priority gets 2 cards
        expect(count($staminaCards))->toBeGreaterThanOrEqual(1);
    });

    it('includes friend card in recommendations', function () {
        SupportCardDefinition::factory()->count(5)->create(['card_type' => 'speed', 'meta_tier' => 'S']);
        SupportCardDefinition::factory()->create(['card_type' => 'friend', 'meta_tier' => 'S']);

        $recommendations = $this->service->recommendDeck($this->character->id);

        $friendCards = array_filter($recommendations['recommended_deck'], fn ($card) => $card['is_friend_card'] === true);

        expect(count($friendCards))->toBe(1);
        expect($friendCards[array_key_first($friendCards)]['position_slot'])->toBe(6);
    });

    it('estimates performance for recommended deck', function () {
        SupportCardDefinition::factory()->count(5)->create(['card_type' => 'speed', 'meta_tier' => 'S+']);
        SupportCardDefinition::factory()->create(['card_type' => 'friend', 'meta_tier' => 'S+']);

        $recommendations = $this->service->recommendDeck($this->character->id);

        expect($recommendations['expected_performance']['performance_score'])->toBeGreaterThan(80);
        expect($recommendations['expected_performance']['expected_outcome'])->toContain('Excellent');
    });

    it('provides reasoning for recommendations', function () {
        SupportCardDefinition::factory()->count(3)->create(['card_type' => 'speed', 'meta_tier' => 'S+']);
        SupportCardDefinition::factory()->count(2)->create(['card_type' => 'stamina', 'meta_tier' => 'S']);
        SupportCardDefinition::factory()->create(['card_type' => 'friend', 'meta_tier' => 'S']);

        $recommendations = $this->service->recommendDeck($this->character->id);

        expect($recommendations['reasoning'])->not->toBeEmpty();
        expect($recommendations['reasoning'][0])->toContain('ura_finale');
    });

    it('caches deck recommendations', function () {
        SupportCardDefinition::factory()->count(5)->create(['card_type' => 'speed', 'meta_tier' => 'S']);
        SupportCardDefinition::factory()->create(['card_type' => 'friend', 'meta_tier' => 'S']);

        // First call
        $recommendations1 = $this->service->recommendDeck($this->character->id);

        // Second call should be cached
        $recommendations2 = $this->service->recommendDeck($this->character->id);

        expect($recommendations1)->toEqual($recommendations2);
    });
});

describe('Comprehensive Analysis', function () {
    it('provides comprehensive deck analysis', function () {
        // Create a complete deck
        $speedCard = SupportCardDefinition::factory()->create(['card_type' => 'speed', 'meta_tier' => 'S']);
        $staminaCard = SupportCardDefinition::factory()->create(['card_type' => 'stamina', 'meta_tier' => 'S']);
        $powerCard = SupportCardDefinition::factory()->create(['card_type' => 'power', 'meta_tier' => 'A']);
        $gutsCard = SupportCardDefinition::factory()->create(['card_type' => 'guts', 'meta_tier' => 'A']);
        $witCard = SupportCardDefinition::factory()->create(['card_type' => 'wit', 'meta_tier' => 'B']);
        $friendCard = SupportCardDefinition::factory()->create(['card_type' => 'friend', 'meta_tier' => 'S']);

        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $speedCard->id,
            'position_slot' => 1,
            'friendship_level' => 80,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $staminaCard->id,
            'position_slot' => 2,
            'friendship_level' => 60,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $powerCard->id,
            'position_slot' => 3,
            'friendship_level' => 40,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $gutsCard->id,
            'position_slot' => 4,
            'friendship_level' => 20,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $witCard->id,
            'position_slot' => 5,
            'friendship_level' => 10,
        ]);
        CharacterSupportCard::factory()->create([
            'character_id' => $this->character->id,
            'support_card_id' => $friendCard->id,
            'position_slot' => 6,
            'is_friend_card' => true,
            'friendship_level' => 50,
        ]);

        $analysis = $this->service->getComprehensiveAnalysis($this->character->id);

        expect($analysis)->toHaveKeys([
            'composition',
            'synergy',
            'meta_optimization',
            'friendship_overview',
            'deck_statistics',
        ]);
        expect($analysis['composition']['coverage_score'])->toBeGreaterThan(0);
        expect($analysis['synergy']['synergy_score'])->toBeGreaterThan(0);
        expect($analysis['meta_optimization']['meta_score']['total_score'])->toBeGreaterThan(0);
        expect($analysis['friendship_overview']['total_cards'])->toBe(6);
        expect($analysis['deck_statistics']['total_cards'])->toBe(6);
    });
});
