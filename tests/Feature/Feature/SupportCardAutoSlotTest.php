<?php

use App\Models\Character;
use App\Models\SupportCard;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['email' => 'admin@umamusume.local']);
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
    $this->supportCards = SupportCard::factory()->count(10)->create();
});

test('deck builder page loads successfully', function () {
    $response = $this->actingAs($this->user)
        ->get(route('characters.deck-builder', $this->character));

    $response->assertStatus(200);
    $response->assertSee('Deck Builder');
    $response->assertSee($this->character->name);
});

test('deck builder shows available cards', function () {
    $response = $this->actingAs($this->user)
        ->get(route('characters.deck-builder', $this->character));

    $response->assertStatus(200);
    $response->assertSee('Available Cards');

    // Should see at least some of the support cards
    foreach ($this->supportCards->take(3) as $card) {
        $response->assertSee($card->name);
    }
});

test('deck builder shows drag and drop instructions', function () {
    $response = $this->actingAs($this->user)
        ->get(route('characters.deck-builder', $this->character));

    $response->assertStatus(200);
    $response->assertSee('Click any card to auto-add');
    $response->assertSee('Drag to reorder');
    $response->assertSee('Arrow keys to move');
});

test('deck builder shows all 6 slots', function () {
    $response = $this->actingAs($this->user)
        ->get(route('characters.deck-builder', $this->character));

    $response->assertStatus(200);
    // The deck count is dynamic via Alpine.js, so we check for the structure
    $response->assertSee('Deck Slots (');
    $response->assertSee('/6)');
    $response->assertSee('Empty Slot');
    // Friend card slot is marked as Required in the actual template
    $response->assertSee('Friend Card Slot (Required)');
});

test('deck builder shows updated empty slot message', function () {
    $response = $this->actingAs($this->user)
        ->get(route('characters.deck-builder', $this->character));

    $response->assertStatus(200);
    // The actual message in the template is "Click a card from library to add"
    $response->assertSee('Click a card from library to add');
    $response->assertDontSee('Click "Add Card" to fill this slot');
});
