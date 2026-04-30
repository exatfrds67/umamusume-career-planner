<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * AchievementService
 *
 * Manages achievement definitions, progress tracking, and unlock logic.
 */
class AchievementService
{
    /**
     * All achievement definitions.
     * Each entry: key, category, title, description, icon, rarity, target.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            // ── Racing ──────────────────────────────────────────────────────
            ['key' => 'first_race_win',       'category' => 'racing',    'title' => 'First Victory',          'description' => 'Win your first race.',                          'icon' => '🏆', 'rarity' => 'common',    'target' => 1],
            ['key' => 'race_wins_10',          'category' => 'racing',    'title' => 'On a Roll',              'description' => 'Win 10 races.',                                 'icon' => '🏆', 'rarity' => 'rare',      'target' => 10],
            ['key' => 'race_wins_50',          'category' => 'racing',    'title' => 'Champion',               'description' => 'Win 50 races.',                                 'icon' => '🥇', 'rarity' => 'epic',      'target' => 50],
            ['key' => 'g1_win',                'category' => 'racing',    'title' => 'G1 Glory',               'description' => 'Win a G1 race.',                                'icon' => '⭐', 'rarity' => 'epic',      'target' => 1],
            ['key' => 'g1_wins_5',             'category' => 'racing',    'title' => 'G1 Dominator',           'description' => 'Win 5 G1 races.',                               'icon' => '⭐', 'rarity' => 'legendary', 'target' => 5],
            ['key' => 'perfect_race',          'category' => 'racing',    'title' => 'Perfect Run',            'description' => 'Win a race with 100% readiness.',               'icon' => '💯', 'rarity' => 'rare',      'target' => 1],
            ['key' => 'fans_1000',             'category' => 'racing',    'title' => 'Rising Star',            'description' => 'Earn 1,000 fans.',                              'icon' => '👥', 'rarity' => 'common',    'target' => 1000],
            ['key' => 'fans_10000',            'category' => 'racing',    'title' => 'Fan Favorite',           'description' => 'Earn 10,000 fans.',                             'icon' => '👥', 'rarity' => 'rare',      'target' => 10000],
            ['key' => 'fans_100000',           'category' => 'racing',    'title' => 'Superstar',              'description' => 'Earn 100,000 fans.',                            'icon' => '🌟', 'rarity' => 'legendary', 'target' => 100000],

            // ── Training ────────────────────────────────────────────────────
            ['key' => 'first_training',        'category' => 'training',  'title' => 'First Steps',            'description' => 'Complete your first training session.',         'icon' => '⚡', 'rarity' => 'common',    'target' => 1],
            ['key' => 'training_sessions_50',  'category' => 'training',  'title' => 'Dedicated Trainer',      'description' => 'Complete 50 training sessions.',                'icon' => '⚡', 'rarity' => 'common',    'target' => 50],
            ['key' => 'training_sessions_200', 'category' => 'training',  'title' => 'Training Veteran',       'description' => 'Complete 200 training sessions.',               'icon' => '💪', 'rarity' => 'rare',      'target' => 200],
            ['key' => 'stat_1000',             'category' => 'training',  'title' => 'Stat Master',            'description' => 'Reach 1,000 in any single stat.',               'icon' => '📊', 'rarity' => 'epic',      'target' => 1000],
            ['key' => 'stat_1200',             'category' => 'training',  'title' => 'Stat Legend',            'description' => 'Reach 1,200 in any single stat.',               'icon' => '📊', 'rarity' => 'legendary', 'target' => 1200],
            ['key' => 'total_stats_3000',      'category' => 'training',  'title' => 'Well-Rounded',           'description' => 'Reach 3,000 total stats across all 5 stats.',   'icon' => '🎯', 'rarity' => 'rare',      'target' => 3000],
            ['key' => 'total_stats_5000',      'category' => 'training',  'title' => 'Elite Athlete',          'description' => 'Reach 5,000 total stats across all 5 stats.',   'icon' => '🎯', 'rarity' => 'legendary', 'target' => 5000],

            // ── Skills ──────────────────────────────────────────────────────
            ['key' => 'first_skill',           'category' => 'skills',    'title' => 'Skill Learner',          'description' => 'Acquire your first skill.',                     'icon' => '✨', 'rarity' => 'common',    'target' => 1],
            ['key' => 'skills_10',             'category' => 'skills',    'title' => 'Skill Collector',        'description' => 'Acquire 10 skills.',                            'icon' => '✨', 'rarity' => 'common',    'target' => 10],
            ['key' => 'skills_30',             'category' => 'skills',    'title' => 'Skill Hoarder',          'description' => 'Acquire 30 skills.',                            'icon' => '💎', 'rarity' => 'rare',      'target' => 30],
            ['key' => 'first_evolution',       'category' => 'skills',    'title' => 'Evolution!',             'description' => 'Evolve a skill for the first time.',            'icon' => '⬆️', 'rarity' => 'rare',      'target' => 1],
            ['key' => 'evolutions_5',          'category' => 'skills',    'title' => 'Evolution Master',       'description' => 'Evolve 5 skills.',                              'icon' => '⬆️', 'rarity' => 'epic',      'target' => 5],
            ['key' => 'sp_spent_1000',         'category' => 'skills',    'title' => 'SP Spender',             'description' => 'Spend 1,000 SP on skills.',                     'icon' => '💰', 'rarity' => 'common',    'target' => 1000],
            ['key' => 'sp_spent_5000',         'category' => 'skills',    'title' => 'SP Whale',               'description' => 'Spend 5,000 SP on skills.',                     'icon' => '💰', 'rarity' => 'epic',      'target' => 5000],
            ['key' => 'hint_level_5',          'category' => 'skills',    'title' => 'Hint Master',            'description' => 'Reach hint level 5 on any skill.',              'icon' => '💡', 'rarity' => 'rare',      'target' => 1],

            // ── Career ──────────────────────────────────────────────────────
            ['key' => 'first_career',          'category' => 'career',    'title' => 'Career Starter',         'description' => 'Complete your first career run.',               'icon' => '🏇', 'rarity' => 'common',    'target' => 1],
            ['key' => 'careers_5',             'category' => 'career',    'title' => 'Experienced Trainer',    'description' => 'Complete 5 career runs.',                       'icon' => '🏇', 'rarity' => 'rare',      'target' => 5],
            ['key' => 'careers_20',            'category' => 'career',    'title' => 'Career Veteran',         'description' => 'Complete 20 career runs.',                      'icon' => '🏅', 'rarity' => 'epic',      'target' => 20],
            ['key' => 'ura_finale_clear',      'category' => 'career',    'title' => 'URA Champion',           'description' => 'Clear the URA Finale.',                         'icon' => '🎖️', 'rarity' => 'epic',      'target' => 1],
            ['key' => 'snapshot_created',      'category' => 'career',    'title' => 'Checkpoint Saver',       'description' => 'Create your first run snapshot.',               'icon' => '📸', 'rarity' => 'common',    'target' => 1],
            ['key' => 'snapshots_10',          'category' => 'career',    'title' => 'Snapshot Enthusiast',    'description' => 'Create 10 run snapshots.',                      'icon' => '📸', 'rarity' => 'rare',      'target' => 10],

            // ── Collection ──────────────────────────────────────────────────
            ['key' => 'first_character',       'category' => 'collection', 'title' => 'First Trainee',         'description' => 'Create your first character.',                  'icon' => '👤', 'rarity' => 'common',    'target' => 1],
            ['key' => 'characters_5',          'category' => 'collection', 'title' => 'Growing Stable',        'description' => 'Create 5 characters.',                          'icon' => '👥', 'rarity' => 'common',    'target' => 5],
            ['key' => 'characters_10',         'category' => 'collection', 'title' => 'Full Stable',           'description' => 'Create 10 characters.',                         'icon' => '🏠', 'rarity' => 'rare',      'target' => 10],
            ['key' => 'ssr_deck',              'category' => 'collection', 'title' => 'SSR Collector',         'description' => 'Build a full deck of SSR support cards.',       'icon' => '🎴', 'rarity' => 'epic',      'target' => 1],
            ['key' => 'ocr_upload',            'category' => 'collection', 'title' => 'Screenshot Analyst',    'description' => 'Upload your first OCR screenshot.',             'icon' => '📷', 'rarity' => 'common',    'target' => 1],
        ];
    }

    /**
     * Seed all achievement definitions for a user (idempotent).
     */
    public function seedForUser(User $user): void
    {
        foreach (self::definitions() as $def) {
            Achievement::firstOrCreate(
                ['user_id' => $user->id, 'key' => $def['key']],
                [
                    'category' => $def['category'],
                    'title' => $def['title'],
                    'description' => $def['description'],
                    'icon' => $def['icon'],
                    'rarity' => $def['rarity'],
                    'target' => $def['target'],
                    'progress' => 0,
                    'is_unlocked' => false,
                ]
            );
        }
    }

