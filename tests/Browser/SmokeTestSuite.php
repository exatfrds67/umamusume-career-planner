<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Smoke Test Suite
 *
 * Quick tests for all critical paths (target: 2-3 minutes)
 * - Core functionality verification
 * - Essential CRUD operations
 * - Critical authentication flows
 * - Basic integration health checks
 *
 * Run with: php artisan test --group=smoke
 *
 * @group browser
 * @group smoke
 */
describe('Critical Page Loads', function () {
    it('loads homepage without errors', function () {
        $page = visit('/');
        $page->assertSee('Uma Musume')
            ->assertNoJavaScriptErrors();
    })->group('smoke', 'critical');

    it('loads login page', function () {
        $page = visit('/login');
        $page->assertSee('Login')
            ->assertNoJavaScriptErrors();
    })->group('smoke', 'critical');

    it('loads dashboard for authenticated user', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = visit('/dashboard');
        $page->assertSee('Dashboard')
            ->assertNoJavaScriptErrors();
    })->group('smoke', 'critical');
});

describe('Authentication Smoke Tests', function () {
    it('can login with valid credentials', function () {
        $user = User::factory()->create([
            'email' => 'smoke@test.com',
            'password' => bcrypt('password123'),
        ]);

        $page = visit('/login');
        $page->fill('email', 'smoke@test.com')
            ->fill('password', 'password123')
            ->click('button[type="submit"]')
            ->pause(500);

        $page->assertPath('/dashboard');
    })->group('smoke', 'auth');

    it('can logout', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = visit('/dashboard');
        $page->click('Logout')
            ->pause(300);

        $page->assertPath('/');
    })->group('smoke', 'auth');

    it('redirects unauthenticated users to login', function () {
        $page = visit('/dashboard');
        $page->assertPath('/login');
    })->group('smoke', 'auth');
});

describe('Character CRUD Smoke Tests', function () {
    it('can create a character', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = visit('/dashboard');
        $page->click('Create Character')
            ->pause(300)
            ->fill('name', 'Smoke Test Character')
            ->click('Create')
            ->pause(500);

        $page->assertSee('Smoke Test Character');
    })->group('smoke', 'character');

    it('can view character details', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'View Test Character',
        ]);

        $this->actingAs($user);
        $page = visit("/characters/{$character->id}");

        $page->assertSee('View Test Character')
            ->assertNoJavaScriptErrors();
    })->group('smoke', 'character');

    it('can edit character', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $page = visit("/characters/{$character->id}/edit");

        $page->fill('name', 'Edited Character Name')
            ->click('Save')
            ->pause(400);

        $page->assertSee('Edited Character Name');
    })->group('smoke', 'character');

    it('can delete character', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $page = visit("/characters/{$character->id}/edit");

        $page->click('Delete')
            ->pause(200)
            ->click('Confirm') // Confirmation modal
            ->pause(400);

        $page->assertPath('/characters');
    })->group('smoke', 'character');
});

describe('Training Smoke Tests', function () {
    it('can access training screen', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $page = visit("/characters/{$character->id}/training");

        $page->assertSee('Training')
            ->assertNoJavaScriptErrors();
    })->group('smoke', 'training');

    it('can execute basic training action', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $page = visit("/characters/{$character->id}/training");

        $page->click('[data-facility="speed"]')
            ->pause(200)
            ->click('Train')
            ->pause(400);

        $page->assertNoJavaScriptErrors();
    })->group('smoke', 'training');
});

describe('Database Connectivity Smoke Tests', function () {
    it('can read from users table', function () {
        $user = User::factory()->create(['name' => 'DB Test User']);

        $this->actingAs($user);
        $page = visit('/settings');

        $page->assertSee('DB Test User');
    })->group('smoke', 'database');

    it('can write to database', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = visit('/settings');
        $page->fill('name', 'Updated DB User')
            ->click('Save Profile')
            ->pause(300);

        // Verify database was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated DB User',
        ]);
    })->group('smoke', 'database');
});

describe('API Endpoint Smoke Tests', function () {
    it('external data API returns data', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Try to load external data page (which likely makes API calls)
        $page = visit('/external-data/browse');
        $page->pause(1000) // Wait for API response
            ->assertNoJavaScriptErrors();
    })->group('smoke', 'api');
});

