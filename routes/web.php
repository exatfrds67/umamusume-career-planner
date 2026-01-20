<?php

use App\Http\Controllers\CareerReportController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoricalTrackingController;
use Illuminate\Support\Facades\Route;

// Main welcome route
Route::get('/', fn () => view('welcome'))->name('welcome');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'create'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'store']);
    Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'create'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'store']);
});

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// About page with features and information
Route::get('/about', fn () => view('about'))->name('about');

// Dashboard route - now uses controller for real data (requires authentication)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Character management routes
    Route::get('/characters', [CharacterController::class, 'index'])->name('characters.index');
    Route::get('/characters/create', [CharacterController::class, 'create'])->name('characters.create');
    Route::post('/characters', [CharacterController::class, 'store'])->name('characters.store');
    Route::get('/characters/{character}', [CharacterController::class, 'show'])->name('characters.show');
    Route::get('/characters/{character}/edit', [CharacterController::class, 'edit'])->name('characters.edit');
    Route::put('/characters/{character}', [CharacterController::class, 'update'])->name('characters.update');
    Route::delete('/characters/{character}', [CharacterController::class, 'destroy'])->name('characters.destroy');
    Route::post('/characters/{character}/rest', [CharacterController::class, 'rest'])->name('characters.rest');
    Route::post('/characters/{character}/next-turn', [CharacterController::class, 'nextTurn'])->name('characters.next-turn');

    // Training routes
    Route::get('/characters/{character}/training', [App\Http\Controllers\TrainingController::class, 'index'])->name('training.index');
    Route::post('/characters/{character}/training', [App\Http\Controllers\TrainingController::class, 'store'])->name('training.store');

    Route::get('/training/predictions', [App\Http\Controllers\TrainingPredictionController::class, 'index'])
        ->name('training.predictions');

    Route::get('/training/predictions/{character}', [App\Http\Controllers\TrainingPredictionController::class, 'show'])
        ->name('training.predictions.show');

    // Race routes (placeholders)
    Route::get('/races', fn () => view('races.index'))->name('races.index');
    Route::get('/races/results', fn () => view('races.results'))->name('races.results');

    // Skills routes
    Route::get('/skills', [App\Http\Controllers\SkillController::class, 'index'])->name('skills.index');

    // AI Dashboard routes
    Route::get('/ai/dashboard', fn () => view('ai.dashboard'))->name('ai.dashboard');

    // AI Chat routes
    Route::get('/ai/chat', [App\Http\Controllers\AIChatController::class, 'index'])->name('ai.chat');

    // MCP Dashboard routes
    Route::get('/mcp/dashboard', fn () => view('mcp.dashboard'))->name('mcp.dashboard');

    // Support Cards routes
    Route::get('/support-cards', [App\Http\Controllers\SupportCardController::class, 'index'])->name('support-cards.index');
    Route::get('/support-cards/{supportCard}', [App\Http\Controllers\SupportCardController::class, 'show'])->name('support-cards.show');
    Route::get('/characters/{character}/deck-builder', [App\Http\Controllers\SupportCardController::class, 'deckBuilder'])->name('characters.deck-builder');

    // OCR Screenshot Processing routes
    Route::get('/ocr/upload', [App\Http\Controllers\OCRUploadController::class, 'showUploadPage'])->name('ocr.upload');
    Route::get('/ocr/results/{extraction}', [App\Http\Controllers\OCRUploadController::class, 'showResults'])->name('ocr.results');
    Route::post('/ocr/import/{extraction}', [App\Http\Controllers\OCRUploadController::class, 'import'])->name('ocr.import');

    // Data Import routes (Task 5.3.1)
    Route::get('/import', [App\Http\Controllers\ImportController::class, 'index'])->name('import.index');

    // Data Export routes (Task 5.3.2)
    Route::get('/export', [App\Http\Controllers\ExportController::class, 'index'])->name('export.index');

    // Data Migration routes (Task 5.3.3)
    Route::get('/migration', [App\Http\Controllers\MigrationController::class, 'index'])->name('migration.index');

    // Backup & Restore routes (Task 5.3.4)
    Route::get('/backup', [App\Http\Controllers\BackupController::class, 'index'])->name('backup.index');

    // Data Management Hub routes (Task 5.3.5)
    Route::get('/data-management', [App\Http\Controllers\DataManagementController::class, 'index'])->name('data-management.index');

    // Profile routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'changePassword'])->name('profile.password.change');
    Route::post('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
    Route::get('/profile/export', [App\Http\Controllers\ProfileController::class, 'exportData'])->name('profile.export');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Career Reports routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [CareerReportController::class, 'index'])->name('index');
        Route::get('/career/{career}', [CareerReportController::class, 'showCareerReport'])->name('career');
        Route::get('/character/{character}', [CareerReportController::class, 'showCharacterReport'])->name('character');
        Route::get('/compare', [CareerReportController::class, 'compareReports'])->name('compare');

        // Export routes
        Route::get('/career/{career}/export/json', [CareerReportController::class, 'exportJson'])->name('export.json');
        Route::get('/career/{career}/export/csv', [CareerReportController::class, 'exportCsv'])->name('export.csv');
        Route::get('/career/{career}/export/pdf', [CareerReportController::class, 'exportPdf'])->name('export.pdf');

        // API routes for AJAX
        Route::get('/api/career/{career}', [CareerReportController::class, 'getReportData'])->name('api.career');
        Route::get('/api/character/{character}', [CareerReportController::class, 'getCharacterReportData'])->name('api.character');
        Route::post('/api/career/{career}/clear-cache', [CareerReportController::class, 'clearCache'])->name('api.clear-cache');
    });

    // Historical Tracking and Benchmarking routes
    Route::prefix('historical')->name('historical.')->group(function () {
        Route::get('/', [HistoricalTrackingController::class, 'index'])->name('index');

        // API routes for AJAX
        Route::get('/api/trends', [HistoricalTrackingController::class, 'getLongTermTrends'])->name('api.trends');
        Route::get('/api/success-rates', [HistoricalTrackingController::class, 'getSuccessRates'])->name('api.success-rates');
        Route::get('/api/ml-recommendations', [HistoricalTrackingController::class, 'getMLRecommendations'])->name('api.ml-recommendations');
        Route::get('/api/benchmarks', [HistoricalTrackingController::class, 'getCommunityBenchmarks'])->name('api.benchmarks');
        Route::get('/api/user-comparison', [HistoricalTrackingController::class, 'getUserBenchmarkComparison'])->name('api.user-comparison');
        Route::get('/api/benchmark-trends', [HistoricalTrackingController::class, 'getBenchmarkTrends'])->name('api.benchmark-trends');
        Route::post('/api/clear-cache', [HistoricalTrackingController::class, 'clearCache'])->name('api.clear-cache');
    });

    // Performance Monitoring Dashboard (Task 6.1.5)
    Route::prefix('performance')->name('performance.')->group(function () {
        Route::get('/apm/dashboard', [App\Http\Controllers\PerformanceController::class, 'apmDashboardView'])
            ->name('apm.dashboard');
    });
});

// Settings routes (requires authentication)
Route::middleware('auth')->group(function () {
    Route::get('/settings', fn () => view('settings.index'))->name('settings.index');
});

// Help routes (public)
Route::get('/help', fn () => view('help.index'))->name('help.index');

// Feedback routes (placeholders)
Route::get('/feedback', function () {
    return view('feedback.create');
})->name('feedback.create');

// Privacy and Terms routes (placeholders)
Route::get('/privacy', function () {
    return view('privacy.policy');
})->name('privacy.policy');

Route::get('/terms', function () {
    return view('terms.service');
})->name('terms.service');

// Accessibility routes (placeholders)
Route::get('/accessibility-statement', function () {
    return view('accessibility.statement');
})->name('accessibility.statement');

Route::get('/keyboard-shortcuts', function () {
    return view('accessibility.shortcuts');
})->name('keyboard.shortcuts');

// Test route for layout components
Route::get('/test-layout', function () {
    return view('test-layout');
})->name('test.layout');

// Design system demo route
Route::get('/design-system-demo', function () {
    return view('design-system-demo');
})->name('design-system.demo');

// Frontend foundation demo route
Route::get('/demo', function () {
    return view('demo');
})->name('demo');
