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
    Route::post('/logout', [\App\Http\Controllers\Api\Auth\AuthController::class, 'logout'])->name('api.logout');
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
    // Connectivity Monitoring (Task 2.2.1)
    Route::prefix('connectivity')->name('connectivity.')->group(function () {
        Route::get('/status', [\App\Http\Controllers\Api\ConnectivityController::class, 'status'])->name('status');
        Route::post('/check', [\App\Http\Controllers\Api\ConnectivityController::class, 'check'])->name('check');
        Route::get('/offline-info', [\App\Http\Controllers\Api\ConnectivityController::class, 'offlineInfo'])->name('offline-info');
        Route::get('/recommendations', [\App\Http\Controllers\Api\ConnectivityController::class, 'recommendations'])->name('recommendations');
        Route::get('/report', [\App\Http\Controllers\Api\ConnectivityController::class, 'report'])->name('report');
    });
});

// Profile API Routes
Route::middleware('auth:sanctum')->prefix('v1/profile')->name('api.v1.profile.')->group(function () {
    Route::get('/', [App\Http\Controllers\ProfileController::class, 'show'])->name('show');
    Route::put('/', [App\Http\Controllers\ProfileController::class, 'updateApi'])->name('update');
    Route::put('/password', [App\Http\Controllers\ProfileController::class, 'changePasswordApi'])->name('password.change');
    Route::post('/avatar', [App\Http\Controllers\ProfileController::class, 'uploadAvatar'])->name('avatar');
    Route::delete('/avatar', [App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
    Route::get('/export', [App\Http\Controllers\ProfileController::class, 'exportData'])->name('export');
    Route::delete('/', [App\Http\Controllers\ProfileController::class, 'destroyApi'])->name('destroy');
});

// Training Prediction API Routes (Protected)
Route::middleware('auth:sanctum')->prefix('training-predictions')->name('api.training-predictions.')->group(function () {
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

// Advisory API Routes (AI-Powered Training Advisory System)
Route::middleware(['auth:sanctum', 'throttle:10,1'])->prefix('advisory')->name('api.advisory.')->group(function () {
    // Training recommendations
    Route::post('/training/recommendations', [\App\Http\Controllers\Api\AdvisoryController::class, 'getTrainingRecommendations'])
        ->name('training.recommendations');

    // Skill purchase advice
    Route::post('/skills/advice', [\App\Http\Controllers\Api\AdvisoryController::class, 'getSkillPurchaseAdvice'])
        ->name('skills.advice');

    // Race strategy generation
    Route::post('/race/strategy', [\App\Http\Controllers\Api\AdvisoryController::class, 'getRaceStrategy'])
        ->name('race.strategy');

    // Critical situation detection
    Route::post('/critical/detect', [\App\Http\Controllers\Api\AdvisoryController::class, 'detectCriticalSituations'])
        ->name('critical.detect');

    // Outcome recording for prediction accuracy tracking
    Route::post('/training/outcome', [\App\Http\Controllers\Api\AdvisoryController::class, 'recordTrainingOutcome'])
        ->name('training.outcome');

    Route::post('/race/outcome', [\App\Http\Controllers\Api\AdvisoryController::class, 'recordRaceOutcome'])
        ->name('race.outcome');
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

    // Build Planner Routes
    Route::get('/build-templates', [App\Http\Controllers\Api\SkillBuildController::class, 'templates'])
        ->name('build-templates');

    Route::get('/saved-builds', [App\Http\Controllers\Api\SkillBuildController::class, 'savedBuilds'])
        ->name('saved-builds');

    Route::post('/build-optimization', [App\Http\Controllers\Api\SkillBuildController::class, 'optimize'])
        ->name('build-optimization');

    Route::post('/apply-build', [App\Http\Controllers\Api\SkillBuildController::class, 'applyBuild'])
        ->name('apply-build');

    Route::post('/save-build', [App\Http\Controllers\Api\SkillBuildController::class, 'saveBuild'])
        ->name('save-build');

    Route::delete('/builds/{id}', [App\Http\Controllers\Api\SkillBuildController::class, 'deleteBuild'])
        ->name('delete-build');
});

// Character-specific skill routes
Route::prefix('characters/{characterId}')->name('api.characters.')->group(function () {
    Route::get('/skill-evolution/opportunities', [App\Http\Controllers\Api\SkillManagementController::class, 'evolutionOpportunities'])
        ->name('evolution-opportunities');

    Route::get('/agent-performance', [App\Http\Controllers\Api\SkillManagementController::class, 'agentPerformance'])
        ->name('agent-performance');

    // Character-specific skill recommendations
    // Used by Skills Management page (/skills) for AI-powered recommendations
    Route::post('/skill-recommendations', [\App\Http\Controllers\Api\SkillRecommendationController::class, 'getRecommendations'])
        ->middleware('auth:sanctum')
        ->name('skill-recommendations');
});

// Skill Hint API Routes
Route::prefix('characters/{characterId}/skill-hints')->name('api.skill-hints.')->group(function () {
    // CRUD operations - index and store first
    Route::get('/', [SkillHintController::class, 'index'])
        ->name('index');

    Route::post('/', [SkillHintController::class, 'store'])
        ->name('store');

    // Cost and statistics - MUST be before /{id} routes to avoid matching "statistics" as an id
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

    // CRUD operations - show and destroy MUST be last (wildcard routes)
    Route::get('/{id}', [SkillHintController::class, 'show'])
        ->name('show');

    Route::delete('/{id}', [SkillHintController::class, 'destroy'])
        ->name('destroy');
});

// V1 Character Deck Management API Routes (must be before other deck routes to take precedence)
Route::middleware('auth:sanctum')->prefix('v1/characters')->name('api.v1.characters.')->group(function () {
    Route::get('/{id}/deck', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'getDeck'])
        ->name('deck.get');
    Route::post('/{id}/deck', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'addCard'])
        ->name('deck.add-card');
    Route::delete('/{id}/deck/{slot}', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'removeCardBySlot'])
        ->where('slot', '[0-9]+')
        ->name('deck.remove-slot');
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

    Route::delete('/cards/{position}', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'removeCardBySlot'])
        ->name('cards.remove-by-slot');

    Route::post('/cards/swap', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'swapCards'])
        ->name('cards.swap');

    Route::put('/cards/replace', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'replaceCard'])
        ->name('cards.replace');

    Route::put('/cards/{position}/details', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'updateCardDetails'])
        ->name('cards.update-details');

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

    // Stream message response (SSE)
    Route::post('/message/stream', [\App\Http\Controllers\AIChatController::class, 'sendMessageStreaming'])
        ->name('message.stream');

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

    // Get available models
    Route::get('/models', [\App\Http\Controllers\AIChatController::class, 'getModels'])
        ->name('models');

    // User preferences
    Route::get('/preferences', [\App\Http\Controllers\AIChatController::class, 'preferences'])
        ->name('preferences.get');

    Route::post('/preferences', [\App\Http\Controllers\AIChatController::class, 'preferences'])
        ->name('preferences.update');

    // Rate a message (thumbs up/down feedback)
    Route::post('/rate', [\App\Http\Controllers\AIChatController::class, 'rateMessage'])
        ->name('rate');
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

