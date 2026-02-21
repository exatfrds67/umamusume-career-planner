<?php

namespace App\Services;

use App\Models\Career;
use App\Models\Race;
use App\Models\RunSnapshot;
use App\Models\SkillAcquisition;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;

class SnapshotService
{
    /**
     * Create a snapshot of the current career state.
     */
    public function createSnapshot(
        Career $career,
        string $triggerType = 'manual',
        ?string $description = null,
    ): RunSnapshot {
        $snapshotData = $this->captureCareerState($career);
        $checksum = $this->calculateChecksum($snapshotData);

        return RunSnapshot::create([
            'career_id' => $career->id,
            'turn_number' => $career->current_turn ?? 0,
            'trigger_type' => $triggerType,
            'description' => $description,
            'snapshot_data' => $snapshotData,
            'checksum' => $checksum,
        ]);
    }

    /**
     * Capture the full state of a career at the current moment.
     *
     * @return array<string, mixed>
     */
    public function captureCareerState(Career $career): array
    {
        $career->loadMissing(['trainingSessions', 'races', 'skillAcquisitions', 'character']);

        return [
            'career' => [
                'current_turn' => $career->current_turn,
                'current_phase' => $career->current_phase,
                'status' => $career->status,
                'career_name' => $career->career_name,
                'scenario_type' => $career->scenario_type,
            ],
            'stats' => [
                'speed' => $career->final_speed ?? 0,
                'stamina' => $career->final_stamina ?? 0,
                'power' => $career->final_power ?? 0,
                'guts' => $career->final_guts ?? 0,
                'wit' => $career->final_wit ?? 0,
            ],
            'sp' => [
                'total_earned' => $career->total_skill_points_earned ?? 0,
                'total_spent' => $career->total_skill_points_spent ?? 0,
            ],
            'training_sessions' => $career->trainingSessions
                ->map(fn (TrainingSession $s) => [
                    'id' => $s->id,
                    'turn_number' => $s->turn_number,
                    'training_type' => $s->training_type,
                ])
                ->values()
                ->toArray(),
            'races' => $career->races
                ->map(fn (Race $r): array => [
                    'id' => $r->id,
                    'name' => $r->race_name,
                    'result' => $r->race_result,
                    'position' => $r->finish_position,
                ])
                ->values()
                ->toArray(),
            'skills_acquired' => $career->skillAcquisitions
                ->map(fn (SkillAcquisition $sa) => [
                    'id' => $sa->id,
                    'skill_id' => $sa->skill_id,
                    'turn_acquired' => $sa->turn_acquired,
                ])
                ->values()
                ->toArray(),
            'performance' => [
                'total_races_won' => $career->total_races_won ?? 0,
                'total_races_participated' => $career->total_races_participated ?? 0,
                'win_rate' => $career->win_rate ?? 0,
                'total_fans_gained' => $career->total_fans_gained ?? 0,
                'efficiency_rating' => $career->efficiency_rating ?? 0,
            ],
            'meta' => [
                'captured_at' => now()->toISOString(),
                'version' => '1.0',
            ],
        ];
    }

    /**
     * Restore a career to a snapshot's state.
     *
     * @return array{success: bool, message: string}
     */
    public function restoreSnapshot(RunSnapshot $snapshot): array
    {
        $career = $snapshot->career;

        if (! $career) {
            return ['success' => false, 'message' => 'Career not found for this snapshot.'];
        }

        $data = $snapshot->snapshot_data;

        if ($snapshot->checksum && $this->calculateChecksum($data) !== $snapshot->checksum) {
            return ['success' => false, 'message' => 'Snapshot data integrity check failed.'];
        }

        /** @var array<string, mixed> $careerData */
        $careerData = $data['career'] ?? [];
        /** @var array<string, mixed> $statsData */
        $statsData = $data['stats'] ?? [];
        /** @var array<string, mixed> $spData */
        $spData = $data['sp'] ?? [];

        $career->update([
            'current_turn' => $careerData['current_turn'] ?? $career->current_turn,
            'current_phase' => $careerData['current_phase'] ?? $career->current_phase,
            'status' => $careerData['status'] ?? $career->status,
            'final_speed' => $statsData['speed'] ?? $career->final_speed,
            'final_stamina' => $statsData['stamina'] ?? $career->final_stamina,
            'final_power' => $statsData['power'] ?? $career->final_power,
            'final_guts' => $statsData['guts'] ?? $career->final_guts,
            'final_wit' => $statsData['wit'] ?? $career->final_wit,
            'total_skill_points_earned' => $spData['total_earned'] ?? $career->total_skill_points_earned,
            'total_skill_points_spent' => $spData['total_spent'] ?? $career->total_skill_points_spent,
        ]);

        return [
            'success' => true,
            'message' => "Career restored to turn {$snapshot->turn_number} snapshot.",
        ];
    }

