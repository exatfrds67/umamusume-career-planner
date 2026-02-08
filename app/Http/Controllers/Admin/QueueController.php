<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\View\View;

class QueueController extends Controller
{
    /**
     * Display queue monitor.
     */
    public function index(): View
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
    public function retry(string $id): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => [$id]]);

        return back()->with('success', 'Job queued for retry.');
    }

    /**
     * Retry all failed jobs.
     */
    public function retryAll(): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        return back()->with('success', 'All failed jobs queued for retry.');
    }

    /**
     * Delete a failed job.
     */
    public function delete(string $id): RedirectResponse
    {
        DB::table('failed_jobs')->where('id', $id)->delete();

        return back()->with('success', 'Failed job deleted.');
    }

    /**
     * Clear all failed jobs.
     */
    public function flush(): RedirectResponse
    {
        Artisan::call('queue:flush');

        return back()->with('success', 'All failed jobs cleared.');
    }

    /**
     * Restart queue workers.
     */
    public function restart(): RedirectResponse
    {
        Artisan::call('queue:restart');

        return back()->with('success', 'Queue workers will restart after completing current jobs.');
    }
}
