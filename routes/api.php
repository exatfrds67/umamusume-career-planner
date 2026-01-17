<?php

use App\Http\Controllers\Api\SkillHintController;
use App\Http\Controllers\Api\TrainingPredictionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
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
