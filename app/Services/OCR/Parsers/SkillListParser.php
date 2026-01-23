<?php

declare(strict_types=1);

namespace App\Services\OCR\Parsers;

/**
 * Skill List Parser
 *
 * Parses skill management screens to extract skill names, SP costs, hints, and acquisition status.
 * Supports skill evolution tracking and hint-based cost reduction.
 *
 * Requirements: Task 5.1.3, Requirement 23.3, 23.4
 */
class SkillListParser extends AbstractScreenParser
{
    /**
     * Skill patterns
     */
    protected const SKILL_PATTERNS = [
        'skill_name' => '/([\p{Han}\p{Hiragana}\p{Katakana}]{2,}|[a-zA-Z\s]{3,})\s+(?:SP|スキルポイント)\s*[:：]?\s*(\d{1,4})/iu',
        'total_sp' => '/(?:所持SP|Total\s*SP|SP)\s*[:：]?\s*(\d{1,5})/iu',
        'hint_level' => '/(?:ヒント|Hint)\s*(?:Lv|レベル)?\s*[:：]?\s*(\d{1})/iu',
        'acquired' => '/(?:習得済|Acquired|取得済)/iu',
    ];

    /**
     * Skill type keywords
     */
    protected const SKILL_TYPE_KEYWORDS = [
        'speed' => ['スピード', 'Speed', '加速', 'Acceleration'],
        'recovery' => ['回復', 'Recovery', '立て直し', 'Comeback'],
        'passive' => ['パッシブ', 'Passive', '常時', 'Always'],
        'debuff' => ['デバフ', 'Debuff', '妨害', 'Interference'],
    ];

    public function getScreenType(): string
    {
        return 'skill_list';
    }

    public function parse(): array
        $data = [];
        $errors = [];

        // Extract total SP
        $totalSP = $this->extractNumeric($text, self::SKILL_PATTERNS['total_sp']);
        if ($totalSP !== null) {
            (is_array($data) && isset($data['total_sp']) ? $data['total_sp'] : null) = $totalSP;
        }

        // Extract skills
        $skills = $this->extractSkills($text);
        if (! empty($skills)) {
            (is_array($data) && isset($data['skills']) ? $data['skills'] : null) = $skills;
            (is_array($data) && isset($data['skill_count']) ? $data['skill_count'] : null) = count($skills);
        }

        // Validate extracted data
        $validation = $this->validate($data);
        if (! $validation['valid']) {
            $errors = array_merge($errors, $validation['errors']);
        }

        // Calculate confidence
        $requiredFields = ['skills'];
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

        // Validate total SP
        if (isset((is_array($data) && isset($data['total_sp']) ? $data['total_sp'] : null)) && ((is_array($data) && isset($data['total_sp']) ? $data['total_sp'] : null) < 0 || (is_array($data) && isset($data['total_sp']) ? $data['total_sp'] : null) > 99999)) {
            $errors[] = "Invalid total SP: {(is_array($data) && isset($data['total_sp']) ? $data['total_sp'] : null)} (must be 0-99999)";
        }

        // Validate skills
        if (isset((is_array($data) && isset($data['skills']) ? $data['skills'] : null))) {
            foreach ((is_array($data) && isset($data['skills']) ? $data['skills'] : null) as $index => $skill) {
                if (! isset($skill['name']) || empty($skill['name'])) {
                    $errors[] = "Skill #{$index}: Missing skill name";
                }

                if (isset($skill['sp_cost']) && ($skill['sp_cost'] < 0 || $skill['sp_cost'] > 500)) {
                    $errors[] = "Skill #{$index}: Invalid SP cost {$skill['sp_cost']} (must be 0-500)";
                }

                if (isset($skill['hint_level']) && ($skill['hint_level'] < 0 || $skill['hint_level'] > 5)) {
                    $errors[] = "Skill #{$index}: Invalid hint level {$skill['hint_level']} (must be 0-5)";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Extract skills from text
     *
     * @return array<int, array<string, mixed>>
     */
    protected function extractSkills(): array
        $skills = [];

        // Find all skill patterns
        if (preg_match_all(self::SKILL_PATTERNS['skill_name'], $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $skillName = trim($match[1]);
                $spCost = (int) $match[2];

                $skill = [
                    'name' => $skillName,
                    'sp_cost' => $spCost,
                    'is_acquired' => false,
                ];

                // Check if skill is acquired
                $contextStart = max(0, strpos($text, $match[0]) - 50);
                $contextEnd = min(strlen($text), strpos($text, $match[0]) + strlen($match[0]) + 50);
                $context = substr($text, $contextStart, $contextEnd - $contextStart);

                if (preg_match(self::SKILL_PATTERNS['acquired'], $context)) {
                    $skill['is_acquired'] = true;
                }

                // Extract hint level if present
                if (preg_match(self::SKILL_PATTERNS['hint_level'], $context, $hintMatch)) {
                    $skill['hint_level'] = (int) $hintMatch[1];
                }

                // Determine skill type
                $skill['skill_type'] = $this->determineSkillType($skillName);

                $skills[] = $skill;
            }
        }

        return $skills;
    }

    /**
     * Determine skill type from skill name
     */
    protected function determineSkillType(string $skillName): string
    {
        foreach (self::SKILL_TYPE_KEYWORDS as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($skillName, $keyword) !== false) {
                    return $type;
                }
            }
        }

        return 'unknown';
    }
}
