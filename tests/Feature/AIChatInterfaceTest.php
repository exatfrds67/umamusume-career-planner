<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
});

it('renders the AI advisor chat page for authenticated users', function () {
    actingAs($this->user)
        ->get(route('ai.chat'))
        ->assertOk()
        ->assertSee('AI Advisor');
});

it('renders the AI chat page with character context', function () {
    actingAs($this->user)
        ->get(route('ai.chat', ['character_id' => $this->character->id]))
        ->assertOk()
        ->assertSee('AI Advisor');
});

it('shows the active context sidebar label', function () {
    actingAs($this->user)
        ->get(route('ai.chat'))
        ->assertOk()
        ->assertSee('ACTIVE CONTEXT');
});

it('shows the model selector', function () {
    actingAs($this->user)
        ->get(route('ai.chat'))
        ->assertOk()
        ->assertSee('AI MODEL');
});

it('shows suggested prompts section', function () {
    actingAs($this->user)
        ->get(route('ai.chat'))
        ->assertOk()
        ->assertSee('SUGGESTED')
        ->assertSee('What should I train next turn?');
});

it('shows chat and history tabs', function () {
    actingAs($this->user)
        ->get(route('ai.chat'))
        ->assertOk()
        ->assertSee('Chat')
        ->assertSee('History');
});

it('shows character name in context when character provided', function () {
    actingAs($this->user)
        ->get(route('ai.chat', ['character_id' => $this->character->id]))
        ->assertOk()
        ->assertSee($this->character->name);
});

it('shows storage mode badge', function () {
    actingAs($this->user)
        ->get(route('ai.chat'))
        ->assertOk()
        ->assertSee('Account');
});

it('shows model options in model selector', function () {
    actingAs($this->user)
        ->get(route('ai.chat'))
        ->assertOk()
        ->assertSee('AWS Bedrock Claude')
        ->assertSee('Local Ollama')
        ->assertSee('Auto Route');
});

it('shows empty character state when no character active', function () {
    $userWithNoCharacters = User::factory()->create();

    actingAs($userWithNoCharacters)
        ->get(route('ai.chat'))
        ->assertOk()
        ->assertSee('No active character');
});

it('requires authentication to access chat interface', function () {
    get(route('ai.chat'))
        ->assertRedirect();
});

it('rejects character belonging to another user', function () {
    $otherUser = User::factory()->create();
    $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);

    actingAs($this->user)
        ->get(route('ai.chat', ['character_id' => $otherCharacter->id]))
        ->assertNotFound();
});

it('shows the history tab with empty state when no conversations', function () {
    actingAs($this->user)
        ->get(route('ai.chat'))
        ->assertOk()
        ->assertSee('History');
});

it('shows the powered by hybrid AI subtitle', function () {
    actingAs($this->user)
        ->get(route('ai.chat'))
        ->assertOk()
        ->assertSee('Powered by Hybrid AI');
});
