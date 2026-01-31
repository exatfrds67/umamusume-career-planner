<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\LogReaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LogController extends Controller
{
    public function __construct(
        protected LogReaderService $logReader
    ) {}

    /**
     * Display application logs.
     */
    public function index(Request $request)
    {
        $lines = $request->input('lines', 100);
        $level = $request->input('level');
        $search = $request->input('search');

        if ($search) {
            $logs = $this->logReader->searchLogs($search, $lines);
        } else {
            $logs = $this->logReader->getLogEntries($lines, $level);
        }

        $fileSize = $this->logReader->getLogFileSize();

        return view('admin.logs.index', compact('logs', 'fileSize'));
    }

    /**
     * Download log file.
     */
    public function download()
    {
        $logPath = storage_path('logs/laravel.log');

        if (! file_exists($logPath)) {
            return back()->with('error', 'Log file not found.');
        }

        return Response::download($logPath, 'laravel_'.date('Y-m-d_His').'.log');
    }

    /**
     * Clear old logs.
     */
    public function clear(Request $request)
    {
        $days = $request->input('days', 7);
        $deleted = $this->logReader->clearOldLogs($days);

        return back()->with('success', "Cleared {$deleted} old log file(s).");
    }
}
