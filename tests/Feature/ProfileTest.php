<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('displays the profile page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('profile.show'));

    $response->assertSuccessful();
    $response->assertViewIs('profile.show');
    $response->assertViewHas('user', $user);
});

it('updates user profile information', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'preferences' => [
            'theme' => 'dark',
            'language' => 'en',
        ],
    ]);

    $response->assertRedirect(route('profile.show'));
    $response->assertSessionHas('success');

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
    expect($user->preferences['theme'])->toBe('dark');
});

it('validates profile update data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => '',
        'email' => 'invalid-email',
    ]);

    $response->assertSessionHasErrors(['name', 'email']);
});

it('prevents duplicate email addresses', function () {
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);
    $user = User::factory()->create(['email' => 'user@example.com']);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => 'existing@example.com',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('allows user to keep their own email', function () {
    $user = User::factory()->create(['email' => 'user@example.com']);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Updated Name',
        'email' => 'user@example.com',
    ]);

    $response->assertRedirect(route('profile.show'));
    $response->assertSessionHasNoErrors();
});

it('changes user password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)->put(route('profile.password.change'), [
        'current_password' => 'old-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect(route('profile.show'));
    $response->assertSessionHas('success');

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

it('validates current password when changing password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    $response = $this->actingAs($user)->put(route('profile.password.change'), [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors(['current_password']);
});

it('requires password confirmation', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)->put(route('profile.password.change'), [
        'current_password' => 'old-password',
        'password' => 'new-password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors(['password']);
});

it('uploads user avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->actingAs($user)->post(route('profile.avatar'), [
        'avatar' => $file,
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure(['message', 'avatar_url']);

    $user->refresh();
    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});

it('validates avatar file type', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 100);

    $response = $this->actingAs($user)->post(route('profile.avatar'), [
        'avatar' => $file,
    ]);

    $response->assertSessionHasErrors(['avatar']);
});

it('exports user data', function () {
    $user = User::factory()->create([
        'preferences' => ['theme' => 'dark'],
        'ai_settings' => ['enable_cloud_ai' => true],
    ]);

    $response = $this->actingAs($user)->get(route('profile.export'));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'user' => ['uuid', 'name', 'email', 'preferences', 'ai_settings'],
        'characters',
        'exported_at',
    ]);
});

it('deletes user account with confirmation', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)->delete(route('profile.destroy'), [
        'password' => 'password',
        'confirmation' => 'DELETE',
    ]);

    $response->assertRedirect(route('welcome'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('ucp_users', ['id' => $user->id]);
});

it('requires password confirmation for account deletion', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)->delete(route('profile.destroy'), [
        'password' => 'wrong-password',
        'confirmation' => 'DELETE',
    ]);

    $response->assertSessionHasErrors(['password']);
    $this->assertDatabaseHas('ucp_users', ['id' => $user->id]);
});

it('requires DELETE confirmation for account deletion', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)->delete(route('profile.destroy'), [
        'password' => 'password',
        'confirmation' => 'WRONG',
    ]);

    $response->assertSessionHasErrors(['confirmation']);
    $this->assertDatabaseHas('ucp_users', ['id' => $user->id]);
});

it('updates profile via API', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->putJson(route('api.v1.profile.update'), [
        'name' => 'API Updated Name',
        'email' => $user->email,
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure(['message', 'user']);

    $user->refresh();
    expect($user->name)->toBe('API Updated Name');
});

it('changes password via API', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user, 'sanctum')->putJson(route('api.v1.profile.password.change'), [
        'current_password' => 'old-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure(['message']);

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

it('deletes account via API', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson(route('api.v1.profile.destroy'), [
        'password' => 'password',
        'confirmation' => 'DELETE',
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure(['message']);

    $this->assertDatabaseMissing('ucp_users', ['id' => $user->id]);
});
