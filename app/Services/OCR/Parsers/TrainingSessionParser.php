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

    public function parse(string $text): array
    {
        $data = [];
        $errors = [];

        // Extract training type
        $trainingType = $this->extractTrainingType($text);
        if ($trainingType) {
            $data['training_type'] = $trainingType;
        }

        // Extract stat gains
        $statGains = $this->extractStatGains($text);
        if (! empty($statGains)) {
            $data['stat_gains'] = $statGains;
        }

        // Extract energy cost
        $energyCost = $this->extractNumeric($text, self::TRAINING_PATTERNS['energy_cost']);
        if ($energyCost !== null) {
            $data['energy_cost'] = $energyCost;
        }

        // Check for special indicators
        $data['has_skill_hint'] = preg_match(self::TRAINING_PATTERNS['skill_hint'], $text) === 1;
        $data['has_friendship'] = preg_match(self::TRAINING_PATTERNS['friendship'], $text) === 1;
        $data['has_spirit_burst'] = preg_match(self::TRAINING_PATTERNS['spirit_burst'], $text) === 1;

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

    public function validate(array $data): array
    {
        $errors = [];

        // Validate training type
        if (isset($data['training_type'])) {
            $validTypes = array_values(self::TRAINING_TYPES);
            $trainingType = is_string($data['training_type']) ? $data['training_type'] : '';
            if (! in_array($trainingType, $validTypes)) {
                $errors[] = "Invalid training type: {$trainingType}";
            }
        }

        // Validate stat gains
        if (isset($data['stat_gains']) && is_array($data['stat_gains'])) {
            /** @var array<string, mixed> $statGains */
            $statGains = $data['stat_gains'];
            foreach ($statGains as $stat => $gain) {
                $statName = is_string($stat) ? $stat : '';
                $gainValue = is_int($gain) ? $gain : 0;
                if ($gainValue < 0 || $gainValue > 200) {
                    $errors[] = "Invalid stat gain for {$statName}: {$gainValue} (must be 0-200)";
                }
            }
        }

        // Validate energy cost
        if (isset($data['energy_cost'])) {
            $energyCost = is_int($data['energy_cost']) ? $data['energy_cost'] : 0;
            if (! $this->validatePercentage($energyCost)) {
                $errors[] = "Invalid energy cost: {$energyCost} (must be 0-100)";
            }
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
    protected function extractStatGains(string $text): array
    {
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