// V1 OCR API Routes
Route::middleware('auth:sanctum')->prefix('v1/ocr')->name('api.v1.ocr.')->group(function () {
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

// Neuron AI Agent API Routes (Task 14 - Neuron AI Integration)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('neuron')->name('api.neuron.')->group(function () {
    // Training Advisor Agent
    Route::prefix('training-advisor')->name('training-advisor.')->group(function () {
        Route::post('/advice', [\App\Http\Controllers\Api\TrainingAdvisorController::class, 'getAdvice'])
            ->name('advice');
        Route::post('/advice/stream', [\App\Http\Controllers\Api\TrainingAdvisorController::class, 'getAdviceStreaming'])
            ->name('advice.stream');
        Route::get('/history/{characterId}', [\App\Http\Controllers\Api\TrainingAdvisorController::class, 'getHistory'])
            ->name('history');
    });

    // Race Strategy Agent
    Route::prefix('race-strategy')->name('race-strategy.')->group(function () {
        Route::post('/strategy', [\App\Http\Controllers\Api\RaceStrategyController::class, 'getStrategy'])
            ->name('strategy');
        Route::post('/strategy/stream', [\App\Http\Controllers\Api\RaceStrategyController::class, 'getStrategyStreaming'])
            ->name('strategy.stream');
        Route::get('/history/{characterId}', [\App\Http\Controllers\Api\RaceStrategyController::class, 'getHistory'])
            ->name('history');
        Route::post('/recommended-skills', [\App\Http\Controllers\Api\RaceStrategyController::class, 'getRecommendedSkills'])
            ->name('recommended-skills');
    });

    // Skill Recommendation Agent
    Route::prefix('skill-recommendation')->name('skill-recommendation.')->group(function () {
        Route::post('/recommendations', [\App\Http\Controllers\Api\SkillRecommendationController::class, 'getRecommendations'])
            ->name('recommendations');
        Route::post('/recommendations/stream', [\App\Http\Controllers\Api\SkillRecommendationController::class, 'getRecommendationsStreaming'])
            ->name('recommendations.stream');
        Route::get('/history/{characterId}', [\App\Http\Controllers\Api\SkillRecommendationController::class, 'getHistory'])
            ->name('history');
        Route::get('/synergies/{characterId}', [\App\Http\Controllers\Api\SkillRecommendationController::class, 'getSynergies'])
            ->name('synergies');
    });

    // Career Planning Agent
    Route::prefix('career-planning')->name('career-planning.')->group(function () {
        Route::post('/plan', [\App\Http\Controllers\Api\CareerPlanningController::class, 'getPlan'])
            ->name('plan');
        Route::post('/plan/stream', [\App\Http\Controllers\Api\CareerPlanningController::class, 'getPlanStreaming'])
            ->name('plan.stream');
        Route::get('/history/{characterId}', [\App\Http\Controllers\Api\CareerPlanningController::class, 'getHistory'])
            ->name('history');
    });
});

