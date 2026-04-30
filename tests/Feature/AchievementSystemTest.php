<?php

declare(strict_types=1);

use App\Models\Achievement;
use App\Models\User;
use App\Services\AchievementService;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('Achievement Page', function () {
    it('renders the achievements page for authenticated users', function () {
        $response = $this->actingAs($this->user)
            ->get(route('achievements.index'));

        $response->assertSuccessful();
        $response->assertSee('Achievements');
    });

    it('redirects unauthenticated users from achievements page', function () {
        $response = $this->get(route('achievements.index'));

        $response->assertRedirect();
    });
});

describe('AchievementService', function () {
    it('seeds achievements for a new user', function () {
        $service = app(AchievementService::class);
        $service->seedForUser($this->user);

        $count = Achievement::where('user_id', $this->user->id)->count();
        expect($count)->toBeGreaterThan(0);
    });

    it('seeds idempotently — no duplicates on second call', function () {
        $service = app(AchievementService::class);
        $service->seedForUser($this->user);
        $firstCount = Achievement::where('user_id', $this->user->id)->count();

        $service->seedForUser($this->user);
        $secondCount = Achievement::where('user_id', $this->user->id)->count();

        expect($secondCount)->toBe($firstCount);
    });

    it('returns all achievements for a user', function () {
        $service = app(AchievementService::class);
        $achievements = $service->getForUser($this->user);

        expect($achievements)->not->toBeEmpty();
        expect($achievements->first())->toBeInstanceOf(Achievement::class);
    });

    it('returns achievements grouped by category', function () {
        $service = app(AchievementService::class);
        $grouped = $service->getGroupedForUser($this->user);

        expect($grouped)->toHaveKey('racing');
        expect($grouped)->toHaveKey('training');
        expect($grouped)->toHaveKey('skills');
        expect($grouped)->toHaveKey('career');
        expect($grouped)->toHaveKey('collection');
    });

    it('updates progress for an achievement', function () {
        $service = app(AchievementService::class);
        $service->seedForUser($this->user);

        $result = $service->updateProgress($this->user, 'race_wins_10', 5);

        $achievement = Achievement::where('user_id', $this->user->id)
            ->where('key', 'race_wins_10')
            ->first();

        expect($achievement)->not->toBeNull();
        expect($achievement->progress)->toBe(5);
        expect($achievement->is_unlocked)->toBeFalse();
        expect($result)->toBeNull(); // Not yet unlocked
    });

    it('unlocks an achievement when progress reaches target', function () {
        $service = app(AchievementService::class);
        $service->seedForUser($this->user);

        $unlocked = $service->updateProgress($this->user, 'first_race_win', 1);

        expect($unlocked)->not->toBeNull();
        expect($unlocked->key)->toBe('first_race_win');
        expect($unlocked->is_unlocked)->toBeTrue();
        expect($unlocked->unlocked_at)->not->toBeNull();
    });

    it('does not re-unlock an already unlocked achievement', function () {
        $service = app(AchievementService::class);
        $service->seedForUser($this->user);

        // Unlock it
        $service->updateProgress($this->user, 'first_race_win', 1);

        // Try to update again
        $result = $service->updateProgress($this->user, 'first_race_win', 1);

        expect($result)->toBeNull();
    });

    it('increments progress correctly', function () {
        $service = app(AchievementService::class);
        $service->seedForUser($this->user);

        $service->incrementProgress($this->user, 'race_wins_10', 3);
        $service->incrementProgress($this->user, 'race_wins_10', 4);

        $achievement = Achievement::where('user_id', $this->user->id)
            ->where('key', 'race_wins_10')
            ->first();

        expect($achievement->progress)->toBe(7);
    });

    it('returns summary stats', function () {
        $service = app(AchievementService::class);
        $summary = $service->getSummary($this->user);

        expect($summary)->toHaveKey('total');
        expect($summary)->toHaveKey('unlocked');
        expect($summary)->toHaveKey('locked');
        expect($summary)->toHaveKey('percent');
        expect($summary)->toHaveKey('by_rarity');
        expect($summary['total'])->toBeGreaterThan(0);
        expect($summary['unlocked'])->toBe(0);
        expect($summary['percent'])->toBe(0);
    });

    it('returns recently unlocked achievements', function () {
        $service = app(AchievementService::class);
        $service->seedForUser($this->user);
        $service->updateProgress($this->user, 'first_race_win', 1);
        $service->updateProgress($this->user, 'first_training', 1);

        $recent = $service->getRecentlyUnlocked($this->user, 5);

        expect($recent->count())->toBe(2);
        expect($recent->first()->is_unlocked)->toBeTrue();
    });
});

describe('Achievement Model', function () {
    it('calculates progress percent correctly', function () {
        $achievement = Achievement::factory()->create([
            'user_id' => $this->user->id,
            'key' => 'test_progress',
            'category' => 'racing',
            'title' => 'Test',
            'description' => 'Test achievement',
            'icon' => '🏆',
            'rarity' => 'common',
            'progress' => 5,
            'target' => 10,
            'is_unlocked' => false,
        ]);

        expect($achievement->progress_percent)->toBe(50);
    });

    it('caps progress percent at 100', function () {
        $achievement = Achievement::factory()->create([
            'user_id' => $this->user->id,
            'key' => 'test_cap',
            'category' => 'racing',
            'title' => 'Test Cap',
            'description' => 'Test',
            'icon' => '🏆',
            'rarity' => 'common',
            'progress' => 15,
            'target' => 10,
            'is_unlocked' => true,
        ]);

        expect($achievement->progress_percent)->toBe(100);
    });

    it('returns correct rarity styles', function () {
        $legendary = Achievement::factory()->create([
            'user_id' => $this->user->id,
            'key' => 'test_legendary',
            'category' => 'racing',
            'title' => 'Legendary',
            'description' => 'Test',
            'icon' => '⭐',
            'rarity' => 'legendary',
            'progress' => 0,
            'target' => 1,
        ]);

        $style = $legendary->rarity_style;
        expect($style)->toHaveKey('bg');
        expect($style)->toHaveKey('color');
        expect($style)->toHaveKey('border');
        expect($style['color'])->toBe('#fff');
    });
});

describe('AchievementBrowser Livewire Component', function () {
    it('renders the achievement browser component', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Achievements\AchievementBrowser::class)
            ->assertSuccessful();
    });

    it('shows all achievements by default', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Achievements\AchievementBrowser::class)
            ->assertSet('activeCategory', 'all');
    });

    it('filters by category', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Achievements\AchievementBrowser::class)
            ->call('setCategory', 'racing')
            ->assertSet('activeCategory', 'racing');
    });

    it('filters by search query', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Achievements\AchievementBrowser::class)
            ->set('searchQuery', 'Victory')
            ->assertSee('First Victory');
    });

    it('shows summary stats', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Achievements\AchievementBrowser::class)
            ->assertSee('Unlocked')
            ->assertSee('Total')
            ->assertSee('Complete');
    });

    it('shows locked state for unearned achievements', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Achievements\AchievementBrowser::class)
            ->assertSee('Locked');
    });

    it('shows unlocked state after earning an achievement', function () {
        $service = app(AchievementService::class);
        $service->seedForUser($this->user);
        $service->updateProgress($this->user, 'first_race_win', 1);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Achievements\AchievementBrowser::class)
            ->assertSee('Unlocked');
    });
});
