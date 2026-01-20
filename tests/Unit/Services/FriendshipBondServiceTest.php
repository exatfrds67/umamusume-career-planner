<?php

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use App\Services\FriendshipBondService;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

/** @var FriendshipBondService $service */
$service = null;
/** @var Character $character */
$character = null;
/** @var SupportCardDefinition $supportCard */
$supportCard = null;
/** @var CharacterSupportCard $characterSupportCard */
$characterSupportCard = null;

beforeEach(function () use (&$service, &$character, &$supportCard, &$characterSupportCard) {
    $service = new FriendshipBondService;

    // Create test data
    $character = Character::factory()->create();
    $supportCard = SupportCardDefinition::factory()->create([
        'name' => 'Test Support Card',
        'card_type' => 'speed',
    ]);

    $characterSupportCard = CharacterSupportCard::create([
        'character_id' => $character->id,
        'support_card_id' => $supportCard->id,
        'position_slot' => 1,
        'friendship_level' => 0,
        'limit_break_level' => 0,
        'is_friend_card' => false,
    ]);
});

it('updates friendship level correctly', function () use (&$service, &$characterSupportCard) {
    $result = $service->updateFriendshipLevel($characterSupportCard->id, 5);

    expect($result)->toHaveKeys(['old_level', 'new_level', 'points_gained', 'rainbow_training_available', 'rainbow_unlocked'])
        ->and($result['old_level'])->toBe(0)
        ->and($result['new_level'])->toBe(5)
        ->and($result['points_gained'])->toBe(5)
        ->and($result['rainbow_training_available'])->toBeFalse()
        ->and($result['rainbow_unlocked'])->toBeFalse();

    $characterSupportCard->refresh();
    expect($characterSupportCard->friendship_level)->toBe(5);
});

it('unlocks rainbow training at 80% friendship', function () use (&$service, &$characterSupportCard) {
    // Set friendship to 75%
    $characterSupportCard->friendship_level = 75;
    $characterSupportCard->save();

    // Update to 80%
    $result = $service->updateFriendshipLevel($characterSupportCard->id, 5);

    expect($result['new_level'])->toBe(80)
        ->and($result['rainbow_training_available'])->toBeTrue()
        ->and($result['rainbow_unlocked'])->toBeTrue();
});

it('does not exceed maximum friendship level', function () use (&$service, &$characterSupportCard) {
    // Set friendship to 98%
    $characterSupportCard->friendship_level = 98;
    $characterSupportCard->save();

    // Try to add 5 points (would be 103)
    $result = $service->updateFriendshipLevel($characterSupportCard->id, 5);

    expect($result['new_level'])->toBe(100)
        ->and($result['points_gained'])->toBe(5);

    $characterSupportCard->refresh();
    expect($characterSupportCard->friendship_level)->toBe(100);
});

it('checks rainbow training availability correctly', function () use (&$service, &$characterSupportCard) {
    // Below threshold
    $characterSupportCard->friendship_level = 79;
    $characterSupportCard->save();
    expect($service->isRainbowTrainingAvailable($characterSupportCard->id))->toBeFalse();

    // At threshold
    $characterSupportCard->friendship_level = 80;
    $characterSupportCard->save();
    expect($service->isRainbowTrainingAvailable($characterSupportCard->id))->toBeTrue();

    // Above threshold
    $characterSupportCard->friendship_level = 100;
    $characterSupportCard->save();
    expect($service->isRainbowTrainingAvailable($characterSupportCard->id))->toBeTrue();
});

it('gets rainbow training cards for a character', function () use (&$service, &$character, &$supportCard) {
    // Create multiple cards with different friendship levels
    $card1 = CharacterSupportCard::create([
        'character_id' => $character->id,
        'support_card_id' => $supportCard->id,
        'position_slot' => 2,
        'friendship_level' => 85,
        'limit_break_level' => 0,
        'is_friend_card' => false,
    ]);

    $card2 = CharacterSupportCard::create([
        'character_id' => $character->id,
        'support_card_id' => $supportCard->id,
        'position_slot' => 3,
        'friendship_level' => 70,
        'limit_break_level' => 0,
        'is_friend_card' => false,
    ]);

    $rainbowCards = $service->getRainbowTrainingCards($character->id);

    expect($rainbowCards)->toHaveCount(1)
        ->and($rainbowCards[0]['friendship_level'])->toBe(85)
        ->and($rainbowCards[0]['position_slot'])->toBe(2);
});

it('calculates friendship bonus based on participant count', function () use (&$service) {
    expect($service->calculateFriendshipBonus(0))->toBe(0)
        ->and($service->calculateFriendshipBonus(1))->toBe(0)
        ->and($service->calculateFriendshipBonus(2))->toBe(2)
        ->and($service->calculateFriendshipBonus(3))->toBe(3)
        ->and($service->calculateFriendshipBonus(4))->toBe(4);
});

it('calculates training bonus with friendship multipliers', function () use (&$service, &$character, &$supportCard) {
    // Create cards with different friendship levels
    $card1 = CharacterSupportCard::create([
        'character_id' => $character->id,
        'support_card_id' => $supportCard->id,
        'position_slot' => 2,
        'friendship_level' => 85,
        'limit_break_level' => 0,
        'is_friend_card' => false,
    ]);

    $card2 = CharacterSupportCard::create([
        'character_id' => $character->id,
        'support_card_id' => $supportCard->id,
        'position_slot' => 3,
        'friendship_level' => 90,
        'limit_break_level' => 0,
        'is_friend_card' => false,
    ]);

    $card3 = CharacterSupportCard::create([
        'character_id' => $character->id,
        'support_card_id' => $supportCard->id,
        'position_slot' => 4,
        'friendship_level' => 70, // Below threshold
        'limit_break_level' => 0,
        'is_friend_card' => false,
    ]);

    $result = $service->calculateTrainingBonusWithFriendship(
        [$card1->id, $card2->id, $card3->id],
        10
    );

    expect($result['base_bonus'])->toBe(10)
        ->and($result['friendship_bonus'])->toBe(2) // 2 participants at 80%+
        ->and($result['total_bonus'])->toBe(12)
        ->and($result['rainbow_count'])->toBe(2)
        ->and($result['is_rainbow_training'])->toBeTrue()
        ->and($result['rainbow_participants'])->toHaveCount(2);
});

