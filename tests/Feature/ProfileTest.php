<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);
});

describe('Profile Display', function () {
    it('displays user profile page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertOk()
            ->assertViewIs('profile.show')
            ->assertViewHas('user');
    });

    it('shows user statistics', function () {
        // Create some characters for the user
        Character::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats['characters_created'] === 3;
            });
    });

    it('requires authentication to view profile', function () {
        $response = $this->get(route('profile.show'));

        // App redirects guests to welcome page instead of login
        $response->assertRedirect(route('welcome'));
    });
});

describe('Profile Update', function () {
    it('updates user name', function () {
        $response = $this->actingAs($this->user)
            ->put(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => $this->user->email,
            ]);

        $response->assertRedirect(route('profile.show'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ucp_users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
        ]);
    });

    it('updates user email', function () {
        $response = $this->actingAs($this->user)
            ->put(route('profile.update'), [
                'name' => $this->user->name,
                'email' => 'newemail@example.com',
            ]);

        $response->assertRedirect(route('profile.show'));

        $this->assertDatabaseHas('ucp_users', [
            'id' => $this->user->id,
            'email' => 'newemail@example.com',
        ]);
    });

    it('validates email format', function () {
        $response = $this->actingAs($this->user)
            ->put(route('profile.update'), [
                'name' => $this->user->name,
                'email' => 'invalid-email',
            ]);

        $response->assertSessionHasErrors('email');
    });

    it('validates email uniqueness', function () {
        User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAs($this->user)
            ->put(route('profile.update'), [
                'name' => $this->user->name,
                'email' => 'other@example.com',
            ]);

        $response->assertSessionHasErrors('email');
    });

    it('validates name is required', function () {
        $response = $this->actingAs($this->user)
            ->put(route('profile.update'), [
                'name' => '',
                'email' => $this->user->email,
            ]);

        $response->assertSessionHasErrors('name');
    });
});

describe('Profile Update API', function () {
    it('updates profile via API', function () {
        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/profile', [
                'name' => 'API Updated Name',
                'email' => $this->user->email,
            ]);

        $response->assertSuccessful()
            ->assertJson(['message' => 'Profile updated successfully.']);

        $this->assertDatabaseHas('ucp_users', [
            'id' => $this->user->id,
            'name' => 'API Updated Name',
        ]);
    });
});

describe('Password Change', function () {
    it('changes password with correct current password', function () {
        $response = $this->actingAs($this->user)
            ->put(route('profile.password.change'), [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('profile.show'));

        $this->assertTrue(Hash::check('newpassword123', $this->user->fresh()->password));
    });

    it('rejects password change with incorrect current password', function () {
        $response = $this->actingAs($this->user)
            ->put(route('profile.password.change'), [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
    });

    it('validates password confirmation', function () {
        $response = $this->actingAs($this->user)
            ->put(route('profile.password.change'), [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertSessionHasErrors('password');
    });
});

describe('Avatar Upload', function () {
    it('uploads avatar image', function () {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($this->user)
            ->post(route('profile.avatar'), [
                'avatar' => $file,
            ]);

        $response->assertSuccessful()
            ->assertJsonStructure(['message', 'avatar_url']);

        $this->user->refresh();
        expect($this->user->avatar_path)->not->toBeNull();
    });

    it('validates avatar file type', function () {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->user)
            ->post(route('profile.avatar'), [
                'avatar' => $file,
            ]);

        $response->assertSessionHasErrors('avatar');
    });

    it('validates avatar file size', function () {
        Storage::fake('public');

        // Create a file larger than the limit (2MB)
        $file = UploadedFile::fake()->image('large.jpg')->size(3000);

        $response = $this->actingAs($this->user)
            ->post(route('profile.avatar'), [
                'avatar' => $file,
            ]);

        $response->assertSessionHasErrors('avatar');
    });
});

describe('Profile Export', function () {
    it('exports user data', function () {
        Character::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('profile.export'));

        $response->assertSuccessful()
            ->assertJsonStructure(['user', 'characters', 'exported_at']);
    });

    it('exports data via API', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/profile/export');

        $response->assertSuccessful()
            ->assertJsonStructure(['user', 'exported_at']);
    });
});

describe('Account Deletion', function () {
    it('deletes user account with correct password and confirmation', function () {
        $userId = $this->user->id;

        $response = $this->actingAs($this->user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
                'confirmation' => 'DELETE',
            ]);

        $response->assertRedirect(route('welcome'));

        $this->assertDatabaseMissing('ucp_users', ['id' => $userId]);
    });

    it('requires password confirmation for deletion', function () {
        $response = $this->actingAs($this->user)
            ->delete(route('profile.destroy'), [
                'password' => 'wrongpassword',
                'confirmation' => 'DELETE',
            ]);

        $response->assertSessionHasErrors('password');

        $this->assertDatabaseHas('ucp_users', ['id' => $this->user->id]);
    });

    it('requires DELETE confirmation text', function () {
        $response = $this->actingAs($this->user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
                'confirmation' => 'delete', // lowercase should fail
            ]);

        $response->assertSessionHasErrors('confirmation');

        $this->assertDatabaseHas('ucp_users', ['id' => $this->user->id]);
    });
});
