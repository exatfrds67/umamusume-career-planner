<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('SettingsController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'settings@example.com',
            'password' => Hash::make('password'),
        ]);
    });

    // -------------------------------------------------------------------------
    // GET /settings
    // -------------------------------------------------------------------------
    it('shows the settings page for authenticated users', function () {
        $this->actingAs($this->user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertViewIs('settings.index')
            ->assertViewHas('user', $this->user);
    });

    it('redirects unauthenticated users from the settings page', function () {
        $this->get(route('settings.index'))
            ->assertRedirect(route('welcome'));
    });

    // -------------------------------------------------------------------------
    // PUT /settings
    // -------------------------------------------------------------------------
    it('updates the user profile fields', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Settings saved successfully.']);

        $this->user->refresh();
        expect($this->user->name)->toBe('Updated Name');
        expect($this->user->email)->toBe('updated@example.com');
    });

    it('updates preferences JSON field', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'preferences' => [
                    'theme' => 'dark',
                    'language' => 'ja',
                    'timezone' => 'UTC',
                    'analytics' => false,
                ],
            ])
            ->assertOk();

        $this->user->refresh();
        expect($this->user->preferences['theme'])->toBe('dark');
        expect($this->user->preferences['language'])->toBe('ja');
    });

    it('merges preferences without overwriting existing keys', function () {
        $this->user->update(['preferences' => ['theme' => 'light', 'language' => 'en', 'font_size' => 120]]);

        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'preferences' => ['theme' => 'dark'],
            ])
            ->assertOk();

        $this->user->refresh();
        expect($this->user->preferences['theme'])->toBe('dark');
        expect($this->user->preferences['language'])->toBe('en');
        expect($this->user->preferences['font_size'])->toBe(120);
    });

    it('updates ai_settings JSON field', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'ai_settings' => [
                    'provider' => 'bedrock',
                    'model' => 'claude',
                ],
            ])
            ->assertOk();

        $this->user->refresh();
        expect($this->user->ai_settings['provider'])->toBe('bedrock');
    });

    it('updates accessibility_settings JSON field', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'accessibility_settings' => [
                    'high_contrast' => true,
                    'reduced_motion' => true,
                ],
            ])
            ->assertOk();

        $this->user->refresh();
        expect($this->user->accessibility_settings['high_contrast'])->toBeTrue();
    });

    it('updates notification_preferences JSON field', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'notification_preferences' => [
                    'training_alerts' => false,
                    'in_app' => true,
                    'sound' => false,
                ],
            ])
            ->assertOk();

        $this->user->refresh();
        expect($this->user->notification_preferences['training_alerts'])->toBeFalse();
    });

    it('rejects an invalid theme value', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'preferences' => ['theme' => 'neon'],
            ])
            ->assertUnprocessable();
    });

    it('rejects an invalid ai provider value', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.update'), [
                'ai_settings' => ['provider' => 'openai_gpt'],
            ])
            ->assertUnprocessable();
    });

    it('rejects unauthenticated update requests', function () {
        $this->putJson(route('settings.update'), ['name' => 'Hacker'])
            ->assertUnauthorized();
    });

    // -------------------------------------------------------------------------
    // PUT /settings/password
    // -------------------------------------------------------------------------
    it('changes the password with the correct current password', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.password'), [
                'current_password' => 'password',
                'password' => 'NewPassword!1',
                'password_confirmation' => 'NewPassword!1',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Password changed successfully.']);

        $this->user->refresh();
        expect(Hash::check('NewPassword!1', $this->user->password))->toBeTrue();
    });

    it('rejects password change with wrong current password', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.password'), [
                'current_password' => 'wrongpassword',
                'password' => 'NewPassword!1',
                'password_confirmation' => 'NewPassword!1',
            ])
            ->assertUnprocessable();
    });

    it('rejects mismatched password confirmation', function () {
        $this->actingAs($this->user)
            ->putJson(route('settings.password'), [
                'current_password' => 'password',
                'password' => 'NewPassword!1',
                'password_confirmation' => 'DifferentPassword!1',
            ])
            ->assertUnprocessable();
    });

    // -------------------------------------------------------------------------
    // GET /settings/export
    // -------------------------------------------------------------------------
    it('exports user data as a downloadable JSON file', function () {
        $response = $this->actingAs($this->user)
            ->get(route('settings.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');

        $data = $response->json();
        expect($data)->toHaveKey('exported_at');
        expect($data)->toHaveKey('user');
        expect($data['user']['email'])->toBe('settings@example.com');
    });

    it('redirects unauthenticated users from export', function () {
        $this->get(route('settings.export'))
            ->assertRedirect(route('welcome'));
    });

    // -------------------------------------------------------------------------
    // DELETE /settings/account
    // -------------------------------------------------------------------------
    it('deletes the account when confirmation is DELETE', function () {
        $userId = $this->user->id;

        $this->actingAs($this->user)
            ->deleteJson(route('settings.account.delete'), [
                'confirmation' => 'DELETE',
            ])
            ->assertOk()
            ->assertJsonStructure(['redirect']);

        expect(User::find($userId))->toBeNull();
    });

    it('rejects account deletion without correct confirmation string', function () {
        $this->actingAs($this->user)
            ->deleteJson(route('settings.account.delete'), [
                'confirmation' => 'delete',
            ])
            ->assertUnprocessable();
    });

    it('rejects account deletion when unauthenticated', function () {
        $this->deleteJson(route('settings.account.delete'), ['confirmation' => 'DELETE'])
            ->assertUnauthorized();
    });
});
