<?php

declare(strict_types=1);

namespace App\ValueObjects;

/**
 * Race Result Value Object
 *
 * Represents the actual outcome of a race.
 * Used to compare against predicted race strategies for accuracy tracking.
 *
 * @see \App\Services\PredictionAccuracyTracker
 */
final readonly class RaceResult
{
    /**
     * Create new Race Result
     *
     * @param  int  $raceId  Race identifier
     * @param  int  $placement  Final placement (1st, 2nd, 3rd, etc.)
     * @param  int  $totalCompetitors  Total number of competitors
     * @param  string  $runningStyle  Running style used (escape, lead, pace, chase)
     * @param  bool  $wasWin  Whether the race was won (1st place)
     * @param  bool  $wasPlaced  Whether the character placed (top 3)
     * @param  float|null  $finishTime  Race finish time in seconds (if available)
     * @param  int  $fanGain  Fan count gained from race
     * @param  array<string, mixed>  $additionalData  Any additional race data
     */
    public function __construct(
        public int $raceId,
        public int $placement,
        public int $totalCompetitors,
        public string $runningStyle,
        public bool $wasWin = false,
        public bool $wasPlaced = false,
        public ?float $finishTime = null,
        public int $fanGain = 0,
        public array $additionalData = [],
    ) {}

    /**
     * Check if race was won (1st place)
     */
    public function isWin(): bool
    {
        return $this->wasWin || $this->placement === 1;
    }

    /**
     * Check if character placed (top 3)
     */
    public function isPlaced(): bool
    {
        return $this->wasPlaced || $this->placement <= 3;
    }

    /**
     * Check if race was lost (not placed)
     */
    public function isLoss(): bool
    {
        return ! $this->isPlaced();
    }

    /**
     * Get placement as ordinal string (1st, 2nd, 3rd, etc.)
     */
    public function getPlacementOrdinal(): string
    {
        return match ($this->placement) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            default => $this->placement.'th',
        };
    }

    /**
     * Get result quality (win, placed, loss)
     */
    public function getResultQuality(): string
    {
        return match (true) {
            $this->isWin() => 'win',
            $this->isPlaced() => 'placed',
            default => 'loss',
        };
    }

    /**
     * Convert result to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'race_id' => $this->raceId,
            'placement' => $this->placement,
            'placement_ordinal' => $this->getPlacementOrdinal(),
            'total_competitors' => $this->totalCompetitors,
            'running_style' => $this->runningStyle,
            'was_win' => $this->isWin(),
            'was_placed' => $this->isPlaced(),
            'result_quality' => $this->getResultQuality(),
            'finish_time' => $this->finishTime,
            'fan_gain' => $this->fanGain,
            'additional_data' => $this->additionalData,
        ];
    }

    /**
     * Create from an array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $raceId = $data['race_id'] ?? 0;
        $placement = $data['placement'] ?? 1;
        $totalCompetitors = $data['total_competitors'] ?? 18;
        $runningStyle = $data['running_style'] ?? 'escape';
        $wasWin = $data['was_win'] ?? false;
        $wasPlaced = $data['was_placed'] ?? false;
        $finishTime = $data['finish_time'] ?? null;
        $fanGain = $data['fan_gain'] ?? 0;
        $additionalData = $data['additional_data'] ?? [];

        return new self(
            raceId: is_numeric($raceId) ? (int) $raceId : 0,
            placement: is_numeric($placement) ? (int) $placement : 1,
            totalCompetitors: is_numeric($totalCompetitors) ? (int) $totalCompetitors : 18,
            runningStyle: is_string($runningStyle) ? $runningStyle : 'escape',
            wasWin: is_bool($wasWin) ? $wasWin : false,
            wasPlaced: is_bool($wasPlaced) ? $wasPlaced : false,
            finishTime: is_float($finishTime) || is_int($finishTime) ? (float) $finishTime : null,
            fanGain: is_numeric($fanGain) ? (int) $fanGain : 0,
            additionalData: is_array($additionalData) ? $additionalData : [],
        );
    }
}