// V1 API Routes for Characters
Route::middleware('auth:sanctum')->prefix('v1')->name('api.v1.')->group(function () {
    // User endpoint
    Route::get('/user', function (Request $request) {
        $user = $request->user();

        return response()->json($user?->only(['id', 'name', 'email', 'created_at', 'updated_at']) ?? []);
    });

    // Character routes
    Route::get('/characters', [\App\Http\Controllers\Api\V1\CharacterController::class, 'index'])
        ->name('characters.index');
    Route::post('/characters', [\App\Http\Controllers\Api\V1\CharacterController::class, 'store'])
        ->name('characters.store');
    Route::get('/characters/{id}', [\App\Http\Controllers\Api\V1\CharacterController::class, 'show'])
        ->name('characters.show');
    Route::put('/characters/{id}', [\App\Http\Controllers\Api\V1\CharacterController::class, 'update'])
        ->name('characters.update');
    Route::delete('/characters/{id}', [\App\Http\Controllers\Api\V1\CharacterController::class, 'destroy'])
        ->name('characters.destroy');
    Route::post('/characters/{id}/skills', [\App\Http\Controllers\Api\V1\CharacterController::class, 'acquireSkill'])
        ->name('characters.skills.acquire');
    Route::get('/characters/{id}/skills', [\App\Http\Controllers\Api\V1\CharacterController::class, 'skills'])
        ->name('characters.skills.index');
    Route::delete('/characters/{id}/skills/{skillId}', [\App\Http\Controllers\Api\V1\CharacterController::class, 'removeSkill'])
        ->name('characters.skills.remove');

    // Career routes
    Route::get('/careers', [\App\Http\Controllers\Api\V1\CareerController::class, 'index'])
        ->name('careers.index');
    Route::post('/careers', [\App\Http\Controllers\Api\V1\CareerController::class, 'store'])
        ->name('careers.store');
    Route::get('/careers/{id}', [\App\Http\Controllers\Api\V1\CareerController::class, 'show'])
        ->name('careers.show');
    Route::put('/careers/{id}', [\App\Http\Controllers\Api\V1\CareerController::class, 'update'])
        ->name('careers.update');
    Route::delete('/careers/{id}', [\App\Http\Controllers\Api\V1\CareerController::class, 'destroy'])
        ->name('careers.destroy');
    Route::get('/careers/{id}/available-races', [\App\Http\Controllers\Api\V1\CareerController::class, 'availableRaces'])
        ->name('careers.available-races');
    Route::get('/careers/{id}/training-predictions', [\App\Http\Controllers\Api\V1\CareerController::class, 'trainingPredictions'])
        ->name('careers.training-predictions');
    Route::get('/careers/{id}/training-sessions', [\App\Http\Controllers\Api\V1\CareerController::class, 'trainingSessions'])
        ->name('careers.training-sessions.index');
    Route::post('/careers/{id}/training-sessions', [\App\Http\Controllers\Api\V1\CareerController::class, 'storeTrainingSession'])
        ->name('careers.training-sessions.store');
    Route::post('/careers/{id}/training-sessions/bulk', [\App\Http\Controllers\Api\V1\CareerController::class, 'bulkStoreTrainingSessions'])
        ->name('careers.training-sessions.bulk');
    Route::post('/careers/{id}/races', [\App\Http\Controllers\Api\V1\CareerController::class, 'storeRace'])
        ->name('careers.races.store');
    Route::put('/careers/{careerId}/races/{raceId}', [\App\Http\Controllers\Api\V1\CareerController::class, 'updateRace'])
        ->name('careers.races.update');
    Route::get('/careers/{id}/races', [\App\Http\Controllers\Api\V1\CareerController::class, 'races'])
        ->name('careers.races.index');
    Route::get('/careers/{id}/report', [\App\Http\Controllers\Api\V1\CareerController::class, 'report'])
        ->name('careers.report');
    Route::get('/careers/{id}/statistics', [\App\Http\Controllers\Api\V1\CareerController::class, 'statistics'])
        ->name('careers.statistics');
    Route::post('/careers/compare', [\App\Http\Controllers\Api\V1\CareerController::class, 'compare'])
        ->name('careers.compare');
    Route::post('/careers/patterns', [\App\Http\Controllers\Api\V1\CareerController::class, 'patterns'])
        ->name('careers.patterns');
    Route::post('/careers/recommendations', [\App\Http\Controllers\Api\V1\CareerController::class, 'recommendations'])
        ->name('careers.recommendations');

    // Skill routes
    Route::get('/skills', [\App\Http\Controllers\Api\V1\SkillController::class, 'index'])
        ->name('skills.index');
    Route::get('/skills/{id}', [\App\Http\Controllers\Api\V1\SkillController::class, 'show'])
        ->name('skills.show');
    Route::get('/skills/{id}/hints', [\App\Http\Controllers\Api\V1\SkillController::class, 'hints'])
        ->name('skills.hints');
    Route::get('/skills/analysis/recommendations', [\App\Http\Controllers\Api\V1\SkillController::class, 'recommendations'])
        ->name('skills.recommendations');
    Route::get('/skills/analysis/evolution', [\App\Http\Controllers\Api\V1\SkillController::class, 'evolution'])
        ->name('skills.evolution');

    // Support Card routes
    Route::get('/support-cards', [\App\Http\Controllers\Api\V1\SupportCardController::class, 'index'])
        ->name('support-cards.index');
    Route::get('/support-cards/meta-ranking', [\App\Http\Controllers\Api\V1\SupportCardController::class, 'metaRanking'])
        ->name('support-cards.meta-ranking');
    Route::get('/support-cards/{id}', [\App\Http\Controllers\Api\V1\SupportCardController::class, 'show'])
        ->name('support-cards.show');
    Route::get('/support-cards/{id}/synergies', [\App\Http\Controllers\Api\V1\SupportCardController::class, 'synergies'])
        ->name('support-cards.synergies');

    // Deck optimization route
    Route::get('/characters/{character}/deck/optimization', [\App\Http\Controllers\Api\V1\DeckManagementController::class, 'getRecommendations'])
        ->name('characters.deck.optimization');
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
    Route::get('/statistics', [\App\Http\Controllers\Api\CacheManagementController::class, 'getStatistics'])
        ->name('statistics');

    Route::get('/api-performance', [\App\Http\Controllers\Api\CacheManagementController::class, 'getApiPerformance'])
        ->name('api-performance');

    Route::get('/health', [\App\Http\Controllers\Api\CacheManagementController::class, 'getHealth'])
        ->name('health');

    // Cache operations
    Route::post('/warm', [\App\Http\Controllers\Api\CacheManagementController::class, 'warmCache'])
        ->name('warm');

    Route::post('/invalidate', [\App\Http\Controllers\Api\CacheManagementController::class, 'invalidateCache'])
        ->name('invalidate');

    Route::post('/clear-all', [\App\Http\Controllers\Api\CacheManagementController::class, 'clearAll'])
        ->name('clear-all');

    // Cost optimization
    Route::post('/optimize-strategy', [\App\Http\Controllers\Api\CacheManagementController::class, 'getOptimizedStrategy'])
        ->name('optimize-strategy');
});

