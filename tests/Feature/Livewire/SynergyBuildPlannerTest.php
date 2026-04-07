<?php

declare(strict_types=1);

use App\Livewire\SynergyBuildPlanner;
use App\Models\Character;
use App\Models\User;
use Livewire\Livewire;

describe('SynergyBuildPlanner Livewire Component', function () {
    it('renders in full mode with report data', function () {
        $character = Character::factory()->uraFinale()->create();

        Livewire::test(SynergyBuildPlanner::class, ['characterId' => $character->id])
            ->assertStatus(200)
            ->assertSet('isCompact', false)
            ->assertSet('characterId', $character->id)
            ->assertNotSet('reportData', null)
            ->assertSee('Skill Strategy & Economy');
    });

    it('renders in compact mode', function () {
        $character = Character::factory()->uraFinale()->create();

        Livewire::test(SynergyBuildPlanner::class, [
            'characterId' => $character->id,
            'isCompact' => true,
        ])
            ->assertStatus(200)
            ->assertSet('isCompact', true);
    });

    it('populates report data on mount', function () {
        $character = Character::factory()->uraFinale()->create();

        $component = Livewire::test(SynergyBuildPlanner::class, ['characterId' => $character->id]);

        $reportData = $component->get('reportData');
        expect($reportData)->not->toBeNull();
        expect($reportData)->toHaveKeys(['overall_score', 'tier', 'layers']);
    });

    it('refreshes and recomputes synergy on refresh action', function () {
        $character = Character::factory()->uraFinale()->create();

        Livewire::test(SynergyBuildPlanner::class, ['characterId' => $character->id])
            ->call('refresh')
            ->assertNotSet('reportData', null);

        $character->refresh();
        expect($character->synergy_snapshot)->not->toBeNull();
    });

    it('handles non-existent character gracefully', function () {
        Livewire::test(SynergyBuildPlanner::class, ['characterId' => 99999])
            ->assertStatus(200)
            ->assertSet('reportData', null);
    });
});

describe('Synergy Page Access', function () {
    it('loads synergy page for authenticated character owner', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('characters.synergy', $character))
            ->assertOk();
    });

    it('redirects guests to login', function () {
        $character = Character::factory()->create();

        $this->get(route('characters.synergy', $character))
            ->assertRedirect();
    });

    it('forbids access to other users characters', function () {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($stranger)
            ->get(route('characters.synergy', $character))
            ->assertForbidden();
    });

    it('allows viewing seeded characters synergy', function () {
        $user = User::factory()->create();
        $character = Character::factory()->seeded()->create();

        $this->actingAs($user)
            ->get(route('characters.synergy', $character))
            ->assertOk();
    });
});
