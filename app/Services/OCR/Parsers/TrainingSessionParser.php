<?php

declare(strict_types=1);

namespace App\Services\OCR\Parsers;

/**
 * Training Session Parser
 *
 * Parses training selection screens to extract training options, stat gains, and energy costs.
 * Supports both URA Finale and Unity Cup training mechanics.
 *
 * Requirements: Task 5.1.3, Requirement 23.3, 23.4
 */
class TrainingSessionParser extends AbstractScreenParser
{
    /**
     * Training type patterns
     */
    protected const TRAINING_PATTERNS = [
        'training_type' => '/(?:トレーニング|Training)\s*[:：]?\s*([\p{Han}\p{Hiragana}\p{Katakana}]+|[a-zA-Z]+)/iu',
        'stat_gain' => '/(?:スピード|スタミナ|パワー|根性|賢さ|Speed|Stamina|Power|Guts|Wit)\s*[+＋]\s*(\d{1,3})/iu',
        'energy_cost' => '/(?:体力|Energy)\s*[-－]\s*(\d{1,3})/iu',
        'skill_hint' => '/(?:スキルヒント|Skill\s*Hint|ヒント|Hint)/iu',
        'friendship' => '/(?:友情|Friendship|絆|Bond)/iu',
        'spirit_burst' => '/(?:スピリットバースト|Spirit\s*Burst|炎|Flame)/iu',
    ];

    /**
     * Training type normalization
     */
    protected const TRAINING_TYPES = [
        'スピード' => 'speed',
        'スタミナ' => 'stamina',
        'パワー' => 'power',
        '根性' => 'guts',
        '賢さ' => 'wit',
        '休息' => 'rest',
        '保健室' => 'infirmary',
        'お出かけ' => 'outing',
    ];

    public function getScreenType(): string
    {
        return 'training_session';
    }

    public function parse(): array
        $data = [];
        $errors = [];

        // Extract training type
        $trainingType = $this->extractTrainingType($text);
        if ($trainingType) {
            (is_array($data) && isset($data['training_type']) ? $data['training_type'] : null) = $trainingType;
        }

        // Extract stat gains
        $statGains = $this->extractStatGains($text);
        if (! empty($statGains)) {
            (is_array($data) && isset($data['stat_gains']) ? $data['stat_gains'] : null) = $statGains;
        }

        // Extract energy cost
        $energyCost = $this->extractNumeric($text, self::TRAINING_PATTERNS['energy_cost']);
        if ($energyCost !== null) {
            (is_array($data) && isset($data['energy_cost']) ? $data['energy_cost'] : null) = $energyCost;
        }

        // Check for special indicators
        (is_array($data) && isset($data['has_skill_hint']) ? $data['has_skill_hint'] : null) = preg_match(self::TRAINING_PATTERNS['skill_hint'], $text) === 1;
        (is_array($data) && isset($data['has_friendship']) ? $data['has_friendship'] : null) = preg_match(self::TRAINING_PATTERNS['friendship'], $text) === 1;
        (is_array($data) && isset($data['has_spirit_burst']) ? $data['has_spirit_burst'] : null) = preg_match(self::TRAINING_PATTERNS['spirit_burst'], $text) === 1;

        // Validate extracted data
        $validation = $this->validate($data);
        if (! $validation['valid']) {
            $errors = array_merge($errors, $validation['errors']);
        }

        // Calculate confidence
        $requiredFields = ['training_type', 'stat_gains'];
        $confidence = $this->calculateConfidence($data, $requiredFields);

        $result = [
            'success' => $confidence >= 0.3,
            'data' => $data,
            'confidence' => $confidence,
            'errors' => $errors,
        ];

        $this->logResult($result);

        return $result;
    }

    public function validate(): array
        $errors = [];

        // Validate training type
        if (isset((is_array($data) && isset($data['training_type']) ? $data['training_type'] : null))) {
            $validTypes = array_values(self::TRAINING_TYPES);
            if (! in_array((is_array($data) && isset($data['training_type']) ? $data['training_type'] : null), $validTypes)) {
                $errors[] = "Invalid training type: {(is_array($data) && isset($data['training_type']) ? $data['training_type'] : null)}";
            }
        }

        // Validate stat gains
        if (isset((is_array($data) && isset($data['stat_gains']) ? $data['stat_gains'] : null))) {
            foreach ((is_array($data) && isset($data['stat_gains']) ? $data['stat_gains'] : null) as $stat => $gain) {
                if ($gain < 0 || $gain > 200) {
                    $errors[] = "Invalid stat gain for {$stat}: {$gain} (must be 0-200)";
                }
            }
        }

        // Validate energy cost
        if (isset((is_array($data) && isset($data['energy_cost']) ? $data['energy_cost'] : null)) && ! $this->validatePercentage((is_array($data) && isset($data['energy_cost']) ? $data['energy_cost'] : null))) {
            $errors[] = "Invalid energy cost: {(is_array($data) && isset($data['energy_cost']) ? $data['energy_cost'] : null)} (must be 0-100)";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Extract training type from text
     */
    protected function extractTrainingType(string $text): ?string
    {
        $typeText = $this->extractText($text, self::TRAINING_PATTERNS['training_type']);
        if (! $typeText) {
            return null;
        }

        // Normalize to English
        foreach (self::TRAINING_TYPES as $japanese => $english) {
            if (mb_stripos($typeText, $japanese) !== false || mb_stripos($typeText, $english) !== false) {
                return $english;
            }
        }

        return null;
    }

    /**
     * Extract stat gains from text
     *
     * @return array<string, int>
     */
    protected function extractStatGains(): array
        $gains = [];

        // Find all stat gain patterns
        if (preg_match_all(self::TRAINING_PATTERNS['stat_gain'], $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $fullMatch = $match[0];
                $gain = (int) $match[1];

                // Determine which stat this gain is for
                foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
                    $statKeywords = [
                        'speed' => ['スピード', 'Speed', 'SPD'],
                        'stamina' => ['スタミナ', 'Stamina', 'STA'],
                        'power' => ['パワー', 'Power', 'POW'],
                        'guts' => ['根性', 'Guts', 'GUT'],
                        'wit' => ['賢さ', 'Wit', 'INT'],
                    ];

                    foreach ($statKeywords[$stat] as $keyword) {
                        if (mb_stripos($fullMatch, $keyword) !== false) {
                            $gains[$stat] = $gain;
                            break 2;
                        }
                    }
                }
            }
        }

        return $gains;
    }
}
