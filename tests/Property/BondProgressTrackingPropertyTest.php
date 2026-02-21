<?php

declare(strict_types=1);

use App\Enums\CareerPhase;
use App\Enums\Mood;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CharacterStats;
use App\ValueObjects\SupportCard;
use App\ValueObjects\SupportCardDeck;
use App\ValueObjects\TrainingContext;

/**
 * Property 12: Bond Progress Tracking
 *
 * Validates: Requirements 3.5
 *
 * Bond progress must track correctly toward 80 threshold for Friendship Training.
 */
describe('Property 12: Bond Progress Tracking', function () {
    beforeEach(function () {
        // Mock the dependencies to avoid AWS credential issues
        $this->mock(\App\Services\Neuron\NeuronAIService::class);
        $this->mock(\App\Services\RuleBasedAdvisor::class);
        $this->mock(\App\Services\GameMechanicsEngine::class);
        $this->mock(\App\Services\PredictionAccuracyTracker::class);
        $this->mock(\App\Services\CriticalSituationDetector::class);

        $this->advisoryService = app(TrainingAdvisoryService::class);
    });

    it('categorizes cards correctly by bond level threshold', function () {
        // Generate various bond levels around the 80 threshold
        $bondLevels = [60, 70, 75, 79, 80, 85, 90, 95];

        foreach ($bondLevels as $bondLevel) {
            $context = generateTrainingContextWithBond($bondLevel);

            $analysis = $this->advisoryService->analyzeSupportCardDeck($context);

            if ($bondLevel >= 80) {
                expect($analysis->cardsReadyForFriendship)->toHaveCount(1);
                expect($analysis->cardsNotReadyForFriendship)->toBeEmpty();
                expect($analysis->cardsReadyForFriendship[0]->bond)->toBe($bondLevel);
            } else {
                expect($analysis->cardsReadyForFriendship)->toBeEmpty();
                expect($analysis->cardsNotReadyForFriendship)->toHaveCount(1);
                expect($analysis->cardsNotReadyForFriendship[0]->bond)->toBe($bondLevel);
            }
        }
    })->group('property', 'bond-tracking');

    it('handles multiple cards with mixed bond levels', function () {
        $bondLevels = [60, 70, 75, 79, 80, 85];
        $context = generateTrainingContextWithBonds($bondLevels);

        $analysis = $this->advisoryService->analyzeSupportCardDeck($context);

        // Cards with bond >= 80: [80, 85] = 2 cards
        expect($analysis->cardsReadyForFriendship)->toHaveCount(2);
        // Cards with bond < 80: [60, 70, 75, 79] = 4 cards
        expect($analysis->cardsNotReadyForFriendship)->toHaveCount(4);

        // Verify all ready cards have bond >= 80
        foreach ($analysis->cardsReadyForFriendship as $card) {
            expect($card->bond)->toBeGreaterThanOrEqual(80);
        }

        // Verify all not-ready cards have bond < 80
        foreach ($analysis->cardsNotReadyForFriendship as $card) {
            expect($card->bond)->toBeLessThan(80);
        }
    })->group('property', 'bond-tracking');

    it('handles edge case of exactly 80 bond', function () {
        $context = generateTrainingContextWithBond(80);

        $analysis = $this->advisoryService->analyzeSupportCardDeck($context);

        // Bond of exactly 80 should be ready for Friendship Training
        expect($analysis->cardsReadyForFriendship)->toHaveCount(1);
        expect($analysis->cardsNotReadyForFriendship)->toBeEmpty();
        expect($analysis->cardsReadyForFriendship[0]->bond)->toBe(80);
    })->group('property', 'bond-tracking');

    it('handles empty deck', function () {
        $context = generateTrainingContextWithBonds([]);

        $analysis = $this->advisoryService->analyzeSupportCardDeck($context);

        expect($analysis->cardsReadyForFriendship)->toBeEmpty();
        expect($analysis->cardsNotReadyForFriendship)->toBeEmpty();
    })->group('property', 'bond-tracking');

    it('handles all cards ready for friendship training', function () {
        $bondLevels = [80, 85, 90, 95, 100];
        $context = generateTrainingContextWithBonds($bondLevels);

        $analysis = $this->advisoryService->analyzeSupportCardDeck($context);

        expect($analysis->cardsReadyForFriendship)->toHaveCount(5);
        expect($analysis->cardsNotReadyForFriendship)->toBeEmpty();
    })->group('property', 'bond-tracking');

    it('handles all cards not ready for friendship training', function () {
        $bondLevels = [10, 20, 30, 40, 50, 60]; // Reduced to 6 cards (max deck size)
        $context = generateTrainingContextWithBonds($bondLevels);

        $analysis = $this->advisoryService->analyzeSupportCardDeck($context);

        expect($analysis->cardsReadyForFriendship)->toBeEmpty();
        expect($analysis->cardsNotReadyForFriendship)->toHaveCount(6);
    })->group('property', 'bond-tracking');
});

/**
 * Helper: Generate TrainingContext with a single card at specified bond level
 */
function generateTrainingContextWithBond(int $bondLevel): TrainingContext
{
    $card = new SupportCard(
        id: 1,
        bond: $bondLevel,
        facility: 'speed',
        limitBreak: 0,
        name: 'Test Card'
    );

    $deck = new SupportCardDeck([$card]);

    return new TrainingContext(
        turnNumber: 15,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(
            speed: 400,
            stamina: 350,
            power: 380,
            guts: 320,
            wisdom: 360
        ),
        spAvailable: 150,
        energy: 80,
        mood: Mood::NORMAL,
        acquiredSkills: [],
        skillHints: [],
        deck: $deck,
        facilityLevels: [
            'speed' => 2,
            'stamina' => 2,
            'power' => 2,
            'guts' => 2,
            'wisdom' => 2,
        ],
        upcomingRaces: [],
        scenario: null,
        storageMode: 'account',
        careerRunId: 1
    );
}

/**
 * Helper: Generate TrainingContext with multiple cards at specified bond levels
 *
 * @param  array<int, int>  $bondLevels
 */
function generateTrainingContextWithBonds(array $bondLevels): TrainingContext
{
    $cards = [];
    foreach ($bondLevels as $index => $bondLevel) {
        $cards[] = new SupportCard(
            id: $index + 1,
            bond: $bondLevel,
            facility: 'speed',
            limitBreak: 0,
            name: "Test Card {$index}"
        );
    }

    $deck = new SupportCardDeck($cards);

    return new TrainingContext(
        turnNumber: 15,
        phase: CareerPhase::CLASSIC,
        stats: new CharacterStats(
            speed: 400,
            stamina: 350,
            power: 380,
            guts: 320,
            wisdom: 360
        ),
        spAvailable: 150,
        energy: 80,
        mood: Mood::NORMAL,
        acquiredSkills: [],
        skillHints: [],
        deck: $deck,
        facilityLevels: [
            'speed' => 2,
            'stamina' => 2,
            'power' => 2,
            'guts' => 2,
            'wisdom' => 2,
        ],
        upcomingRaces: [],
        scenario: null,
        storageMode: 'account',
        careerRunId: 1
    );
}
