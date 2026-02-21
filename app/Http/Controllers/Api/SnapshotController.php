<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\RunSnapshot;
use App\Services\SnapshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SnapshotController extends Controller
{
    public function __construct(
        private SnapshotService $snapshotService,
    ) {}

    /**
     * List snapshots for a career.
     */
    public function index(Request $request, int $careerId): JsonResponse
    {
        $career = Career::where('user_id', $request->user()?->id)->findOrFail($careerId);
        $snapshots = $this->snapshotService->getSnapshotsForCareer($career);

        return response()->json([
            'snapshots' => $snapshots->map(fn (RunSnapshot $s) => [
                'id' => $s->id,
                'turn_number' => $s->turn_number,
                'trigger_type' => $s->trigger_type,
                'description' => $s->description,
                'created_at' => $s->created_at?->toISOString(),
                'time_ago' => $s->created_at?->diffForHumans(),
            ]),
            'total' => $snapshots->count(),
        ]);
    }

    /**
     * Create a manual snapshot.
     */
    public function store(Request $request, int $careerId): JsonResponse
    {
        $career = Career::where('user_id', $request->user()?->id)->findOrFail($careerId);

        $snapshot = $this->snapshotService->createSnapshot(
            $career,
            'manual',
            $request->filled('description') ? $request->string('description')->toString() : null,
        );

        return response()->json([
            'message' => 'Snapshot created successfully.',
            'snapshot' => [
                'id' => $snapshot->id,
                'turn_number' => $snapshot->turn_number,
                'trigger_type' => $snapshot->trigger_type,
                'description' => $snapshot->description,
                'created_at' => $snapshot->created_at?->toISOString(),
            ],
        ], 201);
    }

    /**
     * View a specific snapshot's data.
     */
    public function show(Request $request, int $careerId, int $snapshotId): JsonResponse
    {
        $career = Career::where('user_id', $request->user()?->id)->findOrFail($careerId);
        $snapshot = $career->runSnapshots()->findOrFail($snapshotId);

        return response()->json([
            'snapshot' => [
                'id' => $snapshot->id,
                'turn_number' => $snapshot->turn_number,
                'trigger_type' => $snapshot->trigger_type,
                'description' => $snapshot->description,
                'data' => $snapshot->snapshot_data,
                'created_at' => $snapshot->created_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Restore a career to a snapshot state.
     */
    public function restore(Request $request, int $careerId, int $snapshotId): JsonResponse
    {
        $career = Career::where('user_id', $request->user()?->id)->findOrFail($careerId);
        $snapshot = $career->runSnapshots()->findOrFail($snapshotId);

        // Create a backup snapshot before restoring
        $this->snapshotService->createSnapshot($career, 'auto_pre_restore', 'Auto-saved before restore');

        $result = $this->snapshotService->restoreSnapshot($snapshot);

        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json(['message' => $result['message']]);
    }

    /**
     * Compare two snapshots.
     */
    public function compare(Request $request, int $careerId): JsonResponse
    {
        $career = Career::where('user_id', $request->user()?->id)->findOrFail($careerId);

        $validated = $request->validate([
            'snapshot_a_id' => 'required|integer',
            'snapshot_b_id' => 'required|integer',
        ]);

        /** @var \App\Models\RunSnapshot $snapshotA */
        $snapshotA = $career->runSnapshots()->findOrFail($validated['snapshot_a_id']);
        /** @var \App\Models\RunSnapshot $snapshotB */
        $snapshotB = $career->runSnapshots()->findOrFail($validated['snapshot_b_id']);

        $comparison = $this->snapshotService->compareSnapshots($snapshotA, $snapshotB);

        return response()->json($comparison);
    }

    /**
     * Delete a snapshot.
     */
    public function destroy(Request $request, int $careerId, int $snapshotId): JsonResponse
    {
        $career = Career::where('user_id', $request->user()?->id)->findOrFail($careerId);
        $snapshot = $career->runSnapshots()->findOrFail($snapshotId);
        $snapshot->delete();

        return response()->json(['message' => 'Snapshot deleted.']);
    }

    /**
     * Cleanup old snapshots for a career.
     */
    public function cleanup(Request $request, int $careerId): JsonResponse
    {
        $career = Career::where('user_id', $request->user()?->id)->findOrFail($careerId);
        $keepLatest = max($request->integer('keep', 20), 5);
        $deleted = $this->snapshotService->cleanupOldSnapshots($career, $keepLatest);

        return response()->json([
            'message' => "{$deleted} old snapshot(s) cleaned up.",
            'deleted' => $deleted,
        ]);
    }
}