it('calculates skill hint provision rate based on bond level', function () use (&$service) {
    $baseRate = 0.10;

    // 0% friendship = 10% base rate
    expect($service->calculateSkillHintRate(0, $baseRate))->toBe(0.10);

    // 50% friendship = 10% + (5 * 2%) = 20%
    expect($service->calculateSkillHintRate(50, $baseRate))->toBe(0.20);

    // 100% friendship = 10% + (10 * 2%) = 30%
    $result = $service->calculateSkillHintRate(100, $baseRate);
    expect(abs($result - 0.30) < 0.001)->toBeTrue();
});

it('gets skill hint provision rate for a card', function () use (&$service, &$characterSupportCard) {
    $characterSupportCard->friendship_level = 50;
    $characterSupportCard->save();

    $result = $service->getSkillHintProvisionRate($characterSupportCard->id);

    expect($result['friendship_level'])->toBe(50)
        ->and($result['base_rate'])->toBe(0.10)
        ->and($result['current_rate'])->toBe(0.20)
        ->and($result['rate_increase'])->toBe(0.10)
        ->and($result['rate_percentage'])->toBe(20.0);
});

it('gets friendship progression for a card', function () use (&$service, &$characterSupportCard) {
    $characterSupportCard->friendship_level = 60;
    $characterSupportCard->save();

    $result = $service->getFriendshipProgression($characterSupportCard->id);

    expect($result['current_level'])->toBe(60)
        ->and($result['max_level'])->toBe(100)
        ->and($result['rainbow_threshold'])->toBe(80)
        ->and($result['is_rainbow_available'])->toBeFalse()
        ->and($result['is_max_level'])->toBeFalse()
        ->and($result['progress_to_rainbow'])->toBe(20)
        ->and($result['progress_to_max'])->toBe(40)
        ->and($result['percentage'])->toBe(60.0);
});

it('gets deck friendship overview', function () use (&$service, &$character, &$supportCard, &$characterSupportCard) {
    // Create multiple cards
    CharacterSupportCard::create([
        'character_id' => $character->id,
        'support_card_id' => $supportCard->id,
        'position_slot' => 2,
        'friendship_level' => 85,
        'limit_break_level' => 0,
        'is_friend_card' => false,
    ]);

    CharacterSupportCard::create([
        'character_id' => $character->id,
        'support_card_id' => $supportCard->id,
        'position_slot' => 3,
        'friendship_level' => 90,
        'limit_break_level' => 0,
        'is_friend_card' => false,
    ]);

    $characterSupportCard->friendship_level = 70;
    $characterSupportCard->save();

    $overview = $service->getDeckFriendshipOverview($character->id);

    expect($overview['character_id'])->toBe($character->id)
        ->and($overview['total_cards'])->toBe(3)
        ->and($overview['rainbow_available_count'])->toBe(2)
        ->and($overview['average_friendship'])->toBe(81.67)
        ->and($overview['cards'])->toHaveCount(3);
});

it('bulk updates friendship levels', function () use (&$service, &$character, &$supportCard, &$characterSupportCard) {
    $card2 = CharacterSupportCard::create([
        'character_id' => $character->id,
        'support_card_id' => $supportCard->id,
        'position_slot' => 2,
        'friendship_level' => 50,
        'limit_break_level' => 0,
        'is_friend_card' => false,
    ]);

    $updates = [
        $characterSupportCard->id => 10,
        $card2->id => 15,
    ];

    $result = $service->bulkUpdateFriendshipLevels($updates);

    expect($result['success'])->toBe(2)
        ->and($result['failed'])->toBe(0)
        ->and($result['updates'])->toHaveCount(2);

    $characterSupportCard->refresh();
    $card2->refresh();

    expect($characterSupportCard->friendship_level)->toBe(10)
        ->and($card2->friendship_level)->toBe(65);
});

it('resets friendship level', function () use (&$service, &$characterSupportCard) {
    $characterSupportCard->friendship_level = 75;
    $characterSupportCard->save();

    $result = $service->resetFriendshipLevel($characterSupportCard->id);

    expect($result)->toBeTrue();

    $characterSupportCard->refresh();
    expect($characterSupportCard->friendship_level)->toBe(0);
});

it('sets friendship level directly', function () use (&$service, &$characterSupportCard) {
    $result = $service->setFriendshipLevel($characterSupportCard->id, 85);

    expect($result)->toBeTrue();

    $characterSupportCard->refresh();
    expect($characterSupportCard->friendship_level)->toBe(85);
});

it('validates friendship level bounds when setting directly', function () use (&$service, &$characterSupportCard) {
    expect(fn () => $service->setFriendshipLevel($characterSupportCard->id, -10))
        ->toThrow(\InvalidArgumentException::class);

    expect(fn () => $service->setFriendshipLevel($characterSupportCard->id, 150))
        ->toThrow(\InvalidArgumentException::class);
});

it('handles non-existent card gracefully', function () use (&$service) {
    expect(fn () => $service->updateFriendshipLevel(99999, 5))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
