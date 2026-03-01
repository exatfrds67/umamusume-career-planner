<?php

use App\Http\Controllers\CareerReportController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoricalTrackingController;
use App\Http\Controllers\RaceController;
use Illuminate\Support\Facades\Route;

// Main welcome route
Route::get('/', fn () => view('welcome'))->name('welcome');

// Demo routes for development and testing
Route::prefix('demo')->group(function () {
    Route::get('/password-toggle', fn () => view('test.password-demo'))->name('demo.password-toggle');
    Route::get('/remember-me', fn () => view('test.remember-me-demo'))->name('demo.remember-me');
    Route::get('/critical-alert-badge', fn () => view('test.critical-alert-badge-demo'))->name('demo.critical-alert-badge');
});

// Developer demos page
Route::get('/dev/demos', fn () => view('dev.demos'))->name('dev.demos');

// PWA routes - Service Worker and Offline Page
Route::get('/sw.js', function () {
    $content = file_get_contents(public_path('sw.js'));

    return response($content !== false ? $content : '', 200, [
        'Content-Type' => 'application/javascript; charset=utf-8',
    ]);
})->name('sw');

Route::get('/manifest.json', function () {
    $content = file_get_contents(public_path('manifest.json'));

    return response($content !== false ? $content : '{}', 200, [
        'Content-Type' => 'application/json',
    ]);
})->name('manifest');

Route::get('/offline.html', function () {
    $content = file_get_contents(public_path('offline.html'));

    return response($content !== false ? $content : '', 200, [
        'Content-Type' => 'text/html',
    ]);
})->name('offline');

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
    Route::post('/characters/{character}/toggle-pin', [CharacterController::class, 'togglePin'])->name('characters.toggle-pin');
    Route::post('/characters/{character}/select', [CharacterController::class, 'select'])->name('characters.select');
    Route::post('/api/characters/{character}/select', [CharacterController::class, 'select'])->name('api.characters.select');

    // Factor management routes
    Route::get('/characters/{character}/factors', [CharacterController::class, 'manageFactors'])->name('characters.factors.manage');
    Route::post('/characters/{character}/factors', [CharacterController::class, 'storeFactors'])->name('characters.factors.store');
    Route::put('/characters/{character}/factors/{factor}', [CharacterController::class, 'updateFactor'])->name('characters.factors.update');
    Route::delete('/characters/{character}/factors/{factor}', [CharacterController::class, 'destroyFactor'])->name('characters.factors.destroy');
    Route::patch('/characters/{character}/factors/{factor}/toggle', [CharacterController::class, 'toggleFactor'])->name('characters.factors.toggle');

    // Training routes
    Route::get('/characters/{character}/training', [App\Http\Controllers\TrainingController::class, 'index'])->name('training.index');
    Route::post('/characters/{character}/training', [App\Http\Controllers\TrainingController::class, 'store'])->name('training.store');

    // Training API routes (Phase 3)
    Route::prefix('api/training')->name('api.training.')->group(function () {
        Route::get('/characters/{character}/predictions', [App\Http\Controllers\TrainingController::class, 'predictions'])->name('predictions');
        Route::get('/characters/{character}/predictions/{facility}', [App\Http\Controllers\TrainingController::class, 'facilityPrediction'])->name('facility-prediction');
        Route::post('/characters/{character}/execute', [App\Http\Controllers\TrainingController::class, 'execute'])->name('execute');
        Route::get('/characters/{character}/deck', [App\Http\Controllers\TrainingController::class, 'deck'])->name('deck');
    });

    Route::get('/training/predictions', [App\Http\Controllers\TrainingPredictionController::class, 'index'])
        ->name('training.predictions');

    Route::get('/training/predictions/{character}', [App\Http\Controllers\TrainingPredictionController::class, 'show'])
        ->name('training.predictions.show');

    // Race routes
    Route::get('/races', [RaceController::class, 'index'])->name('races.index');
    Route::get('/races/calendar', [RaceController::class, 'calendar'])->name('races.calendar');
    Route::get('/races/targets', [RaceController::class, 'targets'])->name('races.targets');
    Route::get('/races/{race}', [RaceController::class, 'show'])->name('races.show');

    // Skills routes
    Route::get('/skills', [App\Http\Controllers\SkillController::class, 'index'])->name('skills.index');

    Route::get('/api/search', [App\Http\Controllers\Api\SearchController::class, 'search'])->name('api.search');

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

    // Support Card Deck Management API routes
    Route::prefix('api/v1/characters/{character}/deck')->name('api.v1.characters.deck.')->group(function () {
        Route::post('/save', [App\Http\Controllers\SupportCardController::class, 'saveDeck'])->name('save');
        Route::post('/add-card', [App\Http\Controllers\SupportCardController::class, 'addCardToDeck'])->name('add-card');
        Route::delete('/remove-card/{position}', [App\Http\Controllers\SupportCardController::class, 'removeCardFromDeck'])->name('remove-card');
        Route::put('/update-card/{position}', [App\Http\Controllers\SupportCardController::class, 'updateCardInDeck'])->name('update-card');
        Route::post('/swap', [App\Http\Controllers\SupportCardController::class, 'swapCards'])->name('swap');
        Route::delete('/clear', [App\Http\Controllers\SupportCardController::class, 'clearDeck'])->name('clear');
        Route::post('/optimize', [App\Http\Controllers\SupportCardController::class, 'optimizeDeck'])->name('optimize');
        Route::get('/analysis', [App\Http\Controllers\Api\V1\DeckManagementController::class, 'getAnalysis'])->name('analysis');
        Route::get('/recommendations', [App\Http\Controllers\Api\V1\DeckManagementController::class, 'getRecommendations'])->name('recommendations');
    });

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

    // External Data Browser routes
    Route::get('/external-data/browse', fn () => view('external-data.browse'))->name('external-data.browse');
    Route::post('/external-data/import-support-card', [App\Http\Controllers\Api\ExternalImportController::class, 'importSupportCard'])->name('external-data.import-support-card');

    // Profile routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'changePassword'])->name('profile.password.change');
    Route::post('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
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
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/password', [App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::get('/settings/export', [App\Http\Controllers\SettingsController::class, 'exportData'])->name('settings.export');
    Route::delete('/settings/account', [App\Http\Controllers\SettingsController::class, 'deleteAccount'])->name('settings.account.delete');
    Route::get('/settings/accessibility', fn () => view('settings.accessibility'))->name('settings.accessibility');
    Route::get('/settings/notifications', fn () => view('settings.notifications'))->name('settings.notifications');
    Route::get('/simulation', fn () => view('simulation.index'))->name('simulation.index');
    Route::get('/analytics/patterns', fn () => view('analytics.patterns'))->name('analytics.patterns');
    Route::get('/privacy/dashboard', App\Livewire\Privacy\PrivacyDashboard::class)->name('privacy.dashboard');
});

