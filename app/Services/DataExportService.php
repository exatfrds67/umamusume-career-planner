<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Career;
use App\Models\Character;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Data Export Service
 *
 * Handles comprehensive data export in multiple formats (JSON, CSV, PDF).
 * Supports selective data export with user-defined filters and export templates.
 *
 * Requirements: 23.2
 */
class DataExportService
{
    /**
     * Supported export types
     */
    public const EXPORT_TYPES = [
        'character' => 'Character Data',
        'career' => 'Career Data',
        'training_session' => 'Training Sessions',
        'skill' => 'Skills',
        'support_card' => 'Support Cards',
        'full_backup' => 'Full Data Backup',
    ];

    /**
     * Supported export formats
     */
    public const SUPPORTED_FORMATS = ['json', 'csv', 'pdf'];

    /**
     * Export templates for different use cases
     */
    public const EXPORT_TEMPLATES = [
        'career_summary' => [
            'name' => 'Career Summary',
            'description' => 'Export career overview with final stats and key metrics',
            'types' => ['career'],
            'fields' => ['career_name', 'scenario_type', 'status', 'final_stats', 'started_at', 'completed_at'],
        ],
        'training_log' => [
            'name' => 'Training Log',
            'description' => 'Detailed training session history with stat gains',
            'types' => ['training_session'],
            'fields' => ['turn_number', 'training_type', 'stat_gains', 'energy_cost', 'skill_hints_obtained'],
        ],
        'character_profile' => [
            'name' => 'Character Profile',
            'description' => 'Complete character data with aptitudes and factors',
            'types' => ['character'],
            'fields' => ['name', 'scenario_type', 'current_stats', 'aptitudes', 'factors', 'skills'],
        ],
        'skill_inventory' => [
            'name' => 'Skill Inventory',
            'description' => 'All acquired skills with SP costs and evolution status',
            'types' => ['skill'],
            'fields' => ['name', 'skill_type', 'rarity', 'base_sp_cost', 'is_evolution', 'acquired_at'],
        ],
        'support_deck' => [
            'name' => 'Support Deck',
            'description' => 'Support card configurations and friendship levels',
            'types' => ['support_card'],
            'fields' => ['name', 'rarity', 'specialization', 'limit_break_level', 'friendship_level'],
        ],
        'full_export' => [
            'name' => 'Full Data Export',
            'description' => 'Complete data backup including all records',
            'types' => ['full_backup'],
            'fields' => ['all'],
        ],
    ];

    /**
     * Export storage disk
     */
    private const EXPORT_DISK = 'local';

    /**
     * Export directory
     */
    private const EXPORT_DIR = 'exports';

