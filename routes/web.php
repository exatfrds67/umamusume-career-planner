<?php

use App\Http\Controllers\CharacterController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Main welcome route
Route::get('/', fn () => view('welcome'))->name('welcome');

// About page with features and information
Route::get('/about', fn () => view('about'))->name('about');

// Dashboard route - now uses controller for real data
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
Route::get('/races', function () {
    return view('races.index');
})->name('races.index');

Route::get('/races/results', function () {
    return view('races.results');
})->name('races.results');

// Skills routes (placeholders)
Route::get('/skills', function () {
    return view('skills.index');
})->name('skills.index');

Route::get('/skills/optimizer', function () {
    return view('skills.optimizer');
})->name('skills.optimizer');

// Support Cards routes
Route::get('/support-cards', [App\Http\Controllers\SupportCardController::class, 'index'])->name('support-cards.index');
Route::get('/support-cards/{supportCard}', [App\Http\Controllers\SupportCardController::class, 'show'])->name('support-cards.show');
Route::get('/characters/{character}/deck-builder', [App\Http\Controllers\SupportCardController::class, 'deckBuilder'])->name('characters.deck-builder');

// Profile routes (placeholders)
Route::get('/profile', function () {
    return view('profile.show');
})->name('profile.show');

// Settings routes (placeholders)
Route::get('/settings', function () {
    return view('settings.index');
})->name('settings.index');

// Logout route (placeholder)
Route::post('/logout', function () {
    return redirect('/');
})->name('logout');

// Help routes (placeholders)
Route::get('/help', function () {
    return view('help.index');
})->name('help.index');

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
