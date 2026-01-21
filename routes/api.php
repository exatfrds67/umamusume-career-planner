<?php

use App\Http\Controllers\Api\SkillHintController;
use App\Http\Controllers\Api\TrainingPredictionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/register', fn () => response()->json(['message' => 'Use POST method to register', 'endpoint' => '/api/register'], 405));
Route::post('/register', [\App\Http\Controllers\Api\Auth\AuthController::class, 'register'])->name('api.register');
Route::get('/login', fn () => response()->json(['message' => 'Use POST method to login', 'endpoint' => '/api/login'], 405));
Route::post('/login', [\App\Http\Controllers\Api\Auth\AuthController::class, 'login'])->name('api.login');
Route::post('/password/email', [\App\Http\Controllers\Api\Auth\AuthController::class, 'sendResetLink'])->name('password.email');
// Route::post('/password/reset', [NewPasswordController::class, 'store'])->name('password.store');

// Protected routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\Auth\AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [\App\Http\Controllers\Api\Auth\AuthController::class, 'me'])->name('me');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('characters', \App\Http\Controllers\Api\CharacterController::class)->names([
        'index' => 'api.characters.index',
        'store' => 'api.characters.store',
        'show' => 'api.characters.show',
        'update' => 'api.characters.update',
        'destroy' => 'api.characters.destroy',
    ]);
});

// Profile API Routes
Route::middleware('auth:sanctum')->prefix('v1/profile')->name('api.v1.profile.')->group(function () {
    Route::get('/', [App\Http\Controllers\ProfileController::class, 'show'])->name('show');
    Route::put('/', [App\Http\Controllers\ProfileController::class, 'updateApi'])->name('update');
    Route::put('/password', [App\Http\Controllers\ProfileController::class, 'changePasswordApi'])->name('password.change');
    Route::post('/avatar', [App\Http\Controllers\ProfileController::class, 'uploadAvatar'])->name('avatar');
    Route::get('/export', [App\Http\Controllers\ProfileController::class, 'exportData'])->name('export');
    Route::delete('/', [App\Http\Controllers\ProfileController::class, 'destroyApi'])->name('destroy');
});

// Training Prediction API Routes
// Training Prediction API Routes
Route::prefix('training-predictions')->name('api.training-predictions.')->group(function () {
    // Single prediction
    Route::post('/', [TrainingPredictionController::class, 'predict'])
        ->name('predict');

    // Batch predictions
    Route::post('/batch', [TrainingPredictionController::class, 'batchPredict'])
        ->name('batch');

    // Get recommendation
    Route::post('/recommend', [TrainingPredictionController::class, 'recommend'])
        ->name('recommend');

    // Cache management
    Route::delete('/cache/{characterId}', [TrainingPredictionController::class, 'clearCache'])
        ->name('cache.clear');

    Route::get('/cache/{characterId}/stats', [TrainingPredictionController::class, 'cacheStats'])
        ->name('cache.stats');
});

// Cache Monitoring API Routes (Task 2.1.2 - External API Cache Warming)
Route::prefix('external-cache')->name('api.external-cache.')->group(function () {
    // Cache statistics
    Route::get('/statistics', [\App\Http\Controllers\Api\CacheMonitoringController::class, 'statistics'])
        ->name('statistics');

    // Warming statistics
    Route::get('/warming/statistics', [\App\Http\Controllers\Api\CacheMonitoringController::class, 'warmingStatistics'])
        ->name('warming.statistics');

    // Trigger cache warming
    Route::post('/warm', [\App\Http\Controllers\Api\CacheMonitoringController::class, 'warm'])
        ->name('warm');

    // Comprehensive cache information
    Route::get('/info', [\App\Http\Controllers\Api\CacheMonitoringController::class, 'info'])
        ->name('info');

    // Cache size
    Route::get('/size', [\App\Http\Controllers\Api\CacheMonitoringController::class, 'size'])
        ->name('size');

    // Cached keys
    Route::get('/keys', [\App\Http\Controllers\Api\CacheMonitoringController::class, 'keys'])
        ->name('keys');
});

