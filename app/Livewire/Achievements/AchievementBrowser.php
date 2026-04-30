<?php

declare(strict_types=1);

namespace App\Livewire\Achievements;

use App\Services\AchievementService;
use Livewire\Component;

/**
 * AchievementBrowser Livewire Component
 *
 * Displays all achievements for the authenticated user with filtering by category.
 */
class AchievementBrowser extends Component
{
    public string $activeCategory = 'all';

    public string $searchQuery = '';

    /** @var array<string, string> */
    public array $categoryLabels = [
        'all' => '🏅 All',
        'racing' => '🏆 Racing',
        'training' => '⚡ Training',
        'skills' => '✨ Skills',
        'career' => '🏇 Career',
        'collection' => '🎴 Collection',
    ];

    public function setCategory(string $category): void
    {
        $this->activeCategory = $category;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Achievement>
     */
    private function buildFilteredAchievements(): \Illuminate\Support\Collection
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $achievements = app(AchievementService::class)->getForUser($user);

        if ($this->activeCategory !== 'all') {
            $achievements = $achievements->where('category', $this->activeCategory)->values();
        }

        if ($this->searchQuery !== '') {
            $q = strtolower($this->searchQuery);
            $achievements = $achievements->filter(
                fn ($a) => str_contains(strtolower((string) $a->title), $q) || str_contains(strtolower((string) $a->description), $q)
            )->values();
        }

        return $achievements;
    }

    /**
     * @return array{total: int, unlocked: int, locked: int, percent: int, by_rarity: array<string, int>}
     */
    private function buildSummary(): array
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return app(AchievementService::class)->getSummary($user);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.achievements.achievement-browser', [
            'achievements' => $this->buildFilteredAchievements(),
            'summary' => $this->buildSummary(),
        ]);
    }
}
