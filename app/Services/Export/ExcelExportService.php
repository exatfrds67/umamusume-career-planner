<?php

namespace App\Services\Export;

use App\Models\Career;

class ExcelExportService
{
    /**
     * Export career data in a structured format suitable for spreadsheet rendering.
     *
     * @return array{sheets: array<string, array{headers: array<int, string>, rows: array<int, array<int, mixed>>}>, filename: string, metadata: array{generated_at: string, version: string, career_id: int}}
     */
    public function exportCareer(Career $career): array
    {
        $career->loadMissing(['character', 'trainingSessions', 'races', 'skillAcquisitions']);

        return [
            'sheets' => [
                'Overview' => $this->buildOverviewSheet($career),
                'Stats' => $this->buildStatsSheet($career),
                'Training' => $this->buildTrainingSheet($career),
                'Races' => $this->buildRacesSheet($career),
                'Skills' => $this->buildSkillsSheet($career),
            ],
            'filename' => $this->generateFilename($career),
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
                'version' => '1.0',
                'career_id' => $career->id,
            ],
        ];
    }

    /**
     * Export career data as CSV content.
     *
     * @return array{content: string, filename: string}
     */
    public function exportAsCsv(Career $career): array
    {
        $career->loadMissing(['character', 'trainingSessions']);

        $lines = [];
        $lines[] = implode(',', ['Turn', 'Training Type', 'Speed Gain', 'Stamina Gain', 'Power Gain', 'Guts Gain', 'Wit Gain', 'SP Gain']);

        foreach ($career->trainingSessions->sortBy('turn_number') as $session) {
            $lines[] = implode(',', [
                $session->turn_number,
                '"'.($session->training_type ?? '').'"',
                $session->speed_gain ?? 0,
                $session->stamina_gain ?? 0,
                $session->power_gain ?? 0,
                $session->guts_gain ?? 0,
                $session->wit_gain ?? 0,
                $session->sp_gain ?? 0,
            ]);
        }

        return [
            'content' => implode("\n", $lines),
            'filename' => $this->generateFilename($career, 'csv'),
        ];
    }

    /**
     * Build overview sheet data.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function buildOverviewSheet(Career $career): array
    {
        $character = $career->character;

        return [
            'headers' => ['Field', 'Value'],
            'rows' => [
                ['Career Name', $career->career_name ?? "Career #{$career->id}"],
                ['Character', $character?->name ?? 'Unknown'],
                ['Scenario', $career->scenario_type ?? 'Unknown'],
                ['Status', $career->status ?? 'Unknown'],
                ['Total Turns', $career->current_turn ?? 0],
                ['Started', $career->started_at ?? 'N/A'],
                ['Completed', $career->completed_at ?? 'N/A'],
                ['Total Stats', ($career->final_speed ?? 0) + ($career->final_stamina ?? 0) + ($career->final_power ?? 0) + ($career->final_guts ?? 0) + ($career->final_wit ?? 0)],
            ],
        ];
    }

    /**
     * Build stats sheet data.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function buildStatsSheet(Career $career): array
    {
        $total = ($career->final_speed ?? 0) + ($career->final_stamina ?? 0) + ($career->final_power ?? 0) + ($career->final_guts ?? 0) + ($career->final_wit ?? 0);

        return [
            'headers' => ['Stat', 'Value', 'Percentage'],
            'rows' => [
                ['Speed', $career->final_speed ?? 0, $total > 0 ? round(($career->final_speed ?? 0) / $total * 100, 1).'%' : '0%'],
                ['Stamina', $career->final_stamina ?? 0, $total > 0 ? round(($career->final_stamina ?? 0) / $total * 100, 1).'%' : '0%'],
                ['Power', $career->final_power ?? 0, $total > 0 ? round(($career->final_power ?? 0) / $total * 100, 1).'%' : '0%'],
                ['Guts', $career->final_guts ?? 0, $total > 0 ? round(($career->final_guts ?? 0) / $total * 100, 1).'%' : '0%'],
                ['Wit', $career->final_wit ?? 0, $total > 0 ? round(($career->final_wit ?? 0) / $total * 100, 1).'%' : '0%'],
                ['SP', $career->final_sp ?? 0, 'N/A'],
                ['Total', $total, '100%'],
            ],
        ];
    }

    /**
     * Build training session sheet data.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function buildTrainingSheet(Career $career): array
    {
        $rows = $career->trainingSessions
            ->sortBy('turn_number')
            ->map(fn ($session) => [
                $session->turn_number,
                $session->career_phase ?? 'N/A',
                $session->training_type ?? 'N/A',
                $session->speed_gain ?? 0,
                $session->stamina_gain ?? 0,
                $session->power_gain ?? 0,
                $session->guts_gain ?? 0,
                $session->wit_gain ?? 0,
                $session->sp_gain ?? 0,
                round($session->training_efficiency ?? 0, 2),
            ])
            ->values()
            ->toArray();

        return [
            'headers' => ['Turn', 'Phase', 'Type', 'SPD', 'STA', 'POW', 'GUT', 'WIT', 'SP', 'Efficiency'],
            'rows' => $rows,
        ];
    }

    /**
     * Build races sheet data.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function buildRacesSheet(Career $career): array
    {
        $rows = $career->races
            ->map(fn ($race) => [
                $race->turn_number ?? 'N/A',
                $race->race_name ?? $race->name ?? 'N/A',
                $race->finishing_position ?? $race->position ?? 'N/A',
                $race->race_grade ?? 'N/A',
            ])
            ->values()
            ->toArray();

        return [
            'headers' => ['Turn', 'Race Name', 'Position', 'Grade'],
            'rows' => $rows,
        ];
    }

    /**
     * Build skills sheet data.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function buildSkillsSheet(Career $career): array
    {
        $rows = $career->skillAcquisitions
            ->map(fn ($skill) => [
                $skill->skill_name ?? $skill->name ?? 'N/A',
                $skill->sp_cost ?? 'N/A',
                $skill->acquired_at_turn ?? 'N/A',
                $skill->status ?? 'N/A',
            ])
            ->values()
            ->toArray();

        return [
            'headers' => ['Skill Name', 'SP Cost', 'Acquired Turn', 'Status'],
            'rows' => $rows,
        ];
    }

    /**
     * Generate export filename.
     */
    private function generateFilename(Career $career, string $extension = 'xlsx'): string
    {
        $name = str_replace(' ', '_', $career->career_name ?? 'career_'.$career->id);

        return $name.'_'.now()->format('Y-m-d').'.'.$extension;
    }
}