// API Monitoring Dashboard Routes (Task 5.1.1 - Monitoring Dashboard)
Route::prefix('monitoring')->name('api.monitoring.')->group(function () {
    // Comprehensive dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Api\APIMonitoringController::class, 'dashboard'])
        ->name('dashboard');

    // Response time tracking
    Route::get('/response-times', [\App\Http\Controllers\Api\APIMonitoringController::class, 'responseTimes'])
        ->name('response-times');

    // Cache performance monitoring
    Route::get('/cache-performance', [\App\Http\Controllers\Api\APIMonitoringController::class, 'cachePerformance'])
        ->name('cache-performance');

    // Error rate tracking
    Route::get('/error-rates', [\App\Http\Controllers\Api\APIMonitoringController::class, 'errorRates'])
        ->name('error-rates');

    // Health status
    Route::get('/health', [\App\Http\Controllers\Api\APIMonitoringController::class, 'health'])
        ->name('health');

    // Alerts management
    Route::get('/alerts', [\App\Http\Controllers\Api\APIMonitoringController::class, 'alerts'])
        ->name('alerts');

    Route::post('/alerts/{alertId}/acknowledge', [\App\Http\Controllers\Api\APIMonitoringController::class, 'acknowledgeAlert'])
        ->name('alerts.acknowledge');

    // Performance recommendations
    Route::get('/recommendations', [\App\Http\Controllers\Api\APIMonitoringController::class, 'recommendations'])
        ->name('recommendations');

    // Request volume statistics
    Route::get('/request-volume', [\App\Http\Controllers\Api\APIMonitoringController::class, 'requestVolume'])
        ->name('request-volume');

    // Circuit breaker status
    Route::get('/circuit-breakers', [\App\Http\Controllers\Api\APIMonitoringController::class, 'circuitBreakers'])
        ->name('circuit-breakers');

    Route::post('/circuit-breakers/reset', [\App\Http\Controllers\Api\APIMonitoringController::class, 'resetCircuitBreaker'])
        ->name('circuit-breakers.reset');

    // Real-time metrics
    Route::get('/realtime', [\App\Http\Controllers\Api\APIMonitoringController::class, 'realtime'])
        ->name('realtime');

    // Historical metrics
    Route::get('/historical', [\App\Http\Controllers\Api\APIMonitoringController::class, 'historical'])
        ->name('historical');

    // Reset metrics
    Route::post('/reset', [\App\Http\Controllers\Api\APIMonitoringController::class, 'resetMetrics'])
        ->name('reset');
});

// Cache Invalidation API Routes (Task 2.1.3 - Cache Invalidation Logic)
Route::prefix('external-cache/invalidate')->name('api.external-cache.invalidate.')->group(function () {
    // Manual invalidation endpoints
    Route::post('/pattern', [\App\Http\Controllers\Api\CacheInvalidationController::class, 'invalidateByPattern'])
        ->name('pattern');

    Route::post('/type', [\App\Http\Controllers\Api\CacheInvalidationController::class, 'invalidateByType'])
        ->name('type');

    Route::post('/keys', [\App\Http\Controllers\Api\CacheInvalidationController::class, 'invalidateKeys'])
        ->name('keys');

    Route::post('/stale', [\App\Http\Controllers\Api\CacheInvalidationController::class, 'invalidateStale'])
        ->name('stale');

    Route::post('/flush', [\App\Http\Controllers\Api\CacheInvalidationController::class, 'flush'])
        ->name('flush');

    // Version tracking system
    Route::get('/version', [\App\Http\Controllers\Api\CacheInvalidationController::class, 'getVersion'])
        ->name('version.get');

    Route::post('/version', [\App\Http\Controllers\Api\CacheInvalidationController::class, 'setVersion'])
        ->name('version.set');

    // Cache staleness check
    Route::get('/staleness', [\App\Http\Controllers\Api\CacheInvalidationController::class, 'checkStaleness'])
        ->name('staleness');
});

// Skill Management API Routes
Route::prefix('skills')->name('api.skills.')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\SkillManagementController::class, 'index'])
        ->name('index');

    Route::post('/acquire', [App\Http\Controllers\Api\SkillManagementController::class, 'acquire'])
        ->name('acquire');

    Route::post('/evolve', [App\Http\Controllers\Api\SkillManagementController::class, 'evolve'])
        ->name('evolve');

    Route::get('/recommendations', [App\Http\Controllers\Api\SkillManagementController::class, 'recommendations'])
        ->name('recommendations');
});

// Character-specific skill routes
Route::prefix('characters/{characterId}')->name('api.characters.')->group(function () {
    Route::get('/skill-evolution/opportunities', [App\Http\Controllers\Api\SkillManagementController::class, 'evolutionOpportunities'])
        ->name('evolution-opportunities');

    Route::get('/agent-performance', [App\Http\Controllers\Api\SkillManagementController::class, 'agentPerformance'])
        ->name('agent-performance');
});

// Skill Hint API Routes
Route::prefix('characters/{characterId}/skill-hints')->name('api.skill-hints.')->group(function () {
    // CRUD operations
    Route::get('/', [SkillHintController::class, 'index'])
        ->name('index');

    Route::post('/', [SkillHintController::class, 'store'])
        ->name('store');

    Route::get('/{id}', [SkillHintController::class, 'show'])
        ->name('show');

    Route::delete('/{id}', [SkillHintController::class, 'destroy'])
        ->name('destroy');

    // Cost and statistics
    Route::get('/skills/{skillId}/cost-breakdown', [SkillHintController::class, 'costBreakdown'])
        ->name('cost-breakdown');

    Route::get('/statistics', [SkillHintController::class, 'statistics'])
        ->name('statistics');

    // Hint opportunities and predictions
    Route::post('/predict-opportunities', [SkillHintController::class, 'predictOpportunities'])
        ->name('predict-opportunities');

    Route::post('/collection-strategy', [SkillHintController::class, 'collectionStrategy'])
        ->name('collection-strategy');

    // MCP-powered optimization
    Route::post('/optimization-analysis', [SkillHintController::class, 'optimizationAnalysis'])
        ->name('optimization-analysis');

    Route::post('/optimal-sequence', [SkillHintController::class, 'optimalSequence'])
        ->name('optimal-sequence');

    Route::post('/evaluate-efficiency', [SkillHintController::class, 'evaluateEfficiency'])
        ->name('evaluate-efficiency');

    // Mark hints as used
    Route::post('/skills/{skillId}/mark-used', [SkillHintController::class, 'markAsUsed'])
        ->name('mark-used');
});

