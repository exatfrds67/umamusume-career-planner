<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Enums\Priority;
use App\Enums\RecommendationType;

/**
 * Recommendation Value Object
 *
 * Represents a single actionable recommendation from the AI-Powered Training Advisory System.
 * Recommendations include training facility choices, skill purchases, race strategies,
 * rest/recovery actions, and bond building activities.
 *
 * @see \App\Services\TrainingAdvisoryService
 * @see \App\Services\RuleBasedAdvisor
 */
final readonly class Recommendation
{
    /**
     * Create a new Recommendation
     *
     * @param  RecommendationType  $type  Type of recommendation
     * @param  Priority  $priority  Priority level (CRITICAL, HIGH, MEDIUM, LOW)
     * @param  string  $action  Specific action to take (e.g., "Speed Training", "Purchase Swinging Maestro")
     * @param  string  $reasoning  Explanation of why this recommendation is being made
     * @param  array<string, mixed>  $expectedOutcomes  Expected results of following this recommendation
     * @param  array<string>  $risks  Potential risks or downsides
     * @param  float|null  $confidenceScore  AI confidence score (0.0-1.0), null for rule-based
     * @param  string  $source  Source of recommendation ('ai' or 'rule-based')
     * @param  string  $storageMode  Storage mode this recommendation applies to ('local' or 'account')
     * @param  bool  $isFriendshipTraining  Whether this is a Friendship Training recommendation
     */
    public function __construct(
        public RecommendationType $type,
        public Priority $priority,
        public string $action,
        public string $reasoning,
        public array $expectedOutcomes,
        public array $risks,
        public ?float $confidenceScore = null,
        public string $source = 'ai',
        public string $storageMode = 'account',
        public bool $isFriendshipTraining = false,
    ) {}

    /**
     * Check if this is a training facility recommendation
     */
    public function isTrainingRecommendation(): bool
    {
        return $this->type === RecommendationType::TRAINING_FACILITY;
    }

    /**
     * Check if this is a skill purchase recommendation
     */
    public function isSkillRecommendation(): bool
    {
        return $this->type === RecommendationType::SKILL_PURCHASE;
    }

    /**
     * Check if this is a race strategy recommendation
     */
    public function isRaceRecommendation(): bool
    {
        return $this->type === RecommendationType::RACE_STRATEGY;
    }

    /**
     * Check if this is a rest/recovery recommendation
     */
    public function isRestRecommendation(): bool
    {
        return $this->type === RecommendationType::REST_RECOVERY;
    }

    /**
     * Check if this is a bond building recommendation
     */
    public function isBondRecommendation(): bool
    {
        return $this->type === RecommendationType::BOND_BUILDING;
    }

    /**
     * Check if this recommendation is critical priority
     */
    public function isCritical(): bool
    {
        return $this->priority->isCritical();
    }

    /**
     * Check if this recommendation is high or critical priority
     */
    public function isHighPriority(): bool
    {
        return $this->priority->isHighOrCritical();
    }

    /**
     * Check if this recommendation came from AI
     */
    public function isAIGenerated(): bool
    {
        return $this->source === 'ai';
    }

    /**
     * Check if this recommendation came from rule-based advisor
     */
    public function isRuleBased(): bool
    {
        return $this->source === 'rule-based';
    }

    /**
     * Check if this is for local storage mode
     */
    public function isLocalMode(): bool
    {
        return $this->storageMode === 'local';
    }

    /**
     * Check if this is for account storage mode
     */
    public function isAccountMode(): bool
    {
        return $this->storageMode === 'account';
    }

    /**
     * Get a summary string for display
     */
    public function getSummary(): string
    {
        $priority = $this->priority->label();
        $type = $this->type->label();

        return "[{$priority}] {$type}: {$this->action}";
    }

    /**
     * Get expected outcome as a formatted string
     */
    public function getExpectedOutcomesText(): string
    {
        if (empty($this->expectedOutcomes)) {
            return 'No specific outcomes predicted';
        }

        $lines = [];
        foreach ($this->expectedOutcomes as $key => $value) {
            $keyStr = is_string($key) ? $key : (string) $key;
            if (is_array($value)) {
                $stringValues = array_values(array_map(static fn (mixed $item): string => is_scalar($item) ? (string) $item : '', $value));
                $lines[] = ucfirst(str_replace('_', ' ', $keyStr)).': '.implode(', ', $stringValues);
            } else {
                $valueStr = is_scalar($value) ? (string) $value : '';
                $lines[] = ucfirst(str_replace('_', ' ', $keyStr)).': '.$valueStr;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Get risks as a formatted string
     */
    public function getRisksText(): string
    {
        if (empty($this->risks)) {
            return 'No significant risks identified';
        }

        return '• '.implode("\n• ", $this->risks);
    }

    /**
     * Get confidence score as a percentage string
     */
    public function getConfidencePercentage(): ?string
    {
        if ($this->confidenceScore === null) {
            return null;
        }

        return number_format($this->confidenceScore * 100, 1).'%';
    }

    /**
     * Create recommendation from an array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        // Validate and cast risks to array<string>
        $risks = [];
        if (isset($data['risks']) && is_array($data['risks'])) {
            foreach ($data['risks'] as $risk) {
                if (is_string($risk) || is_numeric($risk)) {
                    $risks[] = (string) $risk;
                }
            }
        }

        $type = $data['type'] ?? 'training_facility';
        $priority = $data['priority'] ?? 'medium';
        $action = $data['action'] ?? '';
        $reasoning = $data['reasoning'] ?? '';
        $expectedOutcomes = $data['expected_outcomes'] ?? [];
        $confidenceScore = $data['confidence_score'] ?? null;
        $source = $data['source'] ?? 'ai';
        $storageMode = $data['storage_mode'] ?? 'account';
        $isFriendshipTraining = $data['is_friendship_training'] ?? false;

        return new self(
            type: RecommendationType::from(is_string($type) ? $type : 'training_facility'),
            priority: Priority::from(is_string($priority) ? $priority : 'medium'),
            action: is_string($action) ? $action : '',
            reasoning: is_string($reasoning) ? $reasoning : '',
            expectedOutcomes: is_array($expectedOutcomes) ? $expectedOutcomes : [],
            risks: $risks,
            confidenceScore: is_float($confidenceScore) || is_int($confidenceScore) ? (float) $confidenceScore : null,
            source: is_string($source) ? $source : 'ai',
            storageMode: is_string($storageMode) ? $storageMode : 'account',
            isFriendshipTraining: is_bool($isFriendshipTraining) ? $isFriendshipTraining : false,
        );
    }

    /**
     * Convert recommendation to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'priority' => $this->priority->value,
            'action' => $this->action,
            'reasoning' => $this->reasoning,
            'expected_outcomes' => $this->expectedOutcomes,
            'risks' => $this->risks,
            'confidence_score' => $this->confidenceScore,
            'source' => $this->source,
            'storage_mode' => $this->storageMode,
            'is_friendship_training' => $this->isFriendshipTraining,
        ];
    }

    /**
     * Create a training facility recommendation
     *
     * @param  array<string, mixed>  $expectedOutcomes
     * @param  array<string>  $risks
     */
    public static function trainingFacility(
        string $facility,
        Priority $priority,
        string $reasoning,
        array $expectedOutcomes,
        array $risks = [],
        ?float $confidenceScore = null,
        string $source = 'ai',
        string $storageMode = 'account',
        bool $isFriendshipTraining = false,
    ): self {
        return new self(
            type: RecommendationType::TRAINING_FACILITY,
            priority: $priority,
            action: ucfirst($facility).' Training',
            reasoning: $reasoning,
            expectedOutcomes: $expectedOutcomes,
            risks: $risks,
            confidenceScore: $confidenceScore,
            source: $source,
            storageMode: $storageMode,
            isFriendshipTraining: $isFriendshipTraining,
        );
    }

    /**
     * Create a skill purchase recommendation
     *
     * @param  array<string, mixed>  $expectedOutcomes
     * @param  array<string>  $risks
     */
    public static function skillPurchase(
        string $skillName,
        Priority $priority,
        string $reasoning,
        array $expectedOutcomes,
        array $risks = [],
        ?float $confidenceScore = null,
        string $source = 'ai',
        string $storageMode = 'account',
    ): self {
        return new self(
            type: RecommendationType::SKILL_PURCHASE,
            priority: $priority,
            action: "Purchase {$skillName}",
            reasoning: $reasoning,
            expectedOutcomes: $expectedOutcomes,
            risks: $risks,
            confidenceScore: $confidenceScore,
            source: $source,
            storageMode: $storageMode,
        );
    }

    /**
     * Create a rest/recovery recommendation
     *
     * @param  array<string, mixed>  $expectedOutcomes
     */
    public static function rest(
        Priority $priority,
        string $reasoning,
        array $expectedOutcomes,
        string $source = 'rule-based',
        string $storageMode = 'account',
    ): self {
        return new self(
            type: RecommendationType::REST_RECOVERY,
            priority: $priority,
            action: 'Rest',
            reasoning: $reasoning,
            expectedOutcomes: $expectedOutcomes,
            risks: [],
            confidenceScore: null,
            source: $source,
            storageMode: $storageMode,
        );
    }

    /**
     * Create a bond building recommendation
     *
     * @param  array<string, mixed>  $expectedOutcomes
     */
    public static function bondBuilding(
        string $facility,
        Priority $priority,
        string $reasoning,
        array $expectedOutcomes,
        string $source = 'ai',
        string $storageMode = 'account',
    ): self {
        return new self(
            type: RecommendationType::BOND_BUILDING,
            priority: $priority,
            action: "Train at {$facility} to build bonds",
            reasoning: $reasoning,
            expectedOutcomes: $expectedOutcomes,
            risks: [],
            confidenceScore: null,
            source: $source,
            storageMode: $storageMode,
        );
    }
}