    /**
     * Get all achievements for a user, seeding if needed.
     *
     * @return Collection<int, Achievement>
     */
    public function getForUser(User $user): Collection
    {
        $this->seedForUser($user);

        return Achievement::where('user_id', $user->id)
            ->orderBy('category')
            ->orderBy('rarity', 'desc')
            ->orderBy('title')
            ->get();
    }

    /**
     * Get achievements grouped by category.
     *
     * @return Collection<string, Collection<int, Achievement>>
     */
    public function getGroupedForUser(User $user): Collection
    {
        return $this->getForUser($user)->groupBy('category');
    }

    /**
     * Update progress for a specific achievement key.
     * Returns the achievement if it was just unlocked, null otherwise.
     */
    public function updateProgress(User $user, string $key, int $progress): ?Achievement
    {
        $achievement = Achievement::where('user_id', $user->id)
            ->where('key', $key)
            ->first();

        if ($achievement === null) {
            // Seed and retry
            $this->seedForUser($user);
            $achievement = Achievement::where('user_id', $user->id)
                ->where('key', $key)
                ->first();
        }

        if ($achievement === null || $achievement->is_unlocked) {
            return null;
        }

        $wasUnlocked = $achievement->is_unlocked;
        $achievement->progress = min($progress, $achievement->target);

        if ($achievement->progress >= $achievement->target) {
            $achievement->is_unlocked = true;
            $achievement->unlocked_at = now();
            $achievement->save();

            return $achievement;
        }

        $achievement->save();

        return null;
    }