// Support Card Deck API Routes
Route::prefix('v1/characters/{character}')->name('api.v1.characters.')->group(function () {
    // Deck management
    Route::post('/deck', [\App\Http\Controllers\Api\SupportDeckController::class, 'save'])
        ->name('deck.save');

    Route::get('/deck', [\App\Http\Controllers\Api\SupportDeckController::class, 'show'])
        ->name('deck.show');

    Route::delete('/deck', [\App\Http\Controllers\Api\SupportDeckController::class, 'clear'])
        ->name('deck.clear');

    // Deck validation and scoring
    Route::post('/deck/validate', [\App\Http\Controllers\Api\SupportDeckController::class, 'validate'])
        ->name('deck.validate');

    Route::get('/deck/synergy', [\App\Http\Controllers\Api\SupportDeckController::class, 'synergy'])
        ->name('deck.synergy');

    // Deck recommendations
    Route::get('/deck/recommendations', [\App\Http\Controllers\Api\SupportDeckController::class, 'recommendations'])
        ->name('deck.recommendations');

    // Update individual card in deck
    Route::patch('/deck/{card}', [\App\Http\Controllers\Api\SupportDeckController::class, 'updateCard'])
        ->name('deck.update-card');
});

// New Deck Management API Routes
Route::prefix('v1/characters/{character}/deck')->name('api.v1.characters.deck.')->group(function () {
    // Get deck
    Route::get('/', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'getDeck'])
        ->name('get');

    // Card operations
    Route::post('/cards', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'addCard'])
        ->name('cards.add');

    Route::delete('/cards', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'removeCard'])
        ->name('cards.remove');

    Route::post('/cards/swap', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'swapCards'])
        ->name('cards.swap');

    Route::put('/cards/replace', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'replaceCard'])
        ->name('cards.replace');

    // Deck operations
    Route::delete('/clear', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'clearDeck'])
        ->name('clear');

    // Analysis and recommendations
    Route::get('/analysis', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'getAnalysis'])
        ->name('analysis');

    Route::get('/recommendations', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'getRecommendations'])
        ->name('recommendations');

    // Friendship management
    Route::post('/friendship/update', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'updateFriendship'])
        ->name('friendship.update');

    Route::get('/friendship/overview', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'getFriendshipOverview'])
        ->name('friendship.overview');
});

// AI Dashboard API Routes
Route::prefix('ai/dashboard')->name('api.ai.dashboard.')->group(function () {
    // Overview
    Route::get('/overview', [\App\Http\Controllers\Api\AIDashboardController::class, 'overview'])
        ->name('overview');

    // MCP Server Status
    Route::get('/servers', [\App\Http\Controllers\Api\AIDashboardController::class, 'servers'])
        ->name('servers');

    Route::get('/servers/{serverName}/health', [\App\Http\Controllers\Api\AIDashboardController::class, 'serverHealth'])
        ->name('servers.health');

    // AI Provider Performance
    Route::get('/performance', [\App\Http\Controllers\Api\AIDashboardController::class, 'performance'])
        ->name('performance');

    // Cost Tracking
    Route::get('/costs', [\App\Http\Controllers\Api\AIDashboardController::class, 'costs'])
        ->name('costs');

    Route::get('/costs/optimization', [\App\Http\Controllers\Api\AIDashboardController::class, 'costOptimization'])
        ->name('costs.optimization');

    Route::get('/costs/trend', [\App\Http\Controllers\Api\AIDashboardController::class, 'costTrend'])
        ->name('costs.trend');

    // Agent Management
    Route::get('/agents', [\App\Http\Controllers\Api\AIDashboardController::class, 'agents'])
        ->name('agents');

    // Conversation History
    Route::get('/conversations', [\App\Http\Controllers\Api\AIDashboardController::class, 'conversations'])
        ->name('conversations');

    Route::get('/conversations/analytics', [\App\Http\Controllers\Api\AIDashboardController::class, 'conversationAnalytics'])
        ->name('conversations.analytics');

    Route::get('/conversations/export', [\App\Http\Controllers\Api\AIDashboardController::class, 'exportConversations'])
        ->name('conversations.export');
});

// MCP Dashboard API Routes
Route::middleware('auth:sanctum')->prefix('mcp/dashboard')->name('api.mcp.dashboard.')->group(function () {
    // Overview
    Route::get('/overview', [\App\Http\Controllers\Api\MCPDashboardController::class, 'overview'])
        ->name('overview');

    // MCP Server Management
    Route::get('/servers', [\App\Http\Controllers\Api\MCPDashboardController::class, 'servers'])
        ->name('servers');

    // Agent Activity
    Route::get('/agents', [\App\Http\Controllers\Api\MCPDashboardController::class, 'agents'])
        ->name('agents');

    // Cost Transparency
    Route::get('/costs', [\App\Http\Controllers\Api\MCPDashboardController::class, 'costs'])
        ->name('costs');

    // Performance Metrics
    Route::get('/performance', [\App\Http\Controllers\Api\MCPDashboardController::class, 'performance'])
        ->name('performance');

    // User Settings
    Route::get('/settings', [\App\Http\Controllers\Api\MCPDashboardController::class, 'settings'])
        ->name('settings');

    Route::post('/settings', [\App\Http\Controllers\Api\MCPDashboardController::class, 'updateSettings'])
        ->name('settings.update');
});