// Fallback and Recovery API Routes (Task 4.4.3)
Route::middleware('auth:sanctum')->prefix('fallback')->name('api.fallback.')->group(function () {
    // Health monitoring
    Route::get('/health/status', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'healthStatus'])
        ->name('health.status');

    Route::get('/health/metrics', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'healthMetrics'])
        ->name('health.metrics');

    // Circuit breaker management
    Route::post('/circuit-breaker/reset', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'resetCircuitBreaker'])
        ->name('circuit-breaker.reset');

    Route::post('/circuit-breaker/reset-all', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'resetAllCircuitBreakers'])
        ->name('circuit-breaker.reset-all');

    // Degradation management
    Route::get('/degradation/status', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'degradationStatus'])
        ->name('degradation.status');

    Route::get('/degradation/metrics', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'degradationMetrics'])
        ->name('degradation.metrics');

    Route::post('/manual-input/enable', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'enableManualInput'])
        ->name('manual-input.enable');

    Route::post('/manual-input/disable', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'disableManualInput'])
        ->name('manual-input.disable');

    // Recovery operations
    Route::post('/recovery/attempt', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'attemptRecovery'])
        ->name('recovery.attempt');

    // Background sync
    Route::get('/sync/status', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'syncStatus'])
        ->name('sync.status');

    Route::post('/sync/queue', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'queueSync'])
        ->name('sync.queue');

    Route::post('/sync/process', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'processSyncQueue'])
        ->name('sync.process');

    Route::get('/sync/history', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'syncHistory'])
        ->name('sync.history');

    Route::post('/sync/reconcile', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'reconcileData'])
        ->name('sync.reconcile');

    // Alerting
    Route::get('/alerts/history', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'alertHistory'])
        ->name('alerts.history');

    Route::get('/alerts/unacknowledged', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'unacknowledgedAlerts'])
        ->name('alerts.unacknowledged');

    Route::post('/alerts/acknowledge', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'acknowledgeAlert'])
        ->name('alerts.acknowledge');

    Route::get('/alerts/statistics', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'alertStatistics'])
        ->name('alerts.statistics');

    // System status
    Route::get('/system/status', [\App\Http\Controllers\Api\FallbackRecoveryController::class, 'systemStatus'])
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

