<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('Settings Page (Phase 9)', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'name' => 'Trainer T',
            'email' => 'trainer@uma.test',
            'password' => Hash::make('password'),
        ]);
    });

    // ── Page renders ──────────────────────────────────────────────────────────

    it('renders the full settings page for authenticated users', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertViewIs('settings.index')
            ->assertViewHas('user', $this->user);
    });

    it('redirects unauthenticated users to welcome', function () {
        $this->get(route('settings.index'))
            ->assertRedirect(route('welcome'));
    });

    // ── All 6 sections present ────────────────────────────────────────────────

    it('contains the Storage section', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('Storage Mode')
            ->assertSee('Replay Onboarding');
    });

    it('contains the AI Advisor section', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('Default AI Model')
            ->assertSee('AI Cost Threshold');
    });

    it('contains the Appearance section', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('Theme')
            ->assertSee('Compact Mode')
            ->assertSee('Language');
    });

    it('contains the Notifications section', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('Notifications');
    });

    it('contains the Accessibility section', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('Accessibility');
    });

    it('contains Save Settings and Reset to Defaults buttons', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('Save Settings')
            ->assertSee('Reset to Defaults');
    });

    // ── Storage mode toggle ───────────────────────────────────────────────────

    it('shows Local and Account storage mode buttons', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('Local')
            ->assertSee('Account');
    });

    it('shows conversion CTA data-testid in local mode', function () {
        // Simulate local mode via session
        $this->actingAs($this->user)
            ->withSession(['storage_mode' => 'local'])
            ->get(route('settings.index'))
            ->assertSee('settings-convert-cta', false);
    });

    // ── Account section visibility ────────────────────────────────────────────

    it('shows account section when in account mode', function () {
        // Authenticated users default to account mode
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('settings-account-section', false);
    });

    it('hides account section when in local mode', function () {
        $this->actingAs($this->user)
            ->withSession(['storage_mode' => 'local'])
            ->get(route('settings.index'))
            ->assertDontSee('settings-account-section', false);
    });

    // ── AI model selector ─────────────────────────────────────────────────────

    it('shows AI model selector with bedrock and ollama options', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('AWS Bedrock Claude')
            ->assertSee('Local Ollama');
    });

    it('pre-selects the user saved AI provider', function () {
        $this->user->update(['ai_settings' => ['provider' => 'bedrock']]);

        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('bedrock');
    });

    // ── Theme toggle ──────────────────────────────────────────────────────────

    it('shows Light and Dark theme buttons', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('settings-theme-light', false)
            ->assertSee('settings-theme-dark', false);
    });

    // ── Compact mode toggle ───────────────────────────────────────────────────

    it('shows compact mode toggle', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('settings-compact-mode-toggle', false);
    });

    // ── Replay onboarding button ──────────────────────────────────────────────

    it('shows replay onboarding button', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertSee('settings-replay-onboarding', false);
    });

    // ── Save + Reset via PUT /settings ────────────────────────────────────────

    it('saves theme preference via PUT', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'preferences' => ['theme' => 'dark'],
            ])
            ->assertOk()
            ->assertJson(['message' => 'Settings saved successfully.']);

        $this->user->refresh();
        expect($this->user->preferences['theme'])->toBe('dark');
    });

    it('saves compact_mode preference via PUT', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'preferences' => ['compact_mode' => true],
            ])
            ->assertOk();

        $this->user->refresh();
        expect($this->user->preferences['compact_mode'])->toBeTrue();
    });

    it('saves language preference via PUT', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'preferences' => ['language' => 'ja'],
            ])
            ->assertOk();

        $this->user->refresh();
        expect($this->user->preferences['language'])->toBe('ja');
    });

    it('saves AI provider via PUT', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'ai_settings' => ['provider' => 'bedrock'],
            ])
            ->assertOk();

        $this->user->refresh();
        expect($this->user->ai_settings['provider'])->toBe('bedrock');
    });

    it('saves AI cost limit via PUT', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'ai_settings' => ['budget_limit' => 0.25],
            ])
            ->assertOk();

        $this->user->refresh();
        expect((float) $this->user->ai_settings['budget_limit'])->toBe(0.25);
    });

    // ── Conversion CTA link ───────────────────────────────────────────────────

    it('conversion CTA links to data.index route', function () {
        $this->actingAs($this->user)
            ->withSession(['storage_mode' => 'local'])
            ->get(route('settings.index'))
            ->assertSee(route('data.index'), false);
    });
});