// AI Chat API Routes
Route::middleware('auth:sanctum')->prefix('ai/chat')->name('api.ai.chat.')->group(function () {
    // Send message
    Route::post('/message', [\App\Http\Controllers\AIChatController::class, 'sendMessage'])
        ->name('message');

    // Get server status with real-time monitoring
    Route::get('/server-status', [\App\Http\Controllers\AIChatController::class, 'getServerStatus'])
        ->name('server-status');

    // Get workflow status with agent progress tracking
    Route::get('/workflow-status', [\App\Http\Controllers\AIChatController::class, 'getWorkflowStatus'])
        ->name('workflow-status');

    // Get tool usage and execution monitoring
    Route::get('/tool-usage', [\App\Http\Controllers\AIChatController::class, 'getToolUsage'])
        ->name('tool-usage');

    // Get performance metrics comparing providers and agents
    Route::get('/performance-metrics', [\App\Http\Controllers\AIChatController::class, 'getPerformanceMetrics'])
        ->name('performance-metrics');

    // Handle server disconnection and recovery
    Route::post('/server-disconnection', [\App\Http\Controllers\AIChatController::class, 'handleServerDisconnection'])
        ->name('server-disconnection');

    // User preferences
    Route::get('/preferences', [\App\Http\Controllers\AIChatController::class, 'preferences'])
        ->name('preferences.get');

    Route::post('/preferences', [\App\Http\Controllers\AIChatController::class, 'preferences'])
        ->name('preferences.update');
});

// MCP Monitoring and Control API Routes
Route::middleware('auth:sanctum')->prefix('mcp/monitoring')->name('api.mcp.monitoring.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\MCPMonitoringController::class, 'dashboard'])
        ->name('dashboard');

    // Server Management
    Route::get('/servers/status', [\App\Http\Controllers\MCPMonitoringController::class, 'serverStatus'])
        ->name('servers.status');

    Route::post('/servers/{serverId}/connect', [\App\Http\Controllers\MCPMonitoringController::class, 'connectServer'])
        ->name('servers.connect');

    Route::post('/servers/{serverId}/disconnect', [\App\Http\Controllers\MCPMonitoringController::class, 'disconnectServer'])
        ->name('servers.disconnect');

    Route::put('/servers/{serverId}/config', [\App\Http\Controllers\MCPMonitoringController::class, 'updateServerConfig'])
        ->name('servers.config');

    // Agent Lifecycle Management
    Route::get('/agents/status', [\App\Http\Controllers\MCPMonitoringController::class, 'agentStatus'])
        ->name('agents.status');

    Route::post('/agents', [\App\Http\Controllers\MCPMonitoringController::class, 'createAgent'])
        ->name('agents.create');

    Route::post('/agents/{agentId}/terminate', [\App\Http\Controllers\MCPMonitoringController::class, 'terminateAgent'])
        ->name('agents.terminate');

    // Cost Tracking
    Route::get('/costs', [\App\Http\Controllers\MCPMonitoringController::class, 'costAnalytics'])
        ->name('costs');

    // Performance Metrics
    Route::get('/performance', [\App\Http\Controllers\MCPMonitoringController::class, 'performanceMetrics'])
        ->name('performance');

    // Optimization Recommendations
    Route::get('/recommendations', [\App\Http\Controllers\MCPMonitoringController::class, 'recommendations'])
        ->name('recommendations');

    // User Preferences
    Route::get('/preferences', [\App\Http\Controllers\MCPMonitoringController::class, 'getUserPreferences'])
        ->name('preferences.get');

    Route::post('/preferences', [\App\Http\Controllers\MCPMonitoringController::class, 'updateUserPreference'])
        ->name('preferences.update');

    // Tool Usage History
    Route::get('/tool-usage', [\App\Http\Controllers\MCPMonitoringController::class, 'toolUsageHistory'])
        ->name('tool-usage');
});

// OCR API Routes (Task 5.1)
Route::middleware('auth:sanctum')->prefix('ocr')->name('api.ocr.')->group(function () {
    Route::post('/upload', [\App\Http\Controllers\OCRUploadController::class, 'upload'])
        ->name('upload');
    Route::get('/status', [\App\Http\Controllers\OCRUploadController::class, 'status'])
        ->name('status');
});

// Data Import API Routes (Task 5.3.1)
Route::middleware('auth:sanctum')->prefix('import')->name('api.import.')->group(function () {
    Route::post('/preview', [\App\Http\Controllers\ImportController::class, 'preview'])
        ->name('preview');
    Route::post('/execute', [\App\Http\Controllers\ImportController::class, 'execute'])
        ->name('execute');
    Route::get('/templates', [\App\Http\Controllers\ImportController::class, 'templates'])
        ->name('templates');
    Route::get('/history', [\App\Http\Controllers\ImportController::class, 'history'])
        ->name('history');
});

