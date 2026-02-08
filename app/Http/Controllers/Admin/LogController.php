<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\LogReaderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LogController extends Controller
{
    public function __construct(
        protected LogReaderService $logReader
    ) {}

    /**
     * Display application logs.
     */
    public function index(Request $request): View
    {
        $linesInput = $request->input('lines');
        $lines = is_numeric($linesInput) ? (int) $linesInput : 100;
        $level = $request->input('level');
        $levelString = is_string($level) ? $level : null;
        $search = $request->input('search');

        if (is_string($search) && $search !== '') {
            $logs = $this->logReader->searchLogs($search, $lines);
        } else {
            $logs = $this->logReader->getLogEntries($lines, $levelString);
        }

        $fileSize = $this->logReader->getLogFileSize();

        return view('admin.logs.index', compact('logs', 'fileSize'));
    }

    /**
     * Download log file.
     */
    public function download(): RedirectResponse|BinaryFileResponse
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
    public function clear(Request $request): RedirectResponse
    {
        $daysInput = $request->input('days');
        $days = is_numeric($daysInput) ? (int) $daysInput : 7;
        $deleted = $this->logReader->clearOldLogs($days);

        return back()->with('success', "Cleared {$deleted} old log file(s).");
    }
}
