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
    $response->assertSee('Deck Slots (0/6)');
    $response->assertSee('Empty Slot');
    $response->assertSee('Friend Card Slot (Optional)');
});

test('deck builder shows updated empty slot message', function () {
    $response = $this->actingAs($this->user)
        ->get(route('characters.deck-builder', $this->character));

    $response->assertStatus(200);
    $response->assertSee('Click any card from the library to add here');
    $response->assertDontSee('Click "Add Card" to fill this slot');
});
