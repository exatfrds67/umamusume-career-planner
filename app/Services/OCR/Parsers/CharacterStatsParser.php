<?php

declare(strict_types=1);

namespace App\Services\OCR\Parsers;

/**
 * Character Stats Parser
 *
 * Parses character stat screens to extract Speed, Stamina, Power, Guts, Wit values.
 * Validates stat ranges (0-1200) and calculates confidence scores.
 *
 * Requirements: Task 5.1.3, Requirement 23.3, 23.4
 */
class CharacterStatsParser extends AbstractScreenParser
{
    /**
     * Stat extraction patterns (Japanese and English)
     */
    protected const STAT_PATTERNS = [
        'speed' => '/(?:スピード|Speed|SPD)\s*[:：]?\s*(\d{2,4})/iu',
        'stamina' => '/(?:スタミナ|Stamina|STA)\s*[:：]?\s*(\d{2,4})/iu',
        'power' => '/(?:パワー|Power|POW)\s*[:：]?\s*(\d{2,4})/iu',
        'guts' => '/(?:根性|Guts|GUT)\s*[:：]?\s*(\d{2,4})/iu',
        'wit' => '/(?:賢さ|Wit|INT|賢)\s*[:：]?\s*(\d{2,4})/iu',
    ];

    /**
     * Additional data patterns
     */
    protected const ADDITIONAL_PATTERNS = [
        'character_name' => '/(?:ウマ娘|Character)\s*[:：]?\s*([\p{Han}\p{Hiragana}\p{Katakana}\s]+)/iu',
        'turn' => '/(?:ターン|Turn)\s*[:：]?\s*(\d{1,2})(?:\/(\d{1,2}))?/iu',
        'energy' => '/(?:体力|Energy|HP)\s*[:：]?\s*(\d{1,3})/iu',
        'mood' => '/(?:やる気|Mood)\s*[:：]?\s*([\p{Han}\p{Hiragana}\p{Katakana}]+|[a-zA-Z]+)/iu',
    ];

    /**
     * Mood normalization map
     */
    protected const MOOD_MAP = [
        '絶好調' => 'great',
        '好調' => 'good',
        '普通' => 'normal',
        '不調' => 'bad',
        '絶不調' => 'awful',
    ];

    public function getScreenType(): string
    {
        return 'character_stats';
    }

    public function parse(): array
        $data = [];
        $errors = [];

        // Extract stats
        $stats = $this->extractStats($text);
        (is_array($data) && isset($data['stats']) ? $data['stats'] : null) = $stats;

        // Extract additional data
        $additionalData = $this->extractAdditionalData($text);
        $data = array_merge($data, $additionalData);

        // Validate extracted data
        $validation = $this->validate($data);
        if (! $validation['valid']) {
            $errors = array_merge($errors, $validation['errors']);
        }

        // Calculate confidence
        $requiredFields = ['stats'];
        $confidence = $this->calculateStatsConfidence($stats);

        $result = [
            'success' => $confidence >= 0.4,
            'data' => $data,
            'confidence' => $confidence,
            'errors' => $errors,
        ];

        $this->logResult($result);

        return $result;
    }

    public function validate(): array
        $errors = [];

        // Validate stats exist
        if (! isset((is_array($data) && isset($data['stats']) ? $data['stats'] : null)) || ! is_array((is_array($data) && isset($data['stats']) ? $data['stats'] : null))) {
            $errors[] = 'Stats data is missing or invalid';

            return ['valid' => false, 'errors' => $errors];
        }

        // Validate each stat value
        foreach ((is_array($data) && isset($data['stats']) ? $data['stats'] : null) as $stat => $value) {
            if ($value !== null && ! $this->validateStatValue($value)) {
                $errors[] = "Invalid {$stat} value: {$value} (must be 0-1200)";
            }
        }

        // Validate energy if present
        if (isset((is_array($data) && isset($data['energy_level']) ? $data['energy_level'] : null)) && ! $this->validatePercentage((is_array($data) && isset($data['energy_level']) ? $data['energy_level'] : null))) {
            $errors[] = "Invalid energy level: {(is_array($data) && isset($data['energy_level']) ? $data['energy_level'] : null)} (must be 0-100)";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Extract stats from OCR text
     *
     * @return array<string, int|null>
     */
    protected function extractStats(): array
        $stats = [
            'speed' => null,
            'stamina' => null,
            'power' => null,
            'guts' => null,
            'wit' => null,
        ];

        foreach (self::STAT_PATTERNS as $stat => $pattern) {
            $value = $this->extractNumeric($text, $pattern);
            if ($value !== null && $this->validateStatValue($value)) {
                $stats[$stat] = $value;
            }
        }

        return $stats;
    }

    /**
     * Extract additional data from OCR text
     *
     * @return array<string, mixed>
     */
    protected function extractAdditionalData(): array
        $data = [];

        // Extract character name
        $characterName = $this->extractText($text, self::ADDITIONAL_PATTERNS['character_name']);
        if ($characterName) {
            (is_array($data) && isset($data['character_name']) ? $data['character_name'] : null) = $characterName;
        }

        // Extract turn
        if (preg_match(self::ADDITIONAL_PATTERNS['turn'], $text, $matches)) {
            (is_array($data) && isset($data['current_turn']) ? $data['current_turn'] : null) = (int) $matches[1];
            if (isset($matches[2])) {
                (is_array($data) && isset($data['total_turns']) ? $data['total_turns'] : null) = (int) $matches[2];
            }
        }

        // Extract energy
        $energy = $this->extractNumeric($text, self::ADDITIONAL_PATTERNS['energy']);
        if ($energy !== null && $this->validatePercentage($energy)) {
            (is_array($data) && isset($data['energy_level']) ? $data['energy_level'] : null) = $energy;
        }

        // Extract mood
        $mood = $this->extractText($text, self::ADDITIONAL_PATTERNS['mood']);
        if ($mood) {
            (is_array($data) && isset($data['mood_status']) ? $data['mood_status'] : null) = $this->normalizeMood($mood);
        }

        return $data;
    }

    /**
     * Calculate confidence based on stats found
     *
     * @param  array<string, int|null>  $stats
     */
    protected function calculateStatsConfidence(array $stats): float
    {
        $foundCount = count(array_filter($stats, fn ($v) => $v !== null));
        $totalStats = 5;

        return round($foundCount / $totalStats, 2);
    }

    /**
     * Normalize mood status to standard values
     */
    protected function normalizeMood(string $mood): string
    {
        // Check Japanese mood
        if (isset(self::MOOD_MAP[$mood])) {
            return self::MOOD_MAP[$mood];
        }

        // Check English mood
        $lower = strtolower(trim($mood));
        $englishMoods = ['great', 'good', 'normal', 'bad', 'awful'];
        if (in_array($lower, $englishMoods)) {
            return $lower;
        }

        return 'normal';
    }
}