// Admin routes (requires authentication and admin privileges)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // User management
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-admin', [App\Http\Controllers\Admin\UserController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // System settings
    Route::get('/system-settings', [App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('system-settings.index');
    Route::post('/system-settings/clear-cache', [App\Http\Controllers\Admin\SystemSettingsController::class, 'clearCache'])->name('system-settings.clear-cache');
    Route::post('/system-settings/optimize', [App\Http\Controllers\Admin\SystemSettingsController::class, 'optimize'])->name('system-settings.optimize');
    Route::post('/system-settings/clear-optimization', [App\Http\Controllers\Admin\SystemSettingsController::class, 'clearOptimization'])->name('system-settings.clear-optimization');

    // Logs
    Route::get('/logs', [App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/download', [App\Http\Controllers\Admin\LogController::class, 'download'])->name('logs.download');
    Route::post('/logs/clear', [App\Http\Controllers\Admin\LogController::class, 'clear'])->name('logs.clear');

    // Database maintenance
    Route::get('/database/maintenance', [App\Http\Controllers\Admin\DatabaseController::class, 'maintenance'])->name('database.maintenance');
    Route::post('/database/optimize', [App\Http\Controllers\Admin\DatabaseController::class, 'optimize'])->name('database.optimize');
    Route::post('/database/backup', [App\Http\Controllers\Admin\DatabaseController::class, 'backup'])->name('database.backup');
    Route::get('/database/backup/{filename}', [App\Http\Controllers\Admin\DatabaseController::class, 'downloadBackup'])->name('database.backup.download');
    Route::post('/database/migrate', [App\Http\Controllers\Admin\DatabaseController::class, 'migrate'])->name('database.migrate');
    Route::post('/database/fresh', [App\Http\Controllers\Admin\DatabaseController::class, 'fresh'])->name('database.fresh');

    // Database seeders
    Route::get('/database/seeders', [App\Http\Controllers\Admin\DatabaseController::class, 'seeders'])->name('database.seeders');
    Route::post('/database/seeders/run', [App\Http\Controllers\Admin\DatabaseController::class, 'runSeeder'])->name('database.seeders.run');
    Route::post('/database/seeders/all', [App\Http\Controllers\Admin\DatabaseController::class, 'seedAll'])->name('database.seeders.all');

    // Queue monitor
    Route::get('/queue-monitor', [App\Http\Controllers\Admin\QueueController::class, 'index'])->name('queue.index');
    Route::post('/queue/{id}/retry', [App\Http\Controllers\Admin\QueueController::class, 'retry'])->name('queue.retry');
    Route::post('/queue/retry-all', [App\Http\Controllers\Admin\QueueController::class, 'retryAll'])->name('queue.retry-all');
    Route::delete('/queue/{id}', [App\Http\Controllers\Admin\QueueController::class, 'delete'])->name('queue.delete');
    Route::post('/queue/flush', [App\Http\Controllers\Admin\QueueController::class, 'flush'])->name('queue.flush');
    Route::post('/queue/restart', [App\Http\Controllers\Admin\QueueController::class, 'restart'])->name('queue.restart');

    // APM Dashboard
    Route::get('/apm', App\Livewire\Admin\ApmDashboard::class)->name('apm');
});

// Help routes (public)
Route::get('/help', fn () => response(view('help.index'))
    ->header('Cache-Control', 'public, max-age=3600, s-maxage=86400')
)->name('help.index');

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

// Components demo route - showcases all Phase 1-3 components
Route::get('/components-demo', function () {
    return view('components-demo');
})->name('components.demo');

// Frontend foundation demo route
Route::get('/demo', function () {
    return view('demo');
})->name('demo');

// API Testing route (development/testing only)
Route::get('/test-api', function () {
    return view('test-api');
})->name('test.api');
