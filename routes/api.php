<?php

use App\Http\Controllers\Api\SkillHintController;
use App\Http\Controllers\Api\TrainingPredictionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
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
Route::prefix('mcp/dashboard')->name('api.mcp.dashboard.')->group(function () {
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

    // Get server status
    Route::get('/server-status', [\App\Http\Controllers\AIChatController::class, 'getServerStatus'])
        ->name('server-status');

    // Get workflow status
    Route::get('/workflow-status', [\App\Http\Controllers\AIChatController::class, 'getWorkflowStatus'])
        ->name('workflow-status');

    // Get tool usage
    Route::get('/tool-usage', [\App\Http\Controllers\AIChatController::class, 'getToolUsage'])
        ->name('tool-usage');

    // User preferences
    Route::get('/preferences', [\App\Http\Controllers\AIChatController::class, 'preferences'])
        ->name('preferences.get');

    Route::post('/preferences', [\App\Http\Controllers\AIChatController::class, 'preferences'])
        ->name('preferences.update');
});
