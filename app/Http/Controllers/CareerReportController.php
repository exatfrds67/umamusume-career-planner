<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Career;
use App\Models\Character;
use App\Services\CareerReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Career Report Controller
 *
 * Handles career report generation, display, and export functionality.
 * Provides intelligent reporting with key insights and recommendations.
 *
 * Requirements: 15.5, 25.5 (Task 5.2.4)
 */
class CareerReportController extends Controller
{
    public function __construct(
        protected CareerReportingService $reportingService
    ) {}

    /**
     * Display the reports index page with available reports
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        // Get all characters with their careers for the current user
        $characters = Character::query()->where('user_id', $user->id ?? throw new \Exception('User required'))
            ->with(['careers' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->orderBy('name')
            ->get();

        // Get recent careers for quick access
        $recentCareers = Career::whereHas('character', function ($query) use ($user) {
            $query->where('user_id', $user->id ?? throw new \Exception('User required'));
        })
            ->with('character')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('reports.index', compact('characters', 'recentCareers'));
    }

    /**
     * Display a career summary report
     */
    public function showCareerReport(Career $career): View|RedirectResponse
    {
        // Ensure user owns this career's character
        if ($career->character?->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this career report.');
        }

        $report = $this->reportingService->generateCareerSummaryReport($career);

        return view('reports.career', compact('career', 'report'));
    }

    /**
     * Display a character report across all careers
     */
    public function showCharacterReport(Character $character): View|RedirectResponse
    {
        // Ensure user owns this character
        if ($character->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this character report.');
        }

        $report = $this->reportingService->generateCharacterReport($character);

        return view('reports.character', compact('character', 'report'));
    }

    /**
     * Export career report to JSON format
     */
    public function exportJson(Career $career): JsonResponse
    {
        // Ensure user owns this career's character
        if ($career->character?->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this career report.');
        }

        $jsonContent = $this->reportingService->exportToJson($career);

        return response()->json(
            json_decode($jsonContent, true),
            200,
            [
                'Content-Disposition' => 'attachment; filename="career_report_'.$career->id.'.json"',
            ]
        );
    }

    /**
     * Export career report to CSV format
     */
    public function exportCsv(Career $career): StreamedResponse
    {
        // Ensure user owns this career's character
        if ($career->character?->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this career report.');
        }

        $csvData = $this->reportingService->exportToCsv($career);
        $filename = 'career_report_'.$career->id.'_'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($csvData) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            // Write headers
            fputcsv($handle, $csvData['headers']);

            // Write rows
            foreach ($csvData['rows'] as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Export career report to PDF-ready format
     */
    public function exportPdf(Career $career): View
    {
        // Ensure user owns this career's character
        if ($career->character?->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this career report.');
        }

        $pdfData = $this->reportingService->exportToPdfFormat($career);

        return view('reports.pdf', compact('career', 'pdfData'));
    }

    /**
     * Get report data via API (for AJAX requests)
     */
    public function getReportData(Career $career): JsonResponse
    {
        // Ensure user owns this career's character
        if ($career->character?->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report = $this->reportingService->generateCareerSummaryReport($career);

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Get character report data via API (for AJAX requests)
     */
    public function getCharacterReportData(Character $character): JsonResponse
    {
        // Ensure user owns this character
        if ($character->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report = $this->reportingService->generateCharacterReport($character);

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Clear cached report for a career
     */
    public function clearCache(Career $career): JsonResponse
    {
        // Ensure user owns this career's character
        if ($career->character?->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->reportingService->clearCareerReportCache($career);

        return response()->json([
            'success' => true,
            'message' => 'Report cache cleared successfully.',
        ]);
    }

    /**
     * Compare multiple careers
     */
    public function compareReports(Request $request): View|RedirectResponse
    {
        $careerIds = $request->input('careers', []);

        if (! is_array($careerIds) || count($careerIds) < 2) {
            return redirect()
                ->route('reports.index')
                ->with('error', 'Please select at least 2 careers to compare.');
        }

        // Validate all careers belong to the current user
        $careers = Career::query()->whereIn('id', $careerIds)
            ->with('character')
            ->get();

        foreach ($careers as $career) {
            if ($career->character?->user_id !== Auth::id()) {
                abort(403, 'Unauthorized access to one or more careers.');
            }
        }

        // Generate reports for each career
        $reports = [];
        foreach ($careers as $career) {
            $reports[$career->id] = [
                'career' => $career,
                'report' => $this->reportingService->generateCareerSummaryReport($career),
            ];
        }

        /** @phpstan-ignore argument.type */
        return view('reports.compare', compact('reports'));
    }
}