    /**
     * Generate export data based on type and filters
     *
     * @param  string  $exportType  Type of data to export
     * @param  int  $userId  User ID for data ownership
     * @param  array  $filters  Optional filters for selective export
     * @return array{success: bool, data: array, count: int, errors: array}
     */
    public function generateExport(string $exportType, int $userId, array $filters = []): array
    {
        try {
            $data = match ($exportType) {
                'character' => $this->exportCharacters($userId, $filters),
                'career' => $this->exportCareers($userId, $filters),
                'training_session' => $this->exportTrainingSessions($userId, $filters),
                'skill' => $this->exportSkills($userId, $filters),
                'support_card' => $this->exportSupportCards($userId, $filters),
                'full_backup' => $this->exportFullBackup($userId, $filters),
                default => throw new \InvalidArgumentException("Unknown export type: {$exportType}"),
            };

            return [
                'success' => true,
                'data' => $data,
                'count' => \count($data),
                'errors' => [],
            ];
        } catch (\Exception $e) {
            Log::error('[DataExportService] Export generation failed', [
                'export_type' => $exportType,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => [],
                'count' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Export characters for a user
     */
    private function exportCharacters(int $userId, array $filters): array
    {
        $query = Character::where('user_id', $userId)
            ->with(['aptitudes', 'factors', 'skillAcquisitions.skill']);

        // Apply filters
        if (! empty($filters['scenario_type'])) {
            $query->where('scenario_type', $filters['scenario_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['character_ids'])) {
            $query->whereIn('id', $filters['character_ids']);
        }

        return $query->get()->map(function ($character) {
            return [
                'id' => $character->id,
                'uuid' => $character->uuid,
                'name' => $character->name,
                'scenario_type' => $character->scenario_type,
                'career_stage' => $character->career_stage,
                'current_turn' => $character->current_turn,
                'current_stats' => $character->current_stats,
                'energy_level' => $character->energy_level,
                'mood_status' => $character->mood_status,
                'conditions' => $character->conditions,
                'goals' => $character->goals,
                'growth_rates' => $character->growth_rates,
                'status' => $character->status,
                'aptitudes' => $character->aptitudes->map(fn ($apt) => [
                    'distance_type' => $apt->distance_type,
                    'surface_type' => $apt->surface_type,
                    'running_style' => $apt->running_style,
                    'grade' => $apt->grade,
                ])->toArray(),
                'factors' => $character->factors->map(fn ($factor) => [
                    'factor_type' => $factor->factor_type,
                    'factor_level' => $factor->factor_level,
                    'stat_bonus' => $factor->stat_bonus,
                    'source_parent' => $factor->source_parent,
                ])->toArray(),
                'skills' => $character->skillAcquisitions->map(fn ($acq) => [
                    'skill_name' => $acq->skill?->name,
                    'skill_type' => $acq->skill?->skill_type,
                    'sp_cost' => $acq->final_sp_cost,
                    'turn_acquired' => $acq->turn_acquired,
                ])->toArray(),
                'created_at' => $character->created_at?->toIso8601String(),
                'updated_at' => $character->updated_at?->toIso8601String(),
            ];
        })->toArray();
    }

    /**
     * Export careers for a user
     */
    private function exportCareers(int $userId, array $filters): array
    {
        $query = Career::where('user_id', $userId)
            ->with(['character', 'trainingSessions', 'races']);

        // Apply filters
        if (! empty($filters['scenario_type'])) {
            $query->where('scenario_type', $filters['scenario_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('started_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('started_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['career_ids'])) {
            $query->whereIn('id', $filters['career_ids']);
        }

        return $query->get()->map(function ($career) {
            return [
                'id' => $career->id,
                'career_name' => $career->career_name,
                'character_name' => $career->character?->name,
                'scenario_type' => $career->scenario_type,
                'status' => $career->status,
                'current_turn' => $career->current_turn,
                'current_phase' => $career->current_phase,
                'final_stats' => [
                    'speed' => $career->final_speed,
                    'stamina' => $career->final_stamina,
                    'power' => $career->final_power,
                    'guts' => $career->final_guts,
                    'wit' => $career->final_wit,
                    'sp' => $career->final_sp,
                ],
                'support_deck' => $career->support_deck,
                'inheritance_factors' => $career->inheritance_factors,
                'strategic_goals' => $career->strategic_goals,
                'lessons_learned' => $career->lessons_learned,
                'performance_analysis' => $career->performance_analysis,
                'training_sessions_count' => $career->trainingSessions->count(),
                'races_count' => $career->races->count(),
                'started_at' => $career->started_at?->toIso8601String(),
                'completed_at' => $career->completed_at?->toIso8601String(),
            ];
        })->toArray();
    }

    /**
     * Export training sessions for a user
     */
    private function exportTrainingSessions(int $userId, array $filters): array
    {
        $query = TrainingSession::whereHas('career', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['career', 'character']);

        // Apply filters
        if (! empty($filters['career_id'])) {
            $query->where('career_id', $filters['career_id']);
        }

        if (! empty($filters['training_type'])) {
            $query->where('training_type', $filters['training_type']);
        }

        if (! empty($filters['turn_from'])) {
            $query->where('turn_number', '>=', $filters['turn_from']);
        }

        if (! empty($filters['turn_to'])) {
            $query->where('turn_number', '<=', $filters['turn_to']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('turn_number')->get()->map(function ($session) {
            return [
                'id' => $session->id,
                'career_name' => $session->career?->career_name,
                'character_name' => $session->character?->name,
                'turn_number' => $session->turn_number,
                'career_phase' => $session->career_phase,
                'training_type' => $session->training_type,
                'stat_gains' => [
                    'speed' => $session->speed_gain,
                    'stamina' => $session->stamina_gain,
                    'power' => $session->power_gain,
                    'guts' => $session->guts_gain,
                    'wit' => $session->wit_gain,
                    'sp' => $session->sp_gain,
                ],
                'energy_cost' => $session->energy_cost,
                'energy_before' => $session->energy_before,
                'energy_after' => $session->energy_after,
                'skill_hints_obtained' => $session->skill_hints_obtained,
                'events_triggered' => $session->events_triggered,
                'training_bonuses' => $session->training_bonuses,
                'friendship_training' => $session->friendship_training,
                'training_failed' => $session->training_failed,
                'failure_reason' => $session->failure_reason,
                'training_efficiency' => $session->training_efficiency,
                'total_stat_points_gained' => $session->total_stat_points_gained,
                'created_at' => $session->created_at?->toIso8601String(),
            ];
        })->toArray();
    }

    /**
     * Export skills for a user
     */
    private function exportSkills(int $userId, array $filters): array
    {
        // Get all skills (reference data)
        $query = Skill::query();

        // Apply filters
        if (! empty($filters['skill_type'])) {
            $query->where('skill_type', $filters['skill_type']);
        }

        if (! empty($filters['rarity'])) {
            $query->where('rarity', $filters['rarity']);
        }

        if (! empty($filters['is_evolution'])) {
            $query->where('is_evolution', $filters['is_evolution']);
        }

        return $query->get()->map(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'skill_type' => $skill->skill_type,
                'rarity' => $skill->rarity,
                'base_sp_cost' => $skill->base_sp_cost,
                'description' => $skill->description,
                'effect' => $skill->effect,
                'is_evolution' => $skill->is_evolution,
                'evolves_from' => $skill->evolves_from,
                'evolves_to' => $skill->evolves_to,
                'meta_tier' => $skill->meta_tier,
            ];
        })->toArray();
    }

    /**
     * Export support cards for a user
     */
    private function exportSupportCards(int $userId, array $filters): array
    {
        // Get support cards used by user's characters
        $query = SupportCard::query();

        // Apply filters
        if (! empty($filters['rarity'])) {
            $query->where('rarity', $filters['rarity']);
        }

        if (! empty($filters['specialization'])) {
            $query->where('card_type', $filters['specialization']);
        }

        if (! empty($filters['meta_tier'])) {
            $query->where('meta_tier', $filters['meta_tier']);
        }

        return $query->get()->map(function ($card) {
            return [
                'id' => $card->id,
                'name' => $card->name,
                'rarity' => $card->rarity,
                'specialization' => $card->card_type,
                'character_name' => $card->character_name,
                'limit_break_level' => $card->max_limit_break,
                'training_bonuses' => [
                    'speed' => $card->speed_bonus,
                    'stamina' => $card->stamina_bonus,
                    'power' => $card->power_bonus,
                    'guts' => $card->guts_bonus,
                    'wit' => $card->wit_bonus,
                ],
                'skill_hints' => $card->skill_hints_provided,
                'meta_tier' => $card->meta_tier,
            ];
        })->toArray();
    }

    /**
     * Export full backup for a user
     */
    private function exportFullBackup(int $userId, array $filters): array
    {
        return [
            'export_info' => [
                'version' => '1.0',
                'exported_at' => now()->toIso8601String(),
                'user_id' => $userId,
            ],
            'characters' => $this->exportCharacters($userId, $filters),
            'careers' => $this->exportCareers($userId, $filters),
            'training_sessions' => $this->exportTrainingSessions($userId, $filters),
            'skills' => $this->exportSkills($userId, $filters),
            'support_cards' => $this->exportSupportCards($userId, $filters),
        ];
    }

    /**
     * Convert data to JSON format
     *
     * @param  array  $data  Data to convert
     * @param  bool  $pretty  Whether to pretty print
     * @return string JSON string
     */
    public function toJson(array $data, bool $pretty = true): string
    {
        $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE;
        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($data, $flags);
    }

    /**
     * Convert data to CSV format
     *
     * @param  array  $data  Data to convert
     * @param  string  $exportType  Type of export for header mapping
     * @return string CSV string
     */
    public function toCsv(array $data, string $exportType): string
    {
        if (empty($data)) {
            return '';
        }

        // For full backup, we need to handle nested structure
        if ($exportType === 'full_backup') {
            return $this->fullBackupToCsv($data);
        }

        $output = fopen('php://temp', 'r+');
        if ($output === false) {
            return '';
        }

        // Flatten nested arrays for CSV
        $flattenedData = $this->flattenForCsv($data);

        if (empty($flattenedData)) {
            fclose($output);

            return '';
        }

        // Write headers
        $headers = array_keys($flattenedData[0]);
        fputcsv($output, $headers);

        // Write data rows
        foreach ($flattenedData as $row) {
            fputcsv($output, array_values($row));
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Flatten nested arrays for CSV export
     */
    private function flattenForCsv(array $data): array
    {
        return array_map(function ($item) {
            $flattened = [];
            foreach ($item as $key => $value) {
                if (\is_array($value)) {
                    // For nested arrays, convert to JSON string
                    $flattened[$key] = json_encode($value);
                } else {
                    $flattened[$key] = $value;
                }
            }

            return $flattened;
        }, $data);
    }

    /**
     * Convert full backup to CSV (multiple sheets as separate sections)
     */
    private function fullBackupToCsv(array $data): string
    {
        $output = '';

        foreach ($data as $section => $sectionData) {
            if ($section === 'export_info') {
                $output .= "# Export Information\n";
                foreach ($sectionData as $key => $value) {
                    $output .= "{$key},{$value}\n";
                }
                $output .= "\n";

                continue;
            }

            if (! \is_array($sectionData) || empty($sectionData)) {
                continue;
            }

            $output .= "# {$section}\n";
            $flattenedData = $this->flattenForCsv($sectionData);

            if (! empty($flattenedData)) {
                $headers = array_keys($flattenedData[0]);
                $output .= implode(',', $headers)."\n";

                foreach ($flattenedData as $row) {
                    $escapedValues = array_map(function ($value) {
                        if (\is_string($value) && (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n"))) {
                            return '"'.str_replace('"', '""', $value).'"';
                        }

                        return $value;
                    }, array_values($row));
                    $output .= implode(',', $escapedValues)."\n";
                }
            }
            $output .= "\n";
        }

        return $output;
    }

    /**
     * Generate PDF export
     *
     * @param  array  $data  Data to export
     * @param  string  $exportType  Type of export
     * @param  array  $options  PDF options
     * @return string HTML content for PDF (to be rendered by browser or PDF library)
     */
    public function toPdf(array $data, string $exportType, array $options = []): string
    {
        $title = self::EXPORT_TYPES[$exportType] ?? 'Data Export';
        $exportDate = now()->format('Y-m-d H:i:s');

        $html = $this->generatePdfHtml($data, $exportType, $title, $exportDate, $options);

        return $html;
    }

    /**
     * Generate HTML for PDF export
     */
    private function generatePdfHtml(array $data, string $exportType, string $title, string $exportDate, array $options): string
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - Umamusume Career Planner</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; line-height: 1.5; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; }
        .header h1 { color: #1e40af; font-size: 24px; margin-bottom: 5px; }
        .header .subtitle { color: #6b7280; font-size: 14px; }
        .section { margin-bottom: 30px; page-break-inside: avoid; }
        .section-title { background: #3b82f6; color: white; padding: 10px 15px; font-size: 16px; font-weight: bold; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px 12px; text-align: left; }
        th { background: #f3f4f6; font-weight: 600; color: #374151; }
        tr:nth-child(even) { background: #f9fafb; }
        .stat-box { display: inline-block; padding: 5px 10px; margin: 2px; background: #dbeafe; border-radius: 4px; }
        .stat-label { font-weight: 600; color: #1e40af; }
        .stat-value { color: #1f2937; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 10px; }
        @media print { .section { page-break-inside: avoid; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏇 {$title}</h1>
        <div class="subtitle">Umamusume Career Planner - Exported on {$exportDate}</div>
    </div>
HTML;

        // Generate content based on export type
        $html .= $this->generatePdfContent($data, $exportType);

        $html .= <<<HTML
    <div class="footer">
        <p>Generated by Umamusume Career Planner | Export ID: {$this->generateExportId()}</p>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Generate PDF content based on export type
     */
    private function generatePdfContent(array $data, string $exportType): string
    {
        if ($exportType === 'full_backup') {
            return $this->generateFullBackupPdfContent($data);
        }

        if (empty($data)) {
            return '<div class="section"><p>No data available for export.</p></div>';
        }

        $html = '<div class="section">';
        $html .= '<div class="section-title">'.htmlspecialchars((string) (self::EXPORT_TYPES[$exportType] ?? 'Data')).'</div>';
        $html .= '<table><thead><tr>';

        // Generate headers from first item
        $firstItem = $data[0];
        foreach (array_keys($firstItem) as $key) {
            $label = ucwords(str_replace('_', ' ', (string) $key));
            $html .= "<th>{$label}</th>";
        }
        $html .= '</tr></thead><tbody>';

        // Generate rows
        foreach ($data as $item) {
            $html .= '<tr>';
            foreach ($item as $value) {
                $displayValue = $this->formatPdfValue($value);
                $html .= "<td>{$displayValue}</td>";
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';

        return $html;
    }

    /**
     * Generate full backup PDF content
     */
    private function generateFullBackupPdfContent(array $data): string
    {
        $html = '';

        // Export info section
        if (isset($data['export_info'])) {
            $html .= '<div class="section">';
            $html .= '<div class="section-title">Export Information</div>';
            $html .= '<table>';
            foreach ($data['export_info'] as $key => $value) {
                $label = ucwords(str_replace('_', ' ', (string) $key));
                $html .= "<tr><th>{$label}</th><td>{$value}</td></tr>";
            }
            $html .= '</table></div>';
        }

        // Other sections
        $sections = ['characters', 'careers', 'training_sessions', 'skills', 'support_cards'];
        foreach ($sections as $section) {
            if (isset($data[$section]) && ! empty($data[$section])) {
                $sectionTitle = ucwords(str_replace('_', ' ', $section));
                $html .= '<div class="section">';
                $html .= "<div class=\"section-title\">{$sectionTitle} (".count($data[$section]).' records)</div>';

                // Summary table for each section
                $html .= '<table><thead><tr>';
                $firstItem = $data[$section][0];
                $displayKeys = array_slice(array_keys($firstItem), 0, 6); // Limit columns for readability
                foreach ($displayKeys as $key) {
                    $label = ucwords(str_replace('_', ' ', (string) $key));
                    $html .= "<th>{$label}</th>";
                }
                $html .= '</tr></thead><tbody>';

                foreach (array_slice($data[$section], 0, 10) as $item) { // Limit rows for PDF
                    $html .= '<tr>';
                    foreach ($displayKeys as $key) {
                        $value = $item[$key] ?? '';
                        $displayValue = $this->formatPdfValue($value);
                        $html .= "<td>{$displayValue}</td>";
                    }
                    $html .= '</tr>';
                }

                if (count($data[$section]) > 10) {
                    $remaining = count($data[$section]) - 10;
                    $html .= '<tr><td colspan="'.count($displayKeys)."\" style=\"text-align: center; font-style: italic;\">... and {$remaining} more records</td></tr>";
                }

                $html .= '</tbody></table></div>';
            }
        }

        return $html;
    }

    /**
     * Format value for PDF display
     */
    private function formatPdfValue(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        if (\is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (\is_array($value)) {
            if (empty($value)) {
                return '-';
            }
            // For stat arrays, format nicely
            if (isset($value['speed']) || isset($value['stamina'])) {
                $parts = [];
                foreach ($value as $stat => $val) {
                    if ($val !== null && $val !== 0) {
                        $parts[] = ucfirst($stat).': '.$val;
                    }
                }

                return implode(', ', $parts) ?: '-';
            }

            return htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE) ?: '[]');
        }

        return htmlspecialchars((string) $value);
    }

    /**
     * Generate unique export ID
     */
    private function generateExportId(): string
    {
        return strtoupper(Str::random(8));
    }

    /**
     * Save export to file and return file info
     *
     * @param  string  $content  Export content
     * @param  string  $format  Export format
     * @param  string  $exportType  Type of export
     * @param  int  $userId  User ID
     * @return array{success: bool, file_path: string, file_name: string, file_size: int, download_url: string}
     */
    public function saveExport(string $content, string $format, string $exportType, int $userId): array
    {
        try {
            $timestamp = now()->format('Y-m-d_His');
            $fileName = "export_{$exportType}_{$timestamp}.{$format}";
            $filePath = self::EXPORT_DIR."/{$userId}/{$fileName}";

            // Ensure directory exists
            Storage::disk(self::EXPORT_DISK)->makeDirectory(self::EXPORT_DIR."/{$userId}");

            // Save file
            Storage::disk(self::EXPORT_DISK)->put($filePath, $content);

            // Log export
            $this->logExport($userId, $exportType, $format, $filePath);

            return [
                'success' => true,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => \strlen($content),
                'download_url' => route('export.download', ['path' => base64_encode($filePath)]),
            ];
        } catch (\Exception $e) {
            Log::error('[DataExportService] Save export failed', [
                'user_id' => $userId,
                'export_type' => $exportType,
                'format' => $format,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'file_path' => '',
                'file_name' => '',
                'file_size' => 0,
                'download_url' => '',
            ];
        }
    }

    /**
     * Log export operation
     */
    private function logExport(int $userId, string $exportType, string $format, string $filePath): void
    {
        DB::table('ucp_system_logs')->insert([
            'log_category' => 'export',
            'log_level' => 'info',
            'log_source' => 'web',
            'event_type' => 'data_export',
            'message' => "Data export completed: {$exportType} in {$format} format",
            'context_data' => json_encode([
                'user_id' => $userId,
                'export_type' => $exportType,
                'format' => $format,
                'file_path' => $filePath,
            ]),
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get export history for a user
     *
     * @param  int  $userId  User ID
     * @param  int  $limit  Number of records to return
     * @return array Export history records
     */
    public function getExportHistory(int $userId, int $limit = 20): array
    {
        return DB::table('ucp_system_logs')
            ->where('user_id', $userId)
            ->where('log_category', 'export')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                $context = json_decode($log->context_data ?? '{}', true);

                return [
                    'id' => $log->id,
                    'export_type' => $context['export_type'] ?? 'unknown',
                    'format' => $context['format'] ?? 'unknown',
                    'file_path' => $context['file_path'] ?? '',
                    'created_at' => $log->created_at,
                ];
            })
            ->toArray();
    }

    /**
     * Get available export templates
     *
     * @return array Export templates
     */
    public function getTemplates(): array
    {
        return self::EXPORT_TEMPLATES;
    }

    /**
     * Get field mapping for export type
     *
     * @param  string  $exportType  Export type
     * @return array Field mapping
     */
    public function getFieldMapping(string $exportType): array
    {
        return match ($exportType) {
            'character' => [
                'id' => ['label' => 'ID', 'type' => 'integer'],
                'uuid' => ['label' => 'UUID', 'type' => 'string'],
                'name' => ['label' => 'Character Name', 'type' => 'string'],
                'scenario_type' => ['label' => 'Scenario Type', 'type' => 'enum', 'options' => ['ura_finale', 'unity_cup']],
                'career_stage' => ['label' => 'Career Stage', 'type' => 'string'],
                'current_turn' => ['label' => 'Current Turn', 'type' => 'integer'],
                'current_stats' => ['label' => 'Current Stats', 'type' => 'object'],
                'energy_level' => ['label' => 'Energy Level', 'type' => 'string'],
                'mood_status' => ['label' => 'Mood Status', 'type' => 'string'],
                'status' => ['label' => 'Status', 'type' => 'string'],
                'aptitudes' => ['label' => 'Aptitudes', 'type' => 'array'],
                'factors' => ['label' => 'Factors', 'type' => 'array'],
                'skills' => ['label' => 'Skills', 'type' => 'array'],
            ],
            'career' => [
                'id' => ['label' => 'ID', 'type' => 'integer'],
                'career_name' => ['label' => 'Career Name', 'type' => 'string'],
                'character_name' => ['label' => 'Character Name', 'type' => 'string'],
                'scenario_type' => ['label' => 'Scenario Type', 'type' => 'enum'],
                'status' => ['label' => 'Status', 'type' => 'string'],
                'current_turn' => ['label' => 'Current Turn', 'type' => 'integer'],
                'final_stats' => ['label' => 'Final Stats', 'type' => 'object'],
                'started_at' => ['label' => 'Started At', 'type' => 'datetime'],
                'completed_at' => ['label' => 'Completed At', 'type' => 'datetime'],
            ],
            'training_session' => [
                'id' => ['label' => 'ID', 'type' => 'integer'],
                'turn_number' => ['label' => 'Turn Number', 'type' => 'integer'],
                'training_type' => ['label' => 'Training Type', 'type' => 'string'],
                'stat_gains' => ['label' => 'Stat Gains', 'type' => 'object'],
                'energy_cost' => ['label' => 'Energy Cost', 'type' => 'integer'],
                'skill_hints_obtained' => ['label' => 'Skill Hints', 'type' => 'array'],
            ],
            'skill' => [
                'id' => ['label' => 'ID', 'type' => 'integer'],
                'name' => ['label' => 'Skill Name', 'type' => 'string'],
                'skill_type' => ['label' => 'Skill Type', 'type' => 'string'],
                'rarity' => ['label' => 'Rarity', 'type' => 'string'],
                'base_sp_cost' => ['label' => 'SP Cost', 'type' => 'integer'],
                'is_evolution' => ['label' => 'Is Evolution', 'type' => 'boolean'],
            ],
            'support_card' => [
                'id' => ['label' => 'ID', 'type' => 'integer'],
                'name' => ['label' => 'Card Name', 'type' => 'string'],
                'rarity' => ['label' => 'Rarity', 'type' => 'string'],
                'specialization' => ['label' => 'Specialization', 'type' => 'string'],
                'limit_break_level' => ['label' => 'Limit Break', 'type' => 'integer'],
                'meta_tier' => ['label' => 'Meta Tier', 'type' => 'string'],
            ],
            default => [],
        };
    }

    /**
     * Download export file
     *
     * @param  string  $encodedPath  Base64 encoded file path
     * @param  int  $userId  User ID for verification
     * @return array{success: bool, content: string, file_name: string, mime_type: string}
     */
    public function downloadExport(string $encodedPath, int $userId): array
    {
        try {
            $filePath = base64_decode($encodedPath, true);
            if ($filePath === false) {
                return [
                    'success' => false,
                    'content' => '',
                    'file_name' => '',
                    'mime_type' => '',
                    'error' => 'Invalid export path',
                ];
            }

            // Verify user owns this export
            if (! str_contains($filePath, "/{$userId}/")) {
                return [
                    'success' => false,
                    'content' => '',
                    'file_name' => '',
                    'mime_type' => '',
                    'error' => 'Unauthorized access to export file',
                ];
            }

            if (! Storage::disk(self::EXPORT_DISK)->exists($filePath)) {
                return [
                    'success' => false,
                    'content' => '',
                    'file_name' => '',
                    'mime_type' => '',
                    'error' => 'Export file not found',
                ];
            }

            $content = Storage::disk(self::EXPORT_DISK)->get($filePath);
            $fileName = basename($filePath);
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);

            $mimeType = match ($extension) {
                'json' => 'application/json',
                'csv' => 'text/csv',
                'pdf' => 'text/html', // HTML for PDF rendering
                default => 'application/octet-stream',
            };

            return [
                'success' => true,
                'content' => $content ?? '',
                'file_name' => $fileName,
                'mime_type' => $mimeType,
            ];
        } catch (\Exception $e) {
            Log::error('[DataExportService] Download export failed', [
                'encoded_path' => $encodedPath,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'content' => '',
                'file_name' => '',
                'mime_type' => '',
                'error' => 'Failed to download export file',
            ];
        }
    }

    /**
     * Schedule an automated export
     *
     * @param  int  $userId  User ID
     * @param  array  $scheduleConfig  Schedule configuration
     * @return array{success: bool, schedule_id: string, message: string}
     */
    public function scheduleExport(int $userId, array $scheduleConfig): array
    {
        try {
            $scheduleId = Str::uuid()->toString();

            // Store schedule in database
            DB::table('ucp_user_preferences')->insert([
                'user_id' => $userId,
                'preference_category' => 'export',
                'preference_key' => "export_schedule_{$scheduleId}",
                'preference_value' => json_encode($scheduleConfig),
                'value_type' => 'object',
                'scope' => 'global',
                'last_modified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]);

            Log::info('[DataExportService] Export scheduled', [
                'user_id' => $userId,
                'schedule_id' => $scheduleId,
                'config' => $scheduleConfig,
            ]);

            return [
                'success' => true,
                'schedule_id' => $scheduleId,
                'message' => 'Export scheduled successfully',
            ];
        } catch (\Exception $e) {
            Log::error('[DataExportService] Schedule export failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'schedule_id' => '',
                'message' => 'Failed to schedule export: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get scheduled exports for a user
     *
     * @param  int  $userId  User ID
     * @return array Scheduled exports
     */
    public function getScheduledExports(int $userId): array
    {
        return DB::table('ucp_user_preferences')
            ->where('user_id', $userId)
            ->where('preference_category', 'export')
            ->where('preference_key', 'like', 'export_schedule_%')
            ->get()
            ->map(function ($pref) {
                $scheduleId = str_replace('export_schedule_', '', $pref->preference_key);
                $config = json_decode($pref->preference_value, true);

                return [
                    'schedule_id' => $scheduleId,
                    'config' => $config,
                    'created_at' => $pref->created_at,
                    'updated_at' => $pref->updated_at,
                ];
            })
            ->toArray();
    }

    /**
     * Delete a scheduled export
     *
     * @param  int  $userId  User ID
     * @param  string  $scheduleId  Schedule ID
     * @return bool Success status
     */
    public function deleteScheduledExport(int $userId, string $scheduleId): bool
    {
        return DB::table('ucp_user_preferences')
            ->where('user_id', $userId)
            ->where('preference_key', "export_schedule_{$scheduleId}")
            ->delete() > 0;
    }
}
