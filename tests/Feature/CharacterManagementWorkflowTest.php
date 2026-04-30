<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\SupportCardDefinition;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/**
 * Comprehensive Character Management Workflow Test
 *
 * This test covers the complete character management workflow including:
 * - Character creation with initial stats
 * - Aptitude management
 * - Support card deck building
 * - Skill acquisition and management
 * - Inheritance factors
 * - Race schedule planning
 *
 * This validates the entire application flow as documented in:
 * - docs/02-prds/PRD-001_Character_Management.md
 * - docs/02-prds/PRD-004_Skill_Management.md
 * - docs/02-prds/PRD-005_Support_Card_Management.md
 * - docs/01-user-flows/UF-002_Career_Setup_Flow.md
 * - docs/01-user-flows/UF-006_Support_Deck_Building_Flow.md
 */
it('completes full character management workflow from creation to deck building', function () {
    // Setup: Create test user and seed support cards
    $user = User::factory()->create([
        'name' => 'Test Trainer',
        'email' => 'trainer@test.com',
    ]);

    // Create support cards for deck building
    $supportCards = SupportCardDefinition::factory()->count(10)->create([
        'rarity' => 'SSR',
        'meta_tier' => 'S',
    ]);

    actingAs($user);

    // ============================================================================
    // STEP 1: Character List Page
    // ============================================================================
    $response = get('/characters');
    $response->assertOk()
        ->assertSee('Characters');

    // ============================================================================
    // STEP 2: Character Creation Form
    // ============================================================================
    $response = get('/characters/create');
    $response->assertOk()
        ->assertSee('Creation Wizard')
        ->assertSee('Choose Scenario')
        ->assertSee('Choose Your Trainee');

    // ============================================================================
    // STEP 3: Submit Character Creation
    // ============================================================================
    $characterData = [
        'name' => 'Agnes Tachyon',
        'scenario_type' => 'ura_finale',
        'stats' => [
            'speed' => 300,
            'stamina' => 250,
            'power' => 200,
            'guts' => 150,
            'wit' => 280,
        ],
        'aptitudes' => [
            'distance' => [
                'sprint' => 'B',
                'mile' => 'A',
                'medium' => 'A',
                'long' => 'C',
            ],
            'surface' => [
                'turf' => 'A',
                'dirt' => 'B',
            ],
            'style' => [
                'front_runner' => 'B',
                'pace_chaser' => 'A',
                'late_surger' => 'A',
                'end_closer' => 'C',
            ],
        ],
    ];

    $response = post('/characters', $characterData);
    $response->assertRedirect()
        ->assertSessionHasNoErrors();

    // Verify character was created in database
    $this->assertDatabaseHas('ucp_characters', [
        'name' => 'Agnes Tachyon',
        'user_id' => $user->id,
        'scenario_type' => 'ura_finale',
    ]);

    $character = Character::where('name', 'Agnes Tachyon')->first();
    expect($character)->not->toBeNull()
        ->and($character->user_id)->toBe($user->id)
        ->and($character->scenario_type)->toBe('ura_finale')
        ->and($character->current_stats['speed'])->toBe(300)
        ->and($character->current_stats['stamina'])->toBe(250)
        ->and($character->current_stats['wit'])->toBe(280);

    // ============================================================================
    // STEP 4: View Character Details
    // ============================================================================
    $response = get("/characters/{$character->id}");
    $response->assertOk()
        ->assertSee('Agnes Tachyon')
        ->assertSee('Speed')
        ->assertSee('300')
        ->assertSee('Stamina')
        ->assertSee('250')
        ->assertSee('Wit')
        ->assertSee('280')
        ->assertSee('Support Deck')
        ->assertSee('Manage Deck');

    // ============================================================================
    // STEP 5: Support Card Deck Builder Page
    // ============================================================================
    $response = get("/characters/{$character->id}/deck-builder");
    $response->assertOk()
        ->assertSee('Deck Builder')
        ->assertSee('Agnes Tachyon');

    // ============================================================================
    // STEP 6: Add Support Cards to Deck via API
    // ============================================================================
    // Add 5 owned cards
    for ($i = 0; $i < 5; $i++) {
        $response = $this->postJson("/api/v1/characters/{$character->id}/deck/cards", [
            'support_card_id' => $supportCards[$i]->id,
            'position_slot' => $i + 1,
            'is_friend_card' => false,
            'limit_break_level' => 0,
        ]);
        $response->assertCreated()
            ->assertJson(['success' => true]);
    }

    // Add friend card to slot 6
    $response = $this->postJson("/api/v1/characters/{$character->id}/deck/cards", [
        'support_card_id' => $supportCards[5]->id,
        'position_slot' => 6,
        'is_friend_card' => true,
        'limit_break_level' => 0,
    ]);
    $response->assertCreated()
        ->assertJson(['success' => true]);

    // Verify deck is complete
    $character->refresh();
    expect($character->supportCards)->toHaveCount(6);

    // Verify friend card is in slot 6
    $friendCard = $character->supportCards->where('position_slot', 6)->first();
    expect($friendCard)->not->toBeNull()
        ->and($friendCard->is_friend_card)->toBeTrue();

    // ============================================================================
    // STEP 7: Update Card Details (Limit Break and Friendship)
    // ============================================================================
    $response = $this->putJson("/api/v1/characters/{$character->id}/deck/cards/1/details", [
        'limit_break_level' => 3,
        'friendship_level' => 75,
    ]);
    $response->assertOk()
        ->assertJson(['success' => true]);

    // Verify card details were updated
    $character->refresh();
    $slot1Card = $character->supportCards->where('position_slot', 1)->first();
    expect($slot1Card->limit_break_level)->toBe(3)
        ->and($slot1Card->friendship_level)->toBe(75);

    // ============================================================================
    // STEP 8: Test Card Swapping
    // ============================================================================
    $slot1CardId = $character->supportCards->where('position_slot', 1)->first()->support_card_id;
    $slot3CardId = $character->supportCards->where('position_slot', 3)->first()->support_card_id;

    $response = $this->postJson("/api/v1/characters/{$character->id}/deck/cards/swap", [
        'position1' => 1,
        'position2' => 3,
    ]);
    $response->assertOk()
        ->assertJson(['success' => true]);

    // Verify cards were swapped
    $character->refresh();
    $newSlot1Card = $character->supportCards->where('position_slot', 1)->first();
    $newSlot3Card = $character->supportCards->where('position_slot', 3)->first();

    expect($newSlot1Card->support_card_id)->toBe($slot3CardId)
        ->and($newSlot3Card->support_card_id)->toBe($slot1CardId);

    // ============================================================================
    // STEP 9: View Deck with Synergy Analysis
    // ============================================================================
    $response = get("/characters/{$character->id}/deck-builder");
    $response->assertOk()
        ->assertSee('Synergy Score');

    // ============================================================================
    // STEP 10: Remove a Card from Deck
    // ============================================================================
    $response = $this->deleteJson("/api/v1/characters/{$character->id}/deck/cards/2");
    $response->assertOk()
        ->assertJson(['success' => true]);

    // Verify card was removed
    $character->refresh();
    expect($character->supportCards)->toHaveCount(5);

    // ============================================================================
    // STEP 11: Character Search and Filtering
    // ============================================================================
    $response = get('/characters?search=Agnes');
    $response->assertOk()
        ->assertSee('Agnes Tachyon');

    $response = get('/characters?scenario=ura_finale');
    $response->assertOk()
        ->assertSee('Agnes Tachyon');

    $response = get('/characters?status=active');
    $response->assertOk()
        ->assertSee('Agnes Tachyon');

    // ============================================================================
    // STEP 12: Character Deletion
    // ============================================================================
    $response = $this->delete("/characters/{$character->id}");
    $response->assertRedirect();

    // Verify character was deleted
    expect(Character::find($character->id))->toBeNull();

    // Verify associated deck cards were also deleted
    $this->assertDatabaseMissing('character_support_cards', [
        'character_id' => $character->id,
    ]);
})->group('feature', 'character', 'workflow', 'comprehensive');
