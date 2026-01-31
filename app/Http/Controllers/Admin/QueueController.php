<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class QueueController extends Controller
{
    /**
     * Display queue monitor.
     */
    public function index()
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate(20);

        $stats = [
            'failed_count' => DB::table('failed_jobs')->count(),
            'jobs_count' => DB::table('jobs')->count(),
        ];

        return view('admin.queue.index', compact('failedJobs', 'stats'));
    }

    /**
     * Retry a failed job.
     */
    public function retry(string $id)
    {
        Artisan::call('queue:retry', ['id' => [$id]]);

        return back()->with('success', 'Job queued for retry.');
    }

    /**
     * Retry all failed jobs.
     */
    public function retryAll()
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        return back()->with('success', 'All failed jobs queued for retry.');
    }

    /**
     * Delete a failed job.
     */
    public function delete(string $id)
    {
        DB::table('failed_jobs')->where('id', $id)->delete();

        return back()->with('success', 'Failed job deleted.');
    }

    /**
     * Clear all failed jobs.
     */
    public function flush()
    {
        Artisan::call('queue:flush');

        return back()->with('success', 'All failed jobs cleared.');
    }

    /**
     * Restart queue workers.
     */
    public function restart()
    {
        Artisan::call('queue:restart');

        return back()->with('success', 'Queue workers will restart after completing current jobs.');
    }
}