// Data Export API Routes (Task 5.3.2)
Route::middleware('auth:sanctum')->prefix('export')->name('api.export.')->group(function () {
    Route::post('/generate', [\App\Http\Controllers\ExportController::class, 'generate'])
        ->name('generate');
    Route::post('/preview', [\App\Http\Controllers\ExportController::class, 'preview'])
        ->name('preview');
    Route::get('/download/{path}', [\App\Http\Controllers\ExportController::class, 'download'])
        ->name('download')
        ->where('path', '.*');
    Route::get('/templates', [\App\Http\Controllers\ExportController::class, 'templates'])
        ->name('templates');
    Route::get('/history', [\App\Http\Controllers\ExportController::class, 'history'])
        ->name('history');
    Route::post('/schedule', [\App\Http\Controllers\ExportController::class, 'schedule'])
        ->name('schedule');
    Route::get('/schedules', [\App\Http\Controllers\ExportController::class, 'schedules'])
        ->name('schedules');
    Route::delete('/schedule/{scheduleId}', [\App\Http\Controllers\ExportController::class, 'deleteSchedule'])
        ->name('schedule.delete');
});

// Data Migration API Routes (Task 5.3.3)
Route::middleware('auth:sanctum')->prefix('migration')->name('api.migration.')->group(function () {
    // Legacy format conversion
    Route::post('/convert', [\App\Http\Controllers\MigrationController::class, 'convert'])
        ->name('convert');
    Route::post('/detect-format', [\App\Http\Controllers\MigrationController::class, 'detectFormat'])
        ->name('detect-format');
    Route::get('/transformation-rules', [\App\Http\Controllers\MigrationController::class, 'getTransformationRules'])
        ->name('transformation-rules');

    // Batch import processing
    Route::post('/batch', [\App\Http\Controllers\MigrationController::class, 'startBatch'])
        ->name('batch.start');
    Route::get('/batch/{id}/status', [\App\Http\Controllers\MigrationController::class, 'getBatchStatus'])
        ->name('batch.status');
    Route::post('/batch/{id}/process', [\App\Http\Controllers\MigrationController::class, 'processBatch'])
        ->name('batch.process');
    Route::post('/batch/{id}/cancel', [\App\Http\Controllers\MigrationController::class, 'cancelBatch'])
        ->name('batch.cancel');
    Route::get('/batch/{id}/report', [\App\Http\Controllers\MigrationController::class, 'getReport'])
        ->name('batch.report');

    // Data validation
    Route::post('/validate', [\App\Http\Controllers\MigrationController::class, 'validateData'])
        ->name('validate');

    // Conflict resolution
    Route::post('/resolve-conflicts', [\App\Http\Controllers\MigrationController::class, 'resolveConflicts'])
        ->name('resolve-conflicts');
    Route::get('/batch/{id}/conflicts', [\App\Http\Controllers\MigrationController::class, 'getConflicts'])
        ->name('batch.conflicts');
});

// Backup & Restore API Routes (Task 5.3.4)
Route::middleware('auth:sanctum')->prefix('backup')->name('api.backup.')->group(function () {
    // Statistics and cleanup (must be before {id} routes)
    Route::get('/statistics', [\App\Http\Controllers\BackupController::class, 'statistics'])
        ->name('statistics');
    Route::post('/cleanup', [\App\Http\Controllers\BackupController::class, 'cleanup'])
        ->name('cleanup');

    // Schedule management (must be before {id} routes)
    Route::post('/schedule', [\App\Http\Controllers\BackupController::class, 'schedule'])
        ->name('schedule.create');
    Route::get('/schedule', [\App\Http\Controllers\BackupController::class, 'getSchedules'])
        ->name('schedule.list');
    Route::put('/schedule/{id}', [\App\Http\Controllers\BackupController::class, 'updateSchedule'])
        ->name('schedule.update');
    Route::delete('/schedule/{id}', [\App\Http\Controllers\BackupController::class, 'deleteSchedule'])
        ->name('schedule.delete');

    // Backup operations
    Route::post('/create', [\App\Http\Controllers\BackupController::class, 'create'])
        ->name('create');
    Route::get('/list', [\App\Http\Controllers\BackupController::class, 'list'])
        ->name('list');
    Route::get('/{id}', [\App\Http\Controllers\BackupController::class, 'show'])
        ->name('show');
    Route::post('/{id}/restore', [\App\Http\Controllers\BackupController::class, 'restore'])
        ->name('restore');
    Route::delete('/{id}', [\App\Http\Controllers\BackupController::class, 'destroy'])
        ->name('destroy');
});

// Data Management Hub API Routes (Task 5.3.5)
Route::middleware('auth:sanctum')->prefix('data-management')->name('api.data-management.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DataManagementController::class, 'dashboard'])
        ->name('dashboard');
    Route::get('/history', [\App\Http\Controllers\DataManagementController::class, 'history'])
        ->name('history');
    Route::get('/status', [\App\Http\Controllers\DataManagementController::class, 'status'])
        ->name('status');
    Route::get('/statistics', [\App\Http\Controllers\DataManagementController::class, 'statistics'])
        ->name('statistics');
});