// Training Advisor API Routes (Neuron AI Integration - Task 12.1)
Route::middleware('auth:sanctum')->prefix('training-advisor')->name('api.training-advisor.')->group(function () {
    // Get training advice
    Route::post('/advice', [\App\Http\Controllers\Api\TrainingAdvisorController::class, 'getAdvice'])
        ->name('advice');

    // Get streaming training advice (SSE)
    Route::post('/advice/stream', [\App\Http\Controllers\Api\TrainingAdvisorController::class, 'getAdviceStreaming'])
        ->name('advice.stream');

    // Get training advice history
    Route::get('/history/{characterId}', [\App\Http\Controllers\Api\TrainingAdvisorController::class, 'getHistory'])
        ->name('history');
});

// Race Strategy API Routes (Neuron AI Integration - Task 12.2)
Route::middleware('auth:sanctum')->prefix('race-strategy')->name('api.race-strategy.')->group(function () {
    // Get race strategy
    Route::post('/strategy', [\App\Http\Controllers\Api\RaceStrategyController::class, 'getStrategy'])
        ->name('strategy');

    // Get streaming race strategy (SSE)
    Route::post('/streaming', [\App\Http\Controllers\Api\RaceStrategyController::class, 'getStrategyStreaming'])
        ->name('streaming');

    // Get race strategy history
    Route::get('/history/{characterId}', [\App\Http\Controllers\Api\RaceStrategyController::class, 'getHistory'])
        ->name('history');

    // Get recommended skills for a race
    Route::post('/recommended-skills', [\App\Http\Controllers\Api\RaceStrategyController::class, 'getRecommendedSkills'])
        ->name('recommended-skills');
});

