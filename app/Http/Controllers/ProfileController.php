<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Character;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(Request $request): View|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Get user statistics
        $stats = [
            'characters_created' => Character::where('user_id', '=', $user->id ?? throw new \Exception('User required'))->count(),
            'training_sessions' => $user->trainingSessions()->count(),
            // Count races where finish_position is not null (completed races)
            'races_completed' => $user->races()->whereNotNull('finish_position')->count(),
        ];

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'user' => $user,
                'stats' => $stats,
            ]);
        }

        return view('profile.show', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Get validated data
        $validated = $request->validated();

        // Only update fields that are present in the request
        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        if (isset($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }

        if (isset($validated['bio'])) {
            $updateData['bio'] = $validated['bio'];
        }

        // Handle nested JSON fields - merge with existing data
        if (isset($validated['preferences'])) {
            $existingPreferences = $user->preferences->getArrayCopy();
            $newPreferences = is_array($validated['preferences']) ? $validated['preferences'] : [];
            $updateData['preferences'] = array_merge($existingPreferences, $newPreferences);
        }

        if (isset($validated['accessibility_settings'])) {
            $existingAccessibility = $user->accessibility_settings->getArrayCopy();
            $newAccessibility = is_array($validated['accessibility_settings']) ? $validated['accessibility_settings'] : [];
            $updateData['accessibility_settings'] = array_merge($existingAccessibility, $newAccessibility);
        }

        if (isset($validated['ai_settings'])) {
            $existingAiSettings = $user->ai_settings->getArrayCopy();
            $newAiSettings = is_array($validated['ai_settings']) ? $validated['ai_settings'] : [];
            $updateData['ai_settings'] = array_merge($existingAiSettings, $newAiSettings);
        }

        if (isset($validated['mcp_settings'])) {
            $existingMcpSettings = $user->mcp_settings->getArrayCopy();
            $newMcpSettings = is_array($validated['mcp_settings']) ? $validated['mcp_settings'] : [];
            $updateData['mcp_settings'] = array_merge($existingMcpSettings, $newMcpSettings);
        }

        // Update user with validated data
        $user->update($updateData);

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's profile information via API.
     */
    public function updateApi(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Update user with validated data
        $user->update($request->validated());

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Change the user's password.
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Update password with hashed value
        $validated = $request->validated();
        /** @var string $password */
        $password = $validated['password'];
        $user->update([
            'password' => Hash::make($password),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Change the user's password via API.
     */
    public function changePasswordApi(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validated();
        /** @var string $password */
        $password = $validated['password'];

        // Update password with hashed value
        $user->update([
            'password' => Hash::make($password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Upload and update the user's avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        /** @var User $user */
        $user = $request->user();

        // Delete old avatar if exists
        if (! empty($user->avatar_path) && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Store new avatar
        $file = $request->file('avatar');
        $path = $file ? $file->store('avatars', 'public') : '';

        // Update user avatar path
        $user->update(['avatar_path' => $path]);

        return response()->json([
            'message' => 'Avatar uploaded successfully.',
            'avatar_url' => $path ? Storage::url($path) : null,
        ]);
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Delete avatar file if exists
        if (! empty($user->avatar_path) && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Clear avatar path from database
        $user->update(['avatar_path' => null]);

        return response()->json([
            'message' => 'Avatar removed successfully.',
        ]);
    }

    /**
     * Export the user's data.
     */
    public function exportData(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = [
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at?->toIso8601String(),
                'preferences' => $user->preferences,
                'accessibility_settings' => $user->accessibility_settings,
                'ai_settings' => $user->ai_settings,
                'mcp_settings' => $user->mcp_settings,
            ],
            // Export all characters with relationships
            'characters' => Character::where('user_id', '=', $user->id ?? throw new \Exception('User required'))
                ->with(['aptitudes', 'supportCards', 'skillAcquisitions'])
                ->get()
                ->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];

        return response()->json($data);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'password' => ['required', 'string', 'current_password'],
            'confirmation' => ['required', 'string', 'in:'.$user->name],
        ], [
            'confirmation.in' => 'Account name does not match. Please type your account name exactly.',
        ]);

        // Delete user's avatar if exists
        if (! empty($user->avatar_path) && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Delete the user (cascade will handle related records)
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome')
            ->with('success', 'Your account has been deleted successfully.');
    }

    /**
     * Delete the user's account via API.
     */
    public function destroyApi(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'password' => ['required', 'string', 'current_password'],
            'confirmation' => ['required', 'string', 'in:'.$user->name],
        ], [
            'confirmation.in' => 'Account name does not match. Please type your account name exactly.',
        ]);

        // Delete user's avatar if exists
        if (! empty($user->avatar_path) && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete the user (cascade will handle related records)
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully.',
        ]);
    }
}