// Career Export/Import API Routes (Task 5.3)
Route::middleware('auth:sanctum')->prefix('v1/characters/{character}')->name('api.v1.characters.')->group(function () {
    Route::get('/export', [\App\Http\Controllers\Api\CareerExportController::class, 'export'])
        ->name('export');
    Route::post('/import', [\App\Http\Controllers\Api\CareerExportController::class, 'import'])
        ->name('import');
    Route::get('/download', [\App\Http\Controllers\Api\CareerExportController::class, 'download'])
        ->name('download');
});

// Cache Management API Routes (Task 4.4.2)
Route::middleware('auth:sanctum')->prefix('cache')->name('api.cache.')->group(function () {
    // Statistics and monitoring
    Route::get('/statistics', [\App\Http\Controllers\API\CacheManagementController::class, 'getStatistics'])
        ->name('statistics');

    Route::get('/api-performance', [\App\Http\Controllers\API\CacheManagementController::class, 'getApiPerformance'])
        ->name('api-performance');

    Route::get('/health', [\App\Http\Controllers\API\CacheManagementController::class, 'getHealth'])
        ->name('health');

    // Cache operations
    Route::post('/warm', [\App\Http\Controllers\API\CacheManagementController::class, 'warmCache'])
        ->name('warm');

    Route::post('/invalidate', [\App\Http\Controllers\API\CacheManagementController::class, 'invalidateCache'])
        ->name('invalidate');

    Route::post('/clear-all', [\App\Http\Controllers\API\CacheManagementController::class, 'clearAll'])
        ->name('clear-all');

    // Cost optimization
    Route::post('/optimize-strategy', [\App\Http\Controllers\API\CacheManagementController::class, 'getOptimizedStrategy'])
        ->name('optimize-strategy');
});

// Fallback and Recovery API Routes (Task 4.4.3)
Route::middleware('auth:sanctum')->prefix('fallback')->name('api.fallback.')->group(function () {
    // Health monitoring
    Route::get('/health/status', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'healthStatus'])
        ->name('health.status');

    Route::get('/health/metrics', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'healthMetrics'])
        ->name('health.metrics');

    // Circuit breaker management
    Route::post('/circuit-breaker/reset', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'resetCircuitBreaker'])
        ->name('circuit-breaker.reset');

    Route::post('/circuit-breaker/reset-all', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'resetAllCircuitBreakers'])
        ->name('circuit-breaker.reset-all');

    // Degradation management
    Route::get('/degradation/status', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'degradationStatus'])
        ->name('degradation.status');

    Route::get('/degradation/metrics', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'degradationMetrics'])
        ->name('degradation.metrics');

    Route::post('/manual-input/enable', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'enableManualInput'])
        ->name('manual-input.enable');

    Route::post('/manual-input/disable', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'disableManualInput'])
        ->name('manual-input.disable');

    // Recovery operations
    Route::post('/recovery/attempt', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'attemptRecovery'])
        ->name('recovery.attempt');

    // Background sync
    Route::get('/sync/status', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'syncStatus'])
        ->name('sync.status');

    Route::post('/sync/queue', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'queueSync'])
        ->name('sync.queue');

    Route::post('/sync/process', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'processSyncQueue'])
        ->name('sync.process');

    Route::get('/sync/history', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'syncHistory'])
        ->name('sync.history');

    Route::post('/sync/reconcile', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'reconcileData'])
        ->name('sync.reconcile');

    // Alerting
    Route::get('/alerts/history', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'alertHistory'])
        ->name('alerts.history');

    Route::get('/alerts/unacknowledged', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'unacknowledgedAlerts'])
        ->name('alerts.unacknowledged');

    Route::post('/alerts/acknowledge', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'acknowledgeAlert'])
        ->name('alerts.acknowledge');

    Route::get('/alerts/statistics', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'alertStatistics'])
        ->name('alerts.statistics');

    // System status
    Route::get('/system/status', [\App\Http\Controllers\API\FallbackRecoveryController::class, 'systemStatus'])
        ->name('system.status');
});

// Career Comparison API Routes (Task 5.2.2)
Route::middleware('auth:sanctum')->prefix('careers/comparison')->name('api.careers.comparison.')->group(function () {
    // Side-by-side career comparison
    Route::post('/compare', [\App\Http\Controllers\Api\CareerComparisonController::class, 'compare'])
        ->name('compare');

    // Pattern identification for successful decision sequences
    Route::post('/patterns', [\App\Http\Controllers\Api\CareerComparisonController::class, 'patterns'])
        ->name('patterns');

    // Success factor analysis across multiple career runs
    Route::post('/success-factors', [\App\Http\Controllers\Api\CareerComparisonController::class, 'successFactors'])
        ->name('success-factors');

    // Statistical significance testing for pattern validation
    Route::post('/statistical-tests', [\App\Http\Controllers\Api\CareerComparisonController::class, 'statisticalTests'])
        ->name('statistical-tests');

    // Comprehensive analysis (all comparison features)
    Route::post('/comprehensive', [\App\Http\Controllers\Api\CareerComparisonController::class, 'comprehensive'])
        ->name('comprehensive');

    // Clear comparison cache
    Route::post('/clear-cache', [\App\Http\Controllers\Api\CareerComparisonController::class, 'clearCache'])
        ->name('clear-cache');
});

