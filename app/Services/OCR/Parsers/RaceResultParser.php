<?php

declare(strict_types=1);

namespace App\Services\OCR\Parsers;

/**
 * Race Result Parser
 *
 * Parses race completion screens to extract placement, performance metrics, and rewards.
 * Supports various race grades (G1, G2, G3, OP, Pre-OP).
 *
 * Requirements: Task 5.1.3, Requirement 23.3, 23.4
 */
class RaceResultParser extends AbstractScreenParser
{
    /**
     * Race result patterns
     */
    protected const RACE_PATTERNS = [
        'position' => '/(?:着順|Position|順位)\s*[:：]?\s*(\d{1,2})(?:位|st|nd|rd|th)?/iu',
        'race_name' => '/(?:レース|Race)\s*[:：]?\s*([\p{Han}\p{Hiragana}\p{Katakana}\s]+)/iu',
        'race_grade' => '/(?:G1|G2|G3|OP|Pre-OP|プレOP)/iu',
        'distance' => '/(\d{3,4})m/iu',
        'surface' => '/(?:芝|ダート|Turf|Dirt)/iu',
        'fans_gained' => '/(?:ファン|Fans?)\s*[+＋]\s*(\d{1,6})/iu',
        'skill_points' => '/(?:SP|スキルポイント)\s*[+＋]\s*(\d{1,4})/iu',
    ];

    /**
     * Surface normalization
     */
    protected const SURFACE_MAP = [
        '芝' => 'turf',
        'ダート' => 'dirt',
    ];

    public function getScreenType(): string
    {
        return 'race_result';
    }

    public function parse(): array
        $data = [];
        $errors = [];

        // Extract position
        $position = $this->extractNumeric($text, self::RACE_PATTERNS['position']);
        if ($position !== null) {
            (is_array($data) && isset($data['position']) ? $data['position'] : null) = $position;
        }

        // Extract race name
        $raceName = $this->extractText($text, self::RACE_PATTERNS['race_name']);
        if ($raceName) {
            (is_array($data) && isset($data['race_name']) ? $data['race_name'] : null) = trim($raceName);
        }

        // Extract race grade
        $raceGrade = $this->extractRaceGrade($text);
        if ($raceGrade) {
            (is_array($data) && isset($data['race_grade']) ? $data['race_grade'] : null) = $raceGrade;
        }

        // Extract distance
        $distance = $this->extractNumeric($text, self::RACE_PATTERNS['distance']);
        if ($distance !== null) {
            (is_array($data) && isset($data['distance']) ? $data['distance'] : null) = $distance;
            (is_array($data) && isset($data['distance_category']) ? $data['distance_category'] : null) = $this->categorizeDistance($distance);
        }

        // Extract surface
        $surface = $this->extractSurface($text);
        if ($surface) {
            (is_array($data) && isset($data['surface']) ? $data['surface'] : null) = $surface;
        }

        // Extract rewards
        $fansGained = $this->extractNumeric($text, self::RACE_PATTERNS['fans_gained']);
        if ($fansGained !== null) {
            (is_array($data) && isset($data['fans_gained']) ? $data['fans_gained'] : null) = $fansGained;
        }

        $skillPoints = $this->extractNumeric($text, self::RACE_PATTERNS['skill_points']);
        if ($skillPoints !== null) {
            (is_array($data) && isset($data['skill_points_gained']) ? $data['skill_points_gained'] : null) = $skillPoints;
        }

        // Determine race outcome
        if (isset((is_array($data) && isset($data['position']) ? $data['position'] : null))) {
            (is_array($data) && isset($data['outcome']) ? $data['outcome'] : null) = $this->determineOutcome((is_array($data) && isset($data['position']) ? $data['position'] : null));
        }

        // Validate extracted data
        $validation = $this->validate($data);
        if (! $validation['valid']) {
            $errors = array_merge($errors, $validation['errors']);
        }

        // Calculate confidence
        $requiredFields = ['position', 'race_name'];
        $confidence = $this->calculateConfidence($data, $requiredFields);

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

        // Validate position
        if (isset((is_array($data) && isset($data['position']) ? $data['position'] : null)) && ((is_array($data) && isset($data['position']) ? $data['position'] : null) < 1 || (is_array($data) && isset($data['position']) ? $data['position'] : null) > 18)) {
            $errors[] = "Invalid position: {(is_array($data) && isset($data['position']) ? $data['position'] : null)} (must be 1-18)";
        }

        // Validate distance
        if (isset((is_array($data) && isset($data['distance']) ? $data['distance'] : null)) && ((is_array($data) && isset($data['distance']) ? $data['distance'] : null) < 1000 || (is_array($data) && isset($data['distance']) ? $data['distance'] : null) > 3600)) {
            $errors[] = "Invalid distance: {(is_array($data) && isset($data['distance']) ? $data['distance'] : null)} (must be 1000-3600m)";
        }

        // Validate fans gained
        if (isset((is_array($data) && isset($data['fans_gained']) ? $data['fans_gained'] : null)) && (is_array($data) && isset($data['fans_gained']) ? $data['fans_gained'] : null) < 0) {
            $errors[] = "Invalid fans gained: {(is_array($data) && isset($data['fans_gained']) ? $data['fans_gained'] : null)} (must be >= 0)";
        }

        // Validate skill points
        if (isset((is_array($data) && isset($data['skill_points_gained']) ? $data['skill_points_gained'] : null)) && (is_array($data) && isset($data['skill_points_gained']) ? $data['skill_points_gained'] : null) < 0) {
            $errors[] = "Invalid skill points: {(is_array($data) && isset($data['skill_points_gained']) ? $data['skill_points_gained'] : null)} (must be >= 0)";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Extract race grade from text
     */
    protected function extractRaceGrade(string $text): ?string
    {
        if (preg_match(self::RACE_PATTERNS['race_grade'], $text, $matches)) {
            $grade = strtoupper($matches[0]);

            // Normalize Pre-OP
            if (in_array($grade, ['PRE-OP', 'プレOP'])) {
                return 'Pre-OP';
            }

            return $grade;
        }

        return null;
    }

    /**
     * Extract surface type from text
     */
    protected function extractSurface(string $text): ?string
    {
        if (preg_match(self::RACE_PATTERNS['surface'], $text, $matches)) {
            $surface = $matches[0];

            // Normalize to English
            foreach (self::SURFACE_MAP as $japanese => $english) {
                if (mb_stripos($surface, $japanese) !== false) {
                    return $english;
                }
            }

            return strtolower($surface);
        }

        return null;
    }

    /**
     * Categorize distance into Sprint/Mile/Medium/Long
     */
    protected function categorizeDistance(int $distance): string
    {
        if ($distance <= 1400) {
            return 'sprint';
        } elseif ($distance <= 1800) {
            return 'mile';
        } elseif ($distance <= 2400) {
            return 'medium';
        } else {
            return 'long';
        }
    }

    /**
     * Determine race outcome based on position
     */
    protected function determineOutcome(int $position): string
    {
        return match (true) {
            $position === 1 => 'victory',
            $position <= 3 => 'podium',
            $position <= 5 => 'top_5',
            default => 'defeat',
        };
    }
}