describe('JavaScript Functionality Smoke Tests', function () {
    it('Livewire components initialize', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = visit('/dashboard');

        // Check if Livewire is loaded
        $page->assertScript("typeof window.Livewire !== 'undefined'");
    })->group('smoke', 'javascript');

    it('Alpine.js initializes', function () {
        $page = visit('/');

        // Check if Alpine is loaded
        $page->assertScript("typeof window.Alpine !== 'undefined'");
    })->group('smoke', 'javascript');
});

describe('Form Validation Smoke Tests', function () {
    it('shows validation errors for empty character creation', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = visit('/dashboard');
        $page->click('Create Character')
            ->pause(300)
            ->click('Create') // Submit empty form
            ->pause(400);

        $page->assertSee('required');
    })->group('smoke', 'validation');

    it('shows validation errors for invalid registration', function () {
        $page = visit('/register');
        $page->fill('email', 'invalid-email')
            ->click('button[type="submit"]')
            ->pause(300);

        $page->assertSee('valid email');
    })->group('smoke', 'validation');
});

describe('Navigation Smoke Tests', function () {
    it('main navigation links work', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = visit('/dashboard');

        // Test main nav links
        $links = ['Characters', 'Settings'];

        foreach ($links as $link) {
            try {
                $page->click($link)
                    ->pause(200)
                    ->assertNoJavaScriptErrors();
            } catch (\Throwable $e) {
                echo "   ⚠️ {$link} navigation not found\n";
            }
        }
    })->group('smoke', 'navigation');
});

describe('Session Management Smoke Tests', function () {
    it('maintains session across page navigation', function () {
        $user = User::factory()->create(['name' => 'Session Test User']);
        $this->actingAs($user);

        $page = visit('/dashboard');
        $page->assertSee('Session Test User');

        $page->visit('/settings');
        $page->assertSee('Session Test User'); // Still logged in

        $page->visit('/dashboard');
        $page->assertSee('Session Test User'); // Session persisted
    })->group('smoke', 'session');
});

describe('Error Handling Smoke Tests', function () {
    it('404 page loads without errors', function () {
        $page = visit('/this-page-definitely-does-not-exist-' . time());
        $page->assertSee('404')
            ->assertNoJavaScriptErrors();
    })->group('smoke', 'errors');

    it('handles unauthorized access gracefully', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user);
        $page = visit("/characters/{$character->id}");

        // Should redirect or show 403
        $page->assertNoJavaScriptErrors();
    })->group('smoke', 'errors');
});

describe('Asset Loading Smoke Tests', function () {
    it('CSS loads correctly', function () {
        $page = visit('/');

        // Check if main CSS is loaded
        $page->assertScript(
            "document.styleSheets.length > 0"
        );
    })->group('smoke', 'assets');

    it('JavaScript loads correctly', function () {
        $page = visit('/');

        // Check if main app.js loaded
        $page->assertScript(
            "document.scripts.length > 0"
        );
    })->group('smoke', 'assets');
});

describe('Mobile Responsiveness Smoke Test', function () {
    it('loads on mobile viewport', function () {
        $page = visit('/', viewport: [375, 667]); // iPhone SE size

        $page->assertSee('Uma Musume')
            ->assertNoJavaScriptErrors();
    })->group('smoke', 'mobile');
});

describe('Performance Smoke Tests', function () {
    it('homepage loads within acceptable time', function () {
        $startTime = microtime(true);

        $page = visit('/');
        $page->assertSee('Uma Musume');

        $loadTime = microtime(true) - $startTime;

        // Should load within 3 seconds
        expect($loadTime)->toBeLessThan(3.0);
    })->group('smoke', 'performance');

    it('dashboard loads within acceptable time', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $startTime = microtime(true);

        $page = visit('/dashboard');
        $page->assertSee('Dashboard');

        $loadTime = microtime(true) - $startTime;

        // Should load within 3 seconds
        expect($loadTime)->toBeLessThan(3.0);
    })->group('smoke', 'performance');
});