    /**
     * Compare two snapshots of the same career.
     *
     * @return array<string, mixed>
     */
    public function compareSnapshots(RunSnapshot $snapshotA, RunSnapshot $snapshotB): array
    {
        $dataA = $snapshotA->snapshot_data;
        $dataB = $snapshotB->snapshot_data;

        /** @var array<string, int> $statsA */
        $statsA = $dataA['stats'] ?? [];
        /** @var array<string, int> $statsB */
        $statsB = $dataB['stats'] ?? [];

        $statDiffs = [];
        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $valA = $statsA[$stat] ?? 0;
            $valB = $statsB[$stat] ?? 0;
            $statDiffs[$stat] = [
                'from' => $valA,
                'to' => $valB,
                'diff' => $valB - $valA,
            ];
        }

        /** @var array<int, mixed> $trainA */
        $trainA = $dataA['training_sessions'] ?? [];
        /** @var array<int, mixed> $trainB */
        $trainB = $dataB['training_sessions'] ?? [];
        /** @var array<int, mixed> $racesA */
        $racesA = $dataA['races'] ?? [];
        /** @var array<int, mixed> $racesB */
        $racesB = $dataB['races'] ?? [];
        /** @var array<int, mixed> $skillsA */
        $skillsA = $dataA['skills_acquired'] ?? [];
        /** @var array<int, mixed> $skillsB */
        $skillsB = $dataB['skills_acquired'] ?? [];

        return [
            'snapshot_a' => [
                'id' => $snapshotA->id,
                'turn' => $snapshotA->turn_number,
                'created_at' => $snapshotA->created_at?->toISOString(),
            ],
            'snapshot_b' => [
                'id' => $snapshotB->id,
                'turn' => $snapshotB->turn_number,
                'created_at' => $snapshotB->created_at?->toISOString(),
            ],
            'stat_diffs' => $statDiffs,
            'turn_diff' => $snapshotB->turn_number - $snapshotA->turn_number,
            'training_sessions_diff' => count($trainB) - count($trainA),
            'races_diff' => count($racesB) - count($racesA),
            'skills_diff' => count($skillsB) - count($skillsA),
        ];
    }

    /**
     * Get all snapshots for a career.
     *
     * @return Collection<int, RunSnapshot>
     */
    public function getSnapshotsForCareer(Career $career): Collection
    {
        return $career->runSnapshots()
            ->orderByDesc('turn_number')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Auto-cleanup old snapshots, keeping the latest N per career.
     */
    public function cleanupOldSnapshots(Career $career, int $keepLatest = 20): int
    {
        $snapshotIds = $career->runSnapshots()
            ->orderByDesc('created_at')
            ->pluck('id');

        if ($snapshotIds->count() <= $keepLatest) {
            return 0;
        }

        $idsToDelete = $snapshotIds->slice($keepLatest)->values();

        /** @var int $deleted */
        $deleted = RunSnapshot::query()->whereIn('id', $idsToDelete->toArray())->delete();

        return $deleted;
    }

    /**
     * Calculate a checksum for snapshot data integrity.
     *
     * @param  array<string, mixed>  $data
     */
    private function calculateChecksum(array $data): string
    {
        return hash('sha256', json_encode($data) ?: '');
    }
}
