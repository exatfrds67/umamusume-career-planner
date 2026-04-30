<?php

declare(strict_types=1);

use App\Livewire\Races\RaceEntryModal;
use App\Livewire\Races\RaceIndex;
use App\Models\Character;
use App\Models\GameRace;
use App\Models\Race;
use App\Models\User;
use Livewire\Livewire;

// ─────────────────────────────────────────────────────────────────────────────
// Race Strategy Page — Upcoming Races List
// ─────────────────────────────────────────────────────────────────────────────

describe('Race Strategy — Upcoming Races', function () {
    it('renders the race strategy page for authenticated users', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('races.index'))
            ->assertOk()
            ->assertSee('Race Calendar');
    });

    it('redirects guests to the welcome page', function () {
        $this->get(route('races.index'))
            ->assertRedirect(route('welcome'));
    });

    it('renders the RaceIndex Livewire component on the upcoming-races view', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->assertStatus(200);
    });

    it('shows Race Strategy heading in the Livewire component', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->assertSee('Race Strategy');
    });

    it('defaults to the upcoming tab', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->assertSet('activeTab', 'upcoming');
    });

    it('switches to the history tab', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->call('switchTab', 'history')
            ->assertSet('activeTab', 'history');
    });

    it('resets selected race when switching tabs', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->set('selectedRaceId', 1)
            ->call('switchTab', 'history')
            ->assertSet('selectedRaceId', null);
    });

    it('ignores invalid tab names', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->call('switchTab', 'invalid')
            ->assertSet('activeTab', 'upcoming');
    });

    it('shows character name in the header when character exists', function () {
        $user = User::factory()->create();
        Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'Silence Suzuka',
        ]);
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->assertSee('Silence Suzuka');
    });

    it('shows upcoming races from the game race catalog', function () {
        $user = User::factory()->create();
        Character::factory()->create([
            'user_id' => $user->id,
            'career_stage' => 'senior',
        ]);
        GameRace::factory()->create([
            'name_en' => 'Hopeful Stakes',
            'grade' => 'G1',
            'phase' => 'senior',
            'is_ura_finale' => false,
        ]);
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->assertSee('Hopeful Stakes');
    });

    it('selects a race and updates selectedRaceId', function () {
        $user = User::factory()->create();
        Character::factory()->create([
            'user_id' => $user->id,
            'career_stage' => 'senior',
        ]);
        $race = GameRace::factory()->create([
            'name_en' => 'Satsuki Sho',
            'grade' => 'G1',
            'phase' => 'senior',
            'is_ura_finale' => false,
        ]);
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->call('selectRace', $race->id)
            ->assertSet('selectedRaceId', $race->id);
    });

    it('shows race detail panel when a race is selected', function () {
        $user = User::factory()->create();
        Character::factory()->create([
            'user_id' => $user->id,
            'career_stage' => 'senior',
        ]);
        $race = GameRace::factory()->create([
            'name_en' => 'Japan Cup',
            'grade' => 'G1',
            'phase' => 'senior',
            'is_ura_finale' => false,
        ]);
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->call('selectRace', $race->id)
            ->assertSee('Japan Cup')
            ->assertSee('Readiness');
    });

    it('shows empty state when no character exists', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->assertSee('No upcoming races available');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Race History Tab
// ─────────────────────────────────────────────────────────────────────────────

describe('Race Strategy — History Tab', function () {
    it('shows race history for the character', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = \App\Models\Career::factory()->create([
            'character_id' => $character->id,
            'user_id' => $user->id,
        ]);
        Race::factory()->create([
            'character_id' => $character->id,
            'career_id' => $career->id,
            'race_name' => 'Arima Kinen',
            'finish_position' => 1,
            'race_grade' => 'G1',
            'turn_number' => 15,
        ]);
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->call('switchTab', 'history')
            ->assertSee('Arima Kinen');
    });

    it('shows finish position in history', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = \App\Models\Career::factory()->create([
            'character_id' => $character->id,
            'user_id' => $user->id,
        ]);
        Race::factory()->create([
            'character_id' => $character->id,
            'career_id' => $career->id,
            'race_name' => 'Takarazuka Kinen',
            'finish_position' => 2,
            'race_grade' => 'G1',
            'turn_number' => 20,
        ]);
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->call('switchTab', 'history')
            ->assertSee('Takarazuka Kinen');
    });

    it('shows empty history state when no races recorded', function () {
        $user = User::factory()->create();
        Character::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->call('switchTab', 'history')
            ->assertSee('No race history recorded');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Race Entry Modal
// ─────────────────────────────────────────────────────────────────────────────

describe('Race Entry Modal', function () {
    it('renders the modal component', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RaceEntryModal::class)
            ->assertStatus(200);
    });

    it('starts with modal hidden', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RaceEntryModal::class)
            ->assertSet('showModal', false);
    });

    it('opens modal when open-race-modal event is dispatched', function () {
        $user = User::factory()->create();
        $race = GameRace::factory()->create(['grade' => 'G1']);
        $this->actingAs($user);

        Livewire::test(RaceEntryModal::class)
            ->dispatch('open-race-modal', raceId: $race->id)
            ->assertSet('showModal', true)
            ->assertSet('raceId', $race->id)
            ->assertSet('step', 'enter');
    });

    it('selects a placement', function () {
        $user = User::factory()->create();
        $race = GameRace::factory()->create(['grade' => 'G2']);
        $this->actingAs($user);

        Livewire::test(RaceEntryModal::class)
            ->dispatch('open-race-modal', raceId: $race->id)
            ->call('selectPlacement', 3)
            ->assertSet('placement', 3);
    });

    it('closes modal and resets state', function () {
        $user = User::factory()->create();
        $race = GameRace::factory()->create(['grade' => 'G3']);
        $this->actingAs($user);

        Livewire::test(RaceEntryModal::class)
            ->dispatch('open-race-modal', raceId: $race->id)
            ->call('selectPlacement', 2)
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertSet('placement', 0);
    });

    it('shows reward preview when placement is selected', function () {
        $user = User::factory()->create();
        $race = GameRace::factory()->create(['grade' => 'G1']);
        $this->actingAs($user);

        Livewire::test(RaceEntryModal::class)
            ->dispatch('open-race-modal', raceId: $race->id)
            ->call('selectPlacement', 1)
            ->assertSee('Reward Preview');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Reward Calculation
// ─────────────────────────────────────────────────────────────────────────────

describe('Race Reward Calculation', function () {
    it('calculates G1 1st place rewards correctly', function () {
        $user = User::factory()->create();
        $race = GameRace::factory()->create(['grade' => 'G1']);
        $this->actingAs($user);

        $component = Livewire::test(RaceEntryModal::class)
            ->dispatch('open-race-modal', raceId: $race->id)
            ->call('selectPlacement', 1);

        // G1 1st: fans=3200*1.0=3200, sp=180*1.0=180, statBonus=8
        $component->assertSee('3,200')
            ->assertSee('180')
            ->assertSee('+8');
    });

    it('calculates G2 2nd place rewards correctly', function () {
        $user = User::factory()->create();
        $race = GameRace::factory()->create(['grade' => 'G2']);
        $this->actingAs($user);

        $component = Livewire::test(RaceEntryModal::class)
            ->dispatch('open-race-modal', raceId: $race->id)
            ->call('selectPlacement', 2);

        // G2 2nd: fans=1800*0.65=1170, sp=120*0.65=78, statBonus=4
        $component->assertSee('1,170')
            ->assertSee('78')
            ->assertSee('+4');
    });

    it('awards no SP for placements outside top 3', function () {
        $user = User::factory()->create();
        $race = GameRace::factory()->create(['grade' => 'G1']);
        $this->actingAs($user);

        $component = Livewire::test(RaceEntryModal::class)
            ->dispatch('open-race-modal', raceId: $race->id)
            ->call('selectPlacement', 4);

        // 4th place: no SP
        $component->assertSee('+0');
    });

    it('awards no stat bonus for placements outside top 3', function () {
        $user = User::factory()->create();
        $race = GameRace::factory()->create(['grade' => 'G3']);
        $this->actingAs($user);

        $component = Livewire::test(RaceEntryModal::class)
            ->dispatch('open-race-modal', raceId: $race->id)
            ->call('selectPlacement', 5);

        // 5th place: statBonus=0
        $component->assertSee('+0');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Storage Mode Awareness
// ─────────────────────────────────────────────────────────────────────────────

describe('Race Strategy — Storage Mode', function () {
    it('shows race list for authenticated account-mode users', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RaceIndex::class)
            ->assertStatus(200);
    });

    it('shows empty state for guest users (no character context)', function () {
        // Guest users have no character, so upcoming races should be empty
        Livewire::test(RaceIndex::class)
            ->assertSee('No upcoming races available');
    });
});