// Skill Recommendation API Routes (Neuron AI Integration - Task 12.3)
Route::middleware('auth:sanctum')->prefix('skill-recommendations')->name('api.skill-recommendations.')->group(function () {
    // Get skill recommendations
    Route::post('/recommendations', [\App\Http\Controllers\Api\SkillRecommendationController::class, 'getRecommendations'])
        ->name('recommendations');

    // Get streaming skill recommendations (SSE)
    Route::post('/streaming', [\App\Http\Controllers\Api\SkillRecommendationController::class, 'getRecommendationsStreaming'])
        ->name('streaming');

    // Get skill acquisition history
    Route::get('/history/{characterId}', [\App\Http\Controllers\Api\SkillRecommendationController::class, 'getHistory'])
        ->name('history');

    // Get skill synergies
    Route::get('/synergies/{characterId}', [\App\Http\Controllers\Api\SkillRecommendationController::class, 'getSynergies'])
        ->name('synergies');
});

// Career Planning API Routes (Neuron AI Integration - Task 12.4)
Route::middleware('auth:sanctum')->prefix('career-planning')->name('api.career-planning.')->group(function () {
    // Get career planning guidance
    Route::post('/plan', [\App\Http\Controllers\Api\CareerPlanningController::class, 'getPlan'])
        ->name('plan');

    // Get streaming career plan (SSE)
    Route::post('/plan/stream', [\App\Http\Controllers\Api\CareerPlanningController::class, 'getPlanStreaming'])
        ->name('plan.stream');

    // Get career planning history
    Route::get('/history/{characterId}', [\App\Http\Controllers\Api\CareerPlanningController::class, 'getHistory'])
        ->name('history');
});

// External Data API Routes (umapyoi.net, umamusumedb.com)
Route::middleware(['throttle:api'])->prefix('external')->name('api.external.')->group(function () {
    // Fetch characters from umapyoi.net
    Route::get('/characters', [\App\Http\Controllers\Api\ExternalDataController::class, 'getCharacters'])
        ->name('characters');

    // Fetch support cards from umapyoi.net
    Route::get('/support-cards', [\App\Http\Controllers\Api\ExternalDataController::class, 'getSupportCards'])
        ->name('support-cards');

    // Fetch skills from umapyoi.net
    Route::get('/skills', [\App\Http\Controllers\Api\ExternalDataController::class, 'getSkills'])
        ->name('skills');

    // Fetch news from umapyoi.net
    Route::get('/news', [\App\Http\Controllers\Api\ExternalDataController::class, 'getNews'])
        ->name('news');

    // Check API status
    Route::get('/status', [\App\Http\Controllers\Api\ExternalDataController::class, 'getStatus'])
        ->name('status');

    // Clear cache
    Route::post('/clear-cache', [\App\Http\Controllers\Api\ExternalDataController::class, 'clearCache'])
        ->name('clear-cache');
});

// Character Prefill API Routes
Route::middleware(['web', 'auth', 'throttle:api'])->prefix('characters/prefill')->name('api.characters.prefill.')->group(function () {
    // Search characters for prefill
    Route::get('/search', [\App\Http\Controllers\Api\CharacterPrefillController::class, 'search'])
        ->name('search');

    // Get character prefill data
    Route::get('/{externalId}', [\App\Http\Controllers\Api\CharacterPrefillController::class, 'getPrefillData'])
        ->name('get');
});

// Support Card Import from External Sources
Route::middleware(['web', 'auth', 'throttle:api'])->group(function () {
    Route::post('/support-cards/import-external', [\App\Http\Controllers\Api\ExternalImportController::class, 'importSupportCard'])
        ->name('api.support-cards.import-external');
});