// Performance Monitoring API Routes (Task 6.1.1)
Route::middleware('auth:sanctum')->prefix('performance')->name('api.performance.')->group(function () {
    // Dashboard - comprehensive performance metrics
    Route::get('/dashboard', [\App\Http\Controllers\PerformanceController::class, 'dashboard'])
        ->name('dashboard');

    // Slow query log with filtering and pagination
    Route::get('/slow-queries', [\App\Http\Controllers\PerformanceController::class, 'slowQueryLog'])
        ->name('slow-queries');

    // Index analysis and recommendations
    Route::get('/index-analysis', [\App\Http\Controllers\PerformanceController::class, 'indexAnalysis'])
        ->name('index-analysis');

    // Query statistics
    Route::get('/query-stats', [\App\Http\Controllers\PerformanceController::class, 'queryStats'])
        ->name('query-stats');

    // Explain a specific query
    Route::post('/explain', [\App\Http\Controllers\PerformanceController::class, 'explainQuery'])
        ->name('explain');

    // Detect N+1 query issues
    Route::get('/n-plus-one', [\App\Http\Controllers\PerformanceController::class, 'detectNPlusOne'])
        ->name('n-plus-one');

    // Cache statistics
    Route::get('/cache-stats', [\App\Http\Controllers\PerformanceController::class, 'cacheStats'])
        ->name('cache-stats');

    // Cache invalidation
    Route::post('/invalidate-cache', [\App\Http\Controllers\PerformanceController::class, 'invalidateCache'])
        ->name('invalidate-cache');

    // Clear performance data
    Route::post('/clear-data', [\App\Http\Controllers\PerformanceController::class, 'clearData'])
        ->name('clear-data');

    // Redis Cache Optimization Routes (Task 6.1.2)
    Route::prefix('redis')->name('redis.')->group(function () {
        // Redis health check
        Route::get('/health', [\App\Http\Controllers\PerformanceController::class, 'redisHealth'])
            ->name('health');

        // Redis memory usage statistics
        Route::get('/memory', [\App\Http\Controllers\PerformanceController::class, 'redisMemory'])
            ->name('memory');

        // Cache warming trigger
        Route::post('/warm', [\App\Http\Controllers\PerformanceController::class, 'warmRedisCache'])
            ->name('warm');

        // Cache invalidation with tags
        Route::post('/invalidate', [\App\Http\Controllers\PerformanceController::class, 'invalidateRedisCache'])
            ->name('invalidate');

        // Cache hit rate statistics
        Route::get('/hit-rate', [\App\Http\Controllers\PerformanceController::class, 'redisHitRate'])
            ->name('hit-rate');

        // Optimization recommendations
        Route::get('/recommendations', [\App\Http\Controllers\PerformanceController::class, 'redisRecommendations'])
            ->name('recommendations');

        // Cleanup stale entries
        Route::post('/cleanup', [\App\Http\Controllers\PerformanceController::class, 'cleanupRedisCache'])
            ->name('cleanup');

        // Comprehensive Redis statistics
        Route::get('/stats', [\App\Http\Controllers\PerformanceController::class, 'redisComprehensiveStats'])
            ->name('stats');
    });

    // API Performance Monitoring Routes (Task 6.1.4)
    Route::prefix('api')->name('api.')->group(function () {
        // API performance dashboard
        Route::get('/dashboard', [\App\Http\Controllers\PerformanceController::class, 'apiDashboard'])
            ->name('dashboard');

        // API performance overview metrics
        Route::get('/overview', [\App\Http\Controllers\PerformanceController::class, 'apiOverview'])
            ->name('overview');

        // Endpoint-specific metrics
        Route::get('/endpoints', [\App\Http\Controllers\PerformanceController::class, 'apiEndpoints'])
            ->name('endpoints');

        // Bottleneck identification
        Route::get('/bottlenecks', [\App\Http\Controllers\PerformanceController::class, 'apiBottlenecks'])
            ->name('bottlenecks');

        // Slow API requests
        Route::get('/slow-requests', [\App\Http\Controllers\PerformanceController::class, 'apiSlowRequests'])
            ->name('slow-requests');

        // Performance trends
        Route::get('/trends', [\App\Http\Controllers\PerformanceController::class, 'apiTrends'])
            ->name('trends');

        // Request batching recommendations
        Route::get('/batching-recommendations', [\App\Http\Controllers\PerformanceController::class, 'apiBatchingRecommendations'])
            ->name('batching-recommendations');

        // Resource utilization
        Route::get('/resources', [\App\Http\Controllers\PerformanceController::class, 'apiResources'])
            ->name('resources');

        // Clear API metrics
        Route::post('/clear', [\App\Http\Controllers\PerformanceController::class, 'apiClearMetrics'])
            ->name('clear');

        // API configuration
        Route::get('/config', [\App\Http\Controllers\PerformanceController::class, 'apiConfig'])
            ->name('config');
    });

    // API Response Caching Routes (Task 6.1.4)
    Route::prefix('api-cache')->name('api-cache.')->group(function () {
        // Cache statistics
        Route::get('/stats', [\App\Http\Controllers\PerformanceController::class, 'apiCacheStats'])
            ->name('stats');

        // Cache invalidation
        Route::post('/invalidate', [\App\Http\Controllers\PerformanceController::class, 'apiCacheInvalidate'])
            ->name('invalidate');

        // Cache warming
        Route::post('/warm', [\App\Http\Controllers\PerformanceController::class, 'apiCacheWarm'])
            ->name('warm');
    });

    // Rate Limiting Status (Task 6.1.4)
    Route::get('/rate-limiting/status', [\App\Http\Controllers\PerformanceController::class, 'rateLimitingStatus'])
        ->name('rate-limiting.status');

    // APM (Application Performance Monitoring) Routes (Task 6.1.5)
    Route::prefix('apm')->name('apm.')->group(function () {
        // Comprehensive APM dashboard data
        Route::get('/dashboard', [\App\Http\Controllers\PerformanceController::class, 'apmDashboard'])
            ->name('dashboard');

        // Health score
        Route::get('/health-score', [\App\Http\Controllers\PerformanceController::class, 'apmHealthScore'])
            ->name('health-score');

        // Overview metrics
        Route::get('/overview', [\App\Http\Controllers\PerformanceController::class, 'apmOverview'])
            ->name('overview');

        // Performance trends
        Route::get('/trends', [\App\Http\Controllers\PerformanceController::class, 'apmTrends'])
            ->name('trends');

        // Aggregated metrics for specific metric type
        Route::get('/metrics/{metric}', [\App\Http\Controllers\PerformanceController::class, 'apmMetrics'])
            ->name('metrics');

        // Clear APM data
        Route::post('/clear', [\App\Http\Controllers\PerformanceController::class, 'apmClear'])
            ->name('clear');
    });

    // Performance Alerting Routes (Task 6.1.5)
    Route::prefix('alerts')->name('alerts.')->group(function () {
        // Check all alert conditions
        Route::post('/check', [\App\Http\Controllers\PerformanceController::class, 'alertsCheck'])
            ->name('check');

        // List all alerts
        Route::get('/', [\App\Http\Controllers\PerformanceController::class, 'alertsList'])
            ->name('list');

        // Get unacknowledged alerts
        Route::get('/unacknowledged', [\App\Http\Controllers\PerformanceController::class, 'alertsUnacknowledged'])
            ->name('unacknowledged');

        // Get alert statistics
        Route::get('/statistics', [\App\Http\Controllers\PerformanceController::class, 'alertsStatistics'])
            ->name('statistics');

        // Acknowledge an alert
        Route::post('/{alertId}/acknowledge', [\App\Http\Controllers\PerformanceController::class, 'alertsAcknowledge'])
            ->name('acknowledge');

        // Clear all alerts
        Route::post('/clear', [\App\Http\Controllers\PerformanceController::class, 'alertsClear'])
            ->name('clear');
    });

    // Performance Regression Detection Routes (Task 6.1.5)
    Route::prefix('regressions')->name('regressions.')->group(function () {
        // Check for regressions
        Route::post('/check', [\App\Http\Controllers\PerformanceController::class, 'regressionsCheck'])
            ->name('check');

        // List all regressions
        Route::get('/', [\App\Http\Controllers\PerformanceController::class, 'regressionsList'])
            ->name('list');

        // Get active regressions
        Route::get('/active', [\App\Http\Controllers\PerformanceController::class, 'regressionsActive'])
            ->name('active');

        // Get regression statistics
        Route::get('/statistics', [\App\Http\Controllers\PerformanceController::class, 'regressionsStatistics'])
            ->name('statistics');

        // Get baseline for a metric
        Route::get('/baseline/{metric}', [\App\Http\Controllers\PerformanceController::class, 'regressionsBaseline'])
            ->name('baseline');

        // Calculate baseline for a metric
        Route::post('/baseline/{metric}', [\App\Http\Controllers\PerformanceController::class, 'regressionsCalculateBaseline'])
            ->name('calculate-baseline');

        // Update regression status
        Route::patch('/{regressionId}', [\App\Http\Controllers\PerformanceController::class, 'regressionsUpdate'])
            ->name('update');

        // Generate regression report
        Route::get('/report', [\App\Http\Controllers\PerformanceController::class, 'regressionsReport'])
            ->name('report');

        // Clear all regression data
        Route::post('/clear', [\App\Http\Controllers\PerformanceController::class, 'regressionsClear'])
            ->name('clear');
    });
});

// Connectivity Monitoring API Routes (Task 2.2.1 - Offline Detection)
Route::prefix('connectivity')->name('api.connectivity.')->group(function () {
    // Get current connectivity status (cached)
    Route::get('/status', [\App\Http\Controllers\Api\ConnectivityController::class, 'status'])
        ->name('status');

    // Force connectivity check (bypass cache)
    Route::post('/check', [\App\Http\Controllers\Api\ConnectivityController::class, 'check'])
        ->name('check');

    // Get offline mode information
    Route::get('/offline-info', [\App\Http\Controllers\Api\ConnectivityController::class, 'offlineInfo'])
        ->name('offline-info');

    // Get connectivity recommendations
    Route::get('/recommendations', [\App\Http\Controllers\Api\ConnectivityController::class, 'recommendations'])
        ->name('recommendations');

    // Get comprehensive connectivity report
    Route::get('/report', [\App\Http\Controllers\Api\ConnectivityController::class, 'report'])
        ->name('report');
});