    /**
     * Increment progress for a specific achievement key by a given amount.
     * Returns the achievement if it was just unlocked, null otherwise.
     */
    public function incrementProgress(User $user, string $key, int $amount = 1): ?Achievement
    {
        $achievement = Achievement::where('user_id', $user->id)
            ->where('key', $key)
            ->first();

        if ($achievement === null) {
            $this->seedForUser($user);
            $achievement = Achievement::where('user_id', $user->id)
                ->where('key', $key)
                ->first();
        }

        if ($achievement === null || $achievement->is_unlocked) {
            return null;
        }

        return $this->updateProgress($user, $key, $achievement->progress + $amount);
    }

    /**
     * Get summary stats for a user's achievements.
     *
     * @return array{total: int, unlocked: int, locked: int, percent: int, by_rarity: array<string, int>}
     */
    public function getSummary(User $user): array
    {
        $achievements = $this->getForUser($user);
        $total = $achievements->count();
        $unlocked = $achievements->where('is_unlocked', true)->count();

        /** @var array<string, int> $byRarity */
        $byRarity = $achievements
            ->where('is_unlocked', true)
            ->groupBy('rarity')
            ->map(fn ($group) => $group->count())
            ->toArray();

        return [
            'total' => $total,
            'unlocked' => $unlocked,
            'locked' => $total - $unlocked,
            'percent' => $total > 0 ? (int) round(($unlocked / $total) * 100) : 0,
            'by_rarity' => $byRarity,
        ];
    }

    /**
     * Get recently unlocked achievements (last 5).
     *
     * @return Collection<int, Achievement>
     */
    public function getRecentlyUnlocked(User $user, int $limit = 5): Collection
    {
        return Achievement::where('user_id', $user->id)
            ->where('is_unlocked', true)
            ->orderByDesc('unlocked_at')
            ->limit($limit)
            ->get();
    }
}
