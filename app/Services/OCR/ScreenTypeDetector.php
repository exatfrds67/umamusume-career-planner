<?php

declare(strict_types=1);

namespace App\Services\OCR;

use Illuminate\Support\Facades\Log;

/**
 * Screen Type Detector
 *
 * Detects the type of game screen from OCR text to apply appropriate parsing strategies.
 * Supports: character_stats, training_session, race_result, skill_list, support_cards
 *
 * Requirements: Task 5.1.3, Requirement 23.3
 */
class ScreenTypeDetector
{
    /**
     * Screen type detection patterns
     */
    protected const DETECTION_PATTERNS = [
        'character_stats' => [
            'keywords' => ['スピード', 'スタミナ', 'パワー', '根性', '賢さ', 'Speed', 'Stamina', 'Power', 'Guts', 'Wit'],
            'required_matches' => 3,
            'weight' => 1.2,
        ],
        'training_session' => [
            'keywords' => ['トレーニング', 'Training', '体力', 'やる気', 'ターン', 'Turn', '友情', 'Friendship'],
            'required_matches' => 2,
            'weight' => 0.9,
        ],
        'race_result' => [
            'keywords' => ['着順', 'Position', 'レース', 'Race', '勝利', 'Victory', '敗北', 'Defeat', 'ファン', 'Fans', '結果', 'Result'],
            'required_matches' => 2,
            'weight' => 1.0,
        ],
        'skill_list' => [
            'keywords' => ['スキル', 'Skill', 'SP', 'ヒント', 'Hint', '習得', 'Acquired', 'コスト', 'Cost'],
            'required_matches' => 2,
            'weight' => 0.85,
        ],
        'support_cards' => [
            'keywords' => ['サポート', 'Support', 'カード', 'Card', '絆', 'Bond', 'デッキ', 'Deck'],
            'required_matches' => 2,
            'weight' => 0.8,
        ],
    ];

    /**
     * Detect screen type from OCR text
     *
     * @return array{
     *     detected_type: string|null,
     *     confidence: float,
     *     scores: array<string, float>
     * }
     */
    public function detectScreenType(string $text): array
    {
        $scores = [];

        foreach (self::DETECTION_PATTERNS as $type => $config) {
            $matchCount = 0;

            foreach ($config['keywords'] as $keyword) {
                if (mb_stripos($text, $keyword) !== false) {
                    $matchCount++;
                }
            }

            // Calculate score based on matches and weight
            $matchRatio = $matchCount / count($config['keywords']);
            $scores[$type] = $matchRatio * $config['weight'];
        }

        // Find the highest scoring type
        arsort($scores);
        $topType = array_key_first($scores);
        $topScore = $scores[$topType] ?? 0.0;

        // Require minimum confidence threshold
        $detectedType = $topScore >= 0.3 ? $topType : null;

        Log::info('[ScreenTypeDetector] Screen type detection completed', [
            'detected_type' => $detectedType,
            'confidence' => $topScore,
            'all_scores' => $scores,
        ]);

        return [
            'detected_type' => $detectedType,
            'confidence' => round($topScore, 2),
            'scores' => array_map(fn ($score) => round($score, 2), $scores),
        ];
    }

    /**
     * Validate if text matches expected screen type
     */
    public function validateScreenType(string $text, string $expectedType): bool
    {
        $detection = $this->detectScreenType($text);

        return $detection['detected_type'] === $expectedType && $detection['confidence'] >= 0.5;
    }

    /**
     * Get supported screen types
     *
     * @return array<string>
     */
    public function getSupportedTypes(): array
    {
        return array_keys(self::DETECTION_PATTERNS);
    }
}
