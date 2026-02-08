<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Enums\AlertType;
use App\Enums\Priority;

/**
 * Critical Alert Value Object
 *
 * Represents a high-priority warning that requires immediate player attention.
 * Critical alerts are generated when character state meets dangerous conditions
 * that could significantly impact career run success.
 *
 * @see \App\Services\CriticalSituationDetector
 * @see \App\Services\TrainingAdvisoryService
 */
final readonly class CriticalAlert
{
    /**
     * Create a new Critical Alert
     *
     * @param  AlertType  $type  Type of alert
     * @param  string  $message  Brief alert message
     * @param  array<string>  $actionItems  Specific actions to take
     * @param  int  $turnsUntilCritical  Number of turns until situation becomes critical (0 = already critical)
     * @param  string|null  $detailedAnalysis  Extended analysis and explanation
     * @param  Priority  $priority  Priority level (typically CRITICAL or HIGH)
     * @param  string  $storageMode  Storage mode this alert applies to ('local' or 'account')
     */
    public function __construct(
        public AlertType $type,
        public string $message,
        public array $actionItems,
        public int $turnsUntilCritical,
        public ?string $detailedAnalysis = null,
        public Priority $priority = Priority::CRITICAL,
        public string $storageMode = 'account',
    ) {}

    /**
     * Check if this alert is already critical (0 turns remaining)
     */
    public function isImmediateCritical(): bool
    {
        return $this->turnsUntilCritical === 0;
    }

    /**
     * Check if this alert is approaching critical (1-3 turns)
     */
    public function isApproachingCritical(): bool
    {
        return $this->turnsUntilCritical > 0 && $this->turnsUntilCritical <= 3;
    }

    /**
     * Check if this is a stamina crisis alert
     */
    public function isStaminaCrisis(): bool
    {
        return $this->type === AlertType::STAMINA_CRISIS;
    }

    /**
     * Check if this is an SP shortage alert
     */
    public function isSpShortage(): bool
    {
        return $this->type === AlertType::SP_SHORTAGE;
    }

    /**
     * Check if this is an energy critical alert
     */
    public function isEnergyCritical(): bool
    {
        return $this->type === AlertType::ENERGY_CRITICAL;
    }

    /**
     * Check if this is a bond behind schedule alert
     */
    public function isBondBehindSchedule(): bool
    {
        return $this->type === AlertType::BOND_BEHIND_SCHEDULE;
    }

    /**
     * Check if this is a facility imbalance alert
     */
    public function isFacilityImbalance(): bool
    {
        return $this->type === AlertType::FACILITY_IMBALANCE;
    }

    /**
     * Check if this is a race unready alert
     */
    public function isRaceUnready(): bool
    {
        return $this->type === AlertType::RACE_UNREADY;
    }

    /**
     * Check if this is a team race unprepared alert
     */
    public function isTeamRaceUnprepared(): bool
    {
        return $this->type === AlertType::TEAM_RACE_UNPREPARED;
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
     * Get urgency level as a string
     */
    public function getUrgencyLevel(): string
    {
        return match (true) {
            $this->turnsUntilCritical === 0 => 'Immediate',
            $this->turnsUntilCritical <= 3 => 'Urgent',
            $this->turnsUntilCritical <= 5 => 'Soon',
            default => 'Upcoming',
        };
    }

    /**
     * Get a formatted urgency message
     */
    public function getUrgencyMessage(): string
    {
        if ($this->turnsUntilCritical === 0) {
            return 'Requires immediate attention';
        }

        if ($this->turnsUntilCritical === 1) {
            return 'Critical in 1 turn';
        }

        return "Critical in {$this->turnsUntilCritical} turns";
    }

    /**
     * Get action items as a formatted string
     */
    public function getActionItemsText(): string
    {
        if (empty($this->actionItems)) {
            return 'No specific actions recommended';
        }

        return '• '.implode("\n• ", $this->actionItems);
    }

    /**
     * Get a summary string for display
     */
    public function getSummary(): string
    {
        $urgency = $this->getUrgencyLevel();
        $type = $this->type->label();

        return "[{$urgency}] {$type}: {$this->message}";
    }

    /**
     * Create alert from an array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        // Validate and cast actionItems to array<string>
        $actionItems = [];
        if (isset($data['action_items']) && is_array($data['action_items'])) {
            foreach ($data['action_items'] as $item) {
                if (is_string($item) || is_numeric($item)) {
                    $actionItems[] = (string) $item;
                }
            }
        }

        $type = $data['type'] ?? 'stamina_crisis';
        $message = $data['message'] ?? '';
        $turnsUntilCritical = $data['turns_until_critical'] ?? 0;
        $detailedAnalysis = $data['detailed_analysis'] ?? null;
        $priority = $data['priority'] ?? 'critical';
        $storageMode = $data['storage_mode'] ?? 'account';

        return new self(
            type: AlertType::from(is_string($type) ? $type : 'stamina_crisis'),
            message: is_string($message) ? $message : '',
            actionItems: $actionItems,
            turnsUntilCritical: is_numeric($turnsUntilCritical) ? (int) $turnsUntilCritical : 0,
            detailedAnalysis: is_string($detailedAnalysis) ? $detailedAnalysis : null,
            priority: Priority::from(is_string($priority) ? $priority : 'critical'),
            storageMode: is_string($storageMode) ? $storageMode : 'account',
        );
    }

    /**
     * Convert alert to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'message' => $this->message,
            'action_items' => $this->actionItems,
            'turns_until_critical' => $this->turnsUntilCritical,
            'detailed_analysis' => $this->detailedAnalysis,
            'priority' => $this->priority->value,
            'storage_mode' => $this->storageMode,
        ];
    }

    /**
     * Create a stamina crisis alert
     *
     * @param  array<string>  $actionItems
     */
    public static function staminaCrisis(
        string $message,
        array $actionItems,
        int $turnsUntilCritical,
        ?string $detailedAnalysis = null,
        string $storageMode = 'account',
    ): self {
        return new self(
            type: AlertType::STAMINA_CRISIS,
            message: $message,
            actionItems: $actionItems,
            turnsUntilCritical: $turnsUntilCritical,
            detailedAnalysis: $detailedAnalysis,
            priority: Priority::CRITICAL,
            storageMode: $storageMode,
        );
    }

    /**
     * Create an SP shortage alert
     *
     * @param  array<string>  $actionItems
     */
    public static function spShortage(
        string $message,
        array $actionItems,
        int $turnsUntilCritical,
        ?string $detailedAnalysis = null,
        string $storageMode = 'account',
    ): self {
        return new self(
            type: AlertType::SP_SHORTAGE,
            message: $message,
            actionItems: $actionItems,
            turnsUntilCritical: $turnsUntilCritical,
            detailedAnalysis: $detailedAnalysis,
            priority: Priority::HIGH,
            storageMode: $storageMode,
        );
    }

    /**
     * Create an energy critical alert
     *
     * @param  array<string>  $actionItems
     */
    public static function energyCritical(
        string $message,
        array $actionItems,
        string $storageMode = 'account',
    ): self {
        return new self(
            type: AlertType::ENERGY_CRITICAL,
            message: $message,
            actionItems: $actionItems,
            turnsUntilCritical: 0,
            detailedAnalysis: null,
            priority: Priority::CRITICAL,
            storageMode: $storageMode,
        );
    }

    /**
     * Create a bond behind schedule alert
     *
     * @param  array<string>  $actionItems
     */
    public static function bondBehindSchedule(
        string $message,
        array $actionItems,
        int $turnsUntilCritical,
        ?string $detailedAnalysis = null,
        string $storageMode = 'account',
    ): self {
        return new self(
            type: AlertType::BOND_BEHIND_SCHEDULE,
            message: $message,
            actionItems: $actionItems,
            turnsUntilCritical: $turnsUntilCritical,
            detailedAnalysis: $detailedAnalysis,
            priority: Priority::HIGH,
            storageMode: $storageMode,
        );
    }

    /**
     * Create a facility imbalance alert
     *
     * @param  array<string>  $actionItems
     */
    public static function facilityImbalance(
        string $message,
        array $actionItems,
        string $storageMode = 'account',
    ): self {
        return new self(
            type: AlertType::FACILITY_IMBALANCE,
            message: $message,
            actionItems: $actionItems,
            turnsUntilCritical: 5,
            detailedAnalysis: null,
            priority: Priority::MEDIUM,
            storageMode: $storageMode,
        );
    }

    /**
     * Create a race unready alert
     *
     * @param  array<string>  $actionItems
     */
    public static function raceUnready(
        string $message,
        array $actionItems,
        int $turnsUntilCritical,
        ?string $detailedAnalysis = null,
        string $storageMode = 'account',
    ): self {
        return new self(
            type: AlertType::RACE_UNREADY,
            message: $message,
            actionItems: $actionItems,
            turnsUntilCritical: $turnsUntilCritical,
            detailedAnalysis: $detailedAnalysis,
            priority: Priority::HIGH,
            storageMode: $storageMode,
        );
    }
}
