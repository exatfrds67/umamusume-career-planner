<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Character;
use App\Services\SynergyBuildAnalyzerService;
use App\ValueObjects\SynergyReport;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Synergy Build Planner Livewire Component
 *
 * Displays a comprehensive synergy analysis for a character's build.
 * Shows an overall score ring, tier badge, per-layer breakdown bars,
 * critical issues, and actionable recommendations.
 *
 * @property-read SynergyReport|null $report
 */
class SynergyBuildPlanner extends Component
{
    public int $characterId;

    public bool $isCompact = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $reportData = null;

    public function mount(int $characterId, bool $isCompact = false): void
    {
        $this->characterId = $characterId;
        $this->isCompact = $isCompact;

        $this->loadReport();
    }

    public function refresh(): void
    {
        $character = Character::find($this->characterId);
        if ($character === null) {
            return;
        }

        $character->update([
            'synergy_snapshot' => null,
            'synergy_computed_at' => null,
        ]);

        $this->loadReport();
    }

    public function render(): View
    {
        return view('livewire.synergy-build-planner');
    }

    public function tierColorClass(string $tier): string
    {
        return match ($tier) {
            'S+' => 'text-amber-500',
            'S' => 'text-purple-500',
            'A' => 'text-blue-500',
            'B' => 'text-green-500',
            default => 'text-neutral-400',
        };
    }

    public function tierBadgeClass(string $tier): string
    {
        return match ($tier) {
            'S+' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
            'S' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
            'A' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
            'B' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
            default => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300',
        };
    }

    public function barColorClass(float $score): string
    {
        return match (true) {
            $score >= 80 => 'bg-green-500 dark:bg-green-400',
            $score >= 65 => 'bg-blue-500 dark:bg-blue-400',
            $score >= 50 => 'bg-amber-500 dark:bg-amber-400',
            default => 'bg-red-500 dark:bg-red-400',
        };
    }

    private function loadReport(): void
    {
        $character = Character::find($this->characterId);
        if ($character === null) {
            return;
        }

        /** @var SynergyBuildAnalyzerService $analyzer */
        $analyzer = app(SynergyBuildAnalyzerService::class);
        $report = $analyzer->analyzeBuild($character);

        $this->reportData = $report->toArray();
    }
}
