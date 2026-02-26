<?php

namespace App\Services\Export;

use App\Models\Career;
use App\Models\Character;

class PDFExportService
{
    /**
     * Generate PDF data for a career export.
     *
     * @return array{content: string, filename: string, metadata: array{generated_at: string, version: string, career_id: int, character_name: string}}
     */
    public function exportCareer(Career $career): array
    {
        $career->loadMissing(['character', 'trainingSessions', 'races', 'skillAcquisitions']);

        $content = $this->buildPdfContent($career);
        $filename = $this->generateFilename($career);

        return [
            'content' => $content,
            'filename' => $filename,
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
                'version' => '1.0',
                'career_id' => $career->id,
                'character_name' => $career->character->name ?? 'Unknown',
            ],
        ];
    }

    /**
     * Generate PDF data for a character summary.
     *
     * @return array{content: string, filename: string, metadata: array{generated_at: string, version: string, character_id: int}}
     */
    public function exportCharacterSummary(Character $character): array
    {
        $character->loadMissing(['careers', 'aptitudes', 'skillAcquisitions']);

        $content = $this->buildCharacterSummaryContent($character);
        $filename = 'character_'.str_replace(' ', '_', $character->name ?? 'unknown').'_'.now()->format('Y-m-d').'.html';

        return [
            'content' => $content,
            'filename' => $filename,
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
                'version' => '1.0',
                'character_id' => $character->id,
            ],
        ];
    }

    /**
     * Build HTML content for PDF rendering.
     */
    private function buildPdfContent(Career $career): string
    {
        $character = $career->character;
        $stats = $this->formatStats($career);
        $trainingHistory = $this->formatTrainingHistory($career);
        $raceHistory = $this->formatRaceHistory($career);

        return $this->renderTemplate([
            'title' => 'Career Report: '.($career->career_name ?? "Career #{$career->id}"),
            'cover' => [
                'character_name' => $character->name ?? 'Unknown',
                'scenario_type' => $career->scenario_type ?? 'Unknown',
                'status' => $career->status ?? 'Unknown',
                'started_at' => $career->started_at,
                'completed_at' => $career->completed_at,
                'total_turns' => $career->current_turn ?? 0,
            ],
            'stats' => $stats,
            'training_history' => $trainingHistory,
            'race_history' => $raceHistory,
        ]);
    }

    /**
     * Build character summary content.
     */
    private function buildCharacterSummaryContent(Character $character): string
    {
        $careersData = $character->careers->map(fn (Career $career) => [
            'name' => $career->career_name ?? "Career #{$career->id}",
            'scenario' => $career->scenario_type ?? 'Unknown',
            'status' => $career->status ?? 'Unknown',
            'total_stats' => ($career->final_speed ?? 0) + ($career->final_stamina ?? 0)
                + ($career->final_power ?? 0) + ($career->final_guts ?? 0)
                + ($career->final_wit ?? 0),
        ])->toArray();

        return $this->renderTemplate([
            'title' => 'Character Summary: '.($character->name ?? 'Unknown'),
            'cover' => [
                'character_name' => $character->name ?? 'Unknown',
                'total_careers' => $character->careers->count(),
                'scenario_type' => $character->scenario_type ?? 'Unknown',
            ],
            'careers' => $careersData,
        ]);
    }

    /**
     * Format career stats for export.
     *
     * @return array<string, int>
     */
    private function formatStats(Career $career): array
    {
        return [
            'Speed' => $career->final_speed ?? 0,
            'Stamina' => $career->final_stamina ?? 0,
            'Power' => $career->final_power ?? 0,
            'Guts' => $career->final_guts ?? 0,
            'Wit' => $career->final_wit ?? 0,
            'SP' => $career->final_sp ?? 0,
        ];
    }

    /**
     * Format training history for export.
     *
     * @return array<int, array{turn: int, type: string|null, speed: int, stamina: int, power: int, guts: int, wit: int}>
     */
    private function formatTrainingHistory(Career $career): array
    {
        /** @var array<int, array{turn: int, type: string|null, speed: int, stamina: int, power: int, guts: int, wit: int}> $history */
        $history = $career->trainingSessions
            ->sortBy('turn_number')
            ->map(fn ($session) => [
                'turn' => $session->turn_number,
                'type' => $session->training_type,
                'speed' => $session->speed_gain ?? 0,
                'stamina' => $session->stamina_gain ?? 0,
                'power' => $session->power_gain ?? 0,
                'guts' => $session->guts_gain ?? 0,
                'wit' => $session->wit_gain ?? 0,
            ])
            ->values()
            ->toArray();

        return $history;
    }

    /**
     * Format race history for export.
     *
     * @return array<int, array{name: string|null, position: int|null, turn: int|null}>
     */
    private function formatRaceHistory(Career $career): array
    {
        /** @var array<int, array{name: string|null, position: int|null, turn: int|null}> $history */
        $history = $career->races
            ->map(fn ($race) => [
                'name' => $race->race_name ?? $race->name ?? null,
                'position' => $race->finishing_position ?? $race->position ?? null,
                'turn' => $race->turn_number ?? null,
            ])
            ->values()
            ->toArray();

        return $history;
    }

    /**
     * Render an HTML template for PDF conversion.
     *
     * @param  array<string, mixed>  $data
     */
    private function renderTemplate(array $data): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $html .= '<title>'.e($data['title'] ?? 'Career Report').'</title>';
        $html .= '<style>body{font-family:sans-serif;margin:20px;color:#333;}';
        $html .= 'h1{color:#1a56db;border-bottom:2px solid #1a56db;padding-bottom:8px;}';
        $html .= 'h2{color:#374151;margin-top:24px;}';
        $html .= 'table{width:100%;border-collapse:collapse;margin:12px 0;}';
        $html .= 'th,td{border:1px solid #d1d5db;padding:8px;text-align:left;}';
        $html .= 'th{background:#f3f4f6;font-weight:600;}';
        $html .= '.stat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:16px 0;}';
        $html .= '.stat-card{border:1px solid #d1d5db;border-radius:8px;padding:12px;text-align:center;}';
        $html .= '.stat-value{font-size:24px;font-weight:700;color:#1a56db;}';
        $html .= '.stat-label{font-size:12px;color:#6b7280;margin-top:4px;}</style></head><body>';

        $html .= '<h1>'.e($data['title'] ?? 'Report').'</h1>';

        if (isset($data['cover'])) {
            /** @var array<string, mixed> $cover */
            $cover = $data['cover'];
            $html .= '<div class="cover">';
            foreach ($cover as $label => $value) {
                $html .= '<p><strong>'.e(ucfirst(str_replace('_', ' ', (string) $label))).':</strong> '.e($value ?? 'N/A').'</p>';
            }
            $html .= '</div>';
        }

        if (isset($data['stats'])) {
            /** @var array<string, mixed> $stats */
            $stats = $data['stats'];
            $html .= '<h2>Final Stats</h2><div class="stat-grid">';
            foreach ($stats as $label => $value) {
                $html .= '<div class="stat-card"><div class="stat-value">'.e($value).'</div>';
                $html .= '<div class="stat-label">'.e($label).'</div></div>';
            }
            $html .= '</div>';
        }

        if (! empty($data['training_history'])) {
            /** @var array<int, array<string, mixed>> $trainingHistory */
            $trainingHistory = $data['training_history'];
            $html .= '<h2>Training History</h2><table>';
            $html .= '<tr><th>Turn</th><th>Type</th><th>SPD</th><th>STA</th><th>POW</th><th>GUT</th><th>WIT</th></tr>';
            foreach ($trainingHistory as $session) {
                $html .= '<tr>';
                $html .= '<td>'.e($session['turn']).'</td>';
                $html .= '<td>'.e($session['type'] ?? 'N/A').'</td>';
                $html .= '<td>'.e($session['speed']).'</td>';
                $html .= '<td>'.e($session['stamina']).'</td>';
                $html .= '<td>'.e($session['power']).'</td>';
                $html .= '<td>'.e($session['guts']).'</td>';
                $html .= '<td>'.e($session['wit']).'</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        if (! empty($data['race_history'])) {
            /** @var array<int, array<string, mixed>> $raceHistory */
            $raceHistory = $data['race_history'];
            $html .= '<h2>Race Results</h2><table>';
            $html .= '<tr><th>Race</th><th>Position</th><th>Turn</th></tr>';
            foreach ($raceHistory as $race) {
                $html .= '<tr>';
                $html .= '<td>'.e($race['name'] ?? 'N/A').'</td>';
                $html .= '<td>'.e($race['position'] ?? 'N/A').'</td>';
                $html .= '<td>'.e($race['turn'] ?? 'N/A').'</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        if (! empty($data['careers'])) {
            /** @var array<int, array<string, mixed>> $careersList */
            $careersList = $data['careers'];
            $html .= '<h2>Career History</h2><table>';
            $html .= '<tr><th>Name</th><th>Scenario</th><th>Status</th><th>Total Stats</th></tr>';
            foreach ($careersList as $career) {
                $html .= '<tr>';
                $html .= '<td>'.e($career['name']).'</td>';
                $html .= '<td>'.e($career['scenario']).'</td>';
                $html .= '<td>'.e($career['status']).'</td>';
                $html .= '<td>'.e(number_format((int) $career['total_stats'])).'</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        $html .= '<footer style="margin-top:40px;text-align:center;color:#9ca3af;font-size:11px;">';
        $html .= 'Generated by Uma Musume Career Planner on '.now()->format('Y-m-d H:i:s');
        $html .= '</footer></body></html>';

        return $html;
    }

    /**
     * Generate a filename for the PDF export.
     */
    private function generateFilename(Career $career): string
    {
        $name = str_replace(' ', '_', $career->career_name ?? 'career_'.$career->id);

        return $name.'_'.now()->format('Y-m-d').'.html';
    }
}
