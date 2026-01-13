<?php

use Illuminate\Support\Facades\Route;

// Main dashboard route using app layout with accessibility features
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Character management routes (placeholders)
Route::get('/characters', function () {
    return view('characters.index');
})->name('characters.index');

Route::get('/characters/create', function () {
    return view('characters.create');
})->name('characters.create');

// Training routes (placeholders)
Route::get('/training', function () {
    return view('training.index');
})->name('training.index');

Route::get('/training/predictions', function () {
    return view('training.predictions');
})->name('training.predictions');

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

// Support Cards routes (placeholders)
Route::get('/support-cards', function () {
    return view('support-cards.index');
})->name('support-cards.index');

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
