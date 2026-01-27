<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

/**
 * GameTora Scraper Service
 *
 * Scrapes detailed support card data from GameTora website.
 * Implements caching and rate limiting to be respectful of the source.
 */
class GameToraScraperService
{
    protected const BASE_URL = 'https://gametora.com/umamusume/supports';

    protected const CACHE_PREFIX = 'gametora:card:';

    protected const CACHE_TTL = 604800; // 7 days

    protected const REQUEST_DELAY_MS = 500; // Delay between requests

    /**
     * Fetch detailed card data from GameTora
     *
     * @return array<string, mixed>|null
     */
    public function fetchCardDetails(string $gametoraId): ?array
    {
        $cacheKey = self::CACHE_PREFIX.$gametoraId;

        /** @var array<string, mixed>|null $cached */
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            // Rate limiting
            usleep(self::REQUEST_DELAY_MS * 1000);

            $url = self::BASE_URL.'/'.$gametoraId;
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'UmamusumeCareerPlanner/2.0 (Data Enrichment)',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($url);

            if (! $response->successful()) {
                Log::warning('[GameToraScraperService] Failed to fetch card', [
                    'gametora_id' => $gametoraId,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $html = $response->body();
            $data = $this->parseCardPage($html, $gametoraId);

            if ($data !== null) {
                Cache::put($cacheKey, $data, self::CACHE_TTL);
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('[GameToraScraperService] Error fetching card', [
                'gametora_id' => $gametoraId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parse card page HTML
     *
     * @return array<string, mixed>|null
     */
    protected function parseCardPage(string $html, string $gametoraId): ?array
    {
        try {
            $crawler = new Crawler($html);

            $data = [
                'gametora_id' => $gametoraId,
                'effects' => $this->parseEffects($crawler),
                'support_hints' => $this->parseSupportHints($crawler),
                'event_skills' => $this->parseEventSkills($crawler),
                'events' => $this->parseEvents($crawler),
                'unique_effects_text' => $this->parseUniqueEffects($crawler),
            ];

            return $data;
        } catch (\Exception $e) {
            Log::warning('[GameToraScraperService] Parse error', [
                'gametora_id' => $gametoraId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parse training effects/bonuses
     *
     * @return array<string, mixed>
     */
    protected function parseEffects(Crawler $crawler): array
    {
        $effects = [];

        // Try to find effect containers
        $crawler->filter('[class*="effect"]')->each(function (Crawler $node) use (&$effects) {
            $text = trim($node->text());

            // Parse common effect patterns
            if (preg_match('/Friendship Bonus.*?(\d+)/i', $text, $m)) {
                $effects['friendship_bonus'] = (int) $m[1];
            }
            if (preg_match('/Training Effectiveness.*?(\d+)/i', $text, $m)) {
                $effects['training_effectiveness'] = (int) $m[1];
            }
            if (preg_match('/Initial Friendship.*?(\d+)/i', $text, $m)) {
                $effects['initial_friendship_gauge'] = (int) $m[1];
            }
            if (preg_match('/Race Bonus.*?(\d+)/i', $text, $m)) {
                $effects['race_bonus'] = (int) $m[1];
            }
            if (preg_match('/Fan Bonus.*?(\d+)/i', $text, $m)) {
                $effects['fan_bonus'] = (int) $m[1];
            }
            if (preg_match('/Mood Effect.*?(\d+)/i', $text, $m)) {
                $effects['mood_effect'] = (int) $m[1];
            }
            if (preg_match('/Hint Level.*?(\d+)/i', $text, $m)) {
                $effects['hint_levels'] = (int) $m[1];
            }
            if (preg_match('/Hint.*?(\d+)%/i', $text, $m)) {
                $effects['hint_frequency'] = (int) $m[1];
            }
            if (preg_match('/Specialty.*?(\d+)/i', $text, $m)) {
                $effects['specialty_priority'] = (int) $m[1];
            }
            if (preg_match('/Event Recovery.*?(\d+)/i', $text, $m)) {
                $effects['event_recovery'] = (int) $m[1];
            }
            if (preg_match('/Event Effectiveness.*?(\d+)/i', $text, $m)) {
                $effects['event_effectiveness'] = (int) $m[1];
            }
            if (preg_match('/Failure.*?(\d+)/i', $text, $m)) {
                $effects['failure_protection'] = (int) $m[1];
            }
            if (preg_match('/Energy.*?Reduction.*?(\d+)/i', $text, $m)) {
                $effects['energy_cost_reduction'] = (int) $m[1];
            }
        });

        return $effects;
    }

    /**
     * Parse support hints (skills from training)
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseSupportHints(Crawler $crawler): array
    {
        $hints = [];

        // Look for skill hint sections
        $crawler->filter('[class*="skill"], [class*="hint"]')->each(function (Crawler $node) use (&$hints) {
            $text = trim($node->text());

            // Skip empty or very short text
            if (strlen($text) < 3) {
                return;
            }

            // Check if it looks like a skill name
            if (preg_match('/^[A-Z][a-zA-Z\s◯○]+$/', $text) || str_contains($text, '◯') || str_contains($text, '○')) {
                $hints[] = [
                    'name' => $text,
                    'is_conditional' => str_contains($text, '◯') || str_contains($text, '○'),
                ];
            }
        });

        return array_values(array_unique($hints, SORT_REGULAR));
    }

    /**
     * Parse event skills
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseEventSkills(Crawler $crawler): array
    {
        $skills = [];

        // Look for skill containers with gold/rare indicators
        $crawler->filter('[class*="skill"]')->each(function (Crawler $node) use (&$skills) {
            $text = trim($node->text());
            $class = $node->attr('class') ?? '';

            if (strlen($text) < 3) {
                return;
            }

            // Determine if it's a gold/rare skill based on class or styling
            $isGold = str_contains($class, 'gold') ||
                str_contains($class, 'rare') ||
                str_contains($class, 'gradient');

            if (preg_match('/^[A-Z][a-zA-Z\s!]+$/', $text)) {
                $skills[] = [
                    'name' => $text,
                    'rarity' => $isGold ? 'gold' : 'normal',
                ];
            }
        });

        return array_values(array_unique($skills, SORT_REGULAR));
    }

    /**
     * Parse events
     *
     * @return array<string, array<int, string>>
     */
    protected function parseEvents(Crawler $crawler): array
    {
        $events = [
            'chain_events' => [],
            'random_events' => [],
        ];

        // Look for event sections
        $crawler->filter('[class*="event"]')->each(function (Crawler $node) use (&$events) {
            $text = trim($node->text());

            if (strlen($text) < 3 || strlen($text) > 100) {
                return;
            }

            // Chain events often have ❯ or similar markers
            if (str_contains($text, '❯') || str_contains($text, '→')) {
                $events['chain_events'][] = $text;
            } else {
                $events['random_events'][] = $text;
            }
        });

        return $events;
    }

    /**
     * Parse unique effects text
     *
     * @return array<int, string>
     */
    protected function parseUniqueEffects(Crawler $crawler): array
    {
        $effects = [];

        $crawler->filter('[class*="unique"], [class*="special"]')->each(function (Crawler $node) use (&$effects) {
            $text = trim($node->text());

            if (strlen($text) > 10 && strlen($text) < 500) {
                $effects[] = $text;
            }
        });

        return array_values(array_unique($effects));
    }

    /**
     * Batch fetch multiple cards with progress callback
     *
     * @param  array<int, string>  $gametoraIds
     * @return array<string, array<string, mixed>|null>
     */
    public function batchFetchCards(array $gametoraIds, ?callable $progressCallback = null): array
    {
        $results = [];
        $total = count($gametoraIds);
        $current = 0;

        foreach ($gametoraIds as $gametoraId) {
            $current++;

            if ($progressCallback !== null) {
                $progressCallback($current, $total, $gametoraId);
            }

            $results[$gametoraId] = $this->fetchCardDetails($gametoraId);
        }

        return $results;
    }

    /**
     * Clear cache for a specific card
     */
    public function clearCardCache(string $gametoraId): void
    {
        Cache::forget(self::CACHE_PREFIX.$gametoraId);
    }

    /**
     * Clear all GameTora cache
     */
    public function clearAllCache(): void
    {
        // Note: This requires Redis SCAN or similar for pattern-based deletion
        // For now, individual cards must be cleared
        Log::info('[GameToraScraperService] Cache clear requested');
    }
}
