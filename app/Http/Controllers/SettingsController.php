<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\StoreSettingsRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Show the settings page.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('settings.index', compact('user'));
    }

    /**
     * Update the authenticated user's profile and preferences.
     *
     * Accepts a JSON payload that may contain any combination of:
     * - name, email (scalar profile fields)
     * - preferences (theme, language, timezone, …)
     * - ai_settings  (provider, model, limits, …)
     * - accessibility_settings
     * - notification_preferences
     */
    public function update(StoreSettingsRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validated();

        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        if (isset($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }

        if (isset($validated['preferences'])) {
            $existing = $user->preferences ? $user->preferences->getArrayCopy() : [];
            $updateData['preferences'] = array_merge($existing, $validated['preferences']);
        }

        if (isset($validated['ai_settings'])) {
            $existing = $user->ai_settings ? $user->ai_settings->getArrayCopy() : [];
            $updateData['ai_settings'] = array_merge($existing, $validated['ai_settings']);
        }

        if (isset($validated['accessibility_settings'])) {
            $existing = $user->accessibility_settings ? $user->accessibility_settings->getArrayCopy() : [];
            $updateData['accessibility_settings'] = array_merge($existing, $validated['accessibility_settings']);
        }

        if (isset($validated['notification_preferences'])) {
            $existing = $user->notification_preferences ? $user->notification_preferences->getArrayCopy() : [];
            $updateData['notification_preferences'] = array_merge($existing, $validated['notification_preferences']);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Settings saved successfully.',
        ]);
    }

    /**
     * Change the authenticated user's password.
     */
    public function updatePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validated();
        /** @var string $password */
        $password = $validated['password'];

        $user->update([
            'password' => Hash::make($password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Export the user's account data as a downloadable JSON file.
     */
    public function exportData(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['userPreferences']);

        $data = [
            'schema_version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at?->toIso8601String(),
                'preferences' => $user->preferences,
                'accessibility_settings' => $user->accessibility_settings,
                'ai_settings' => $user->ai_settings,
                'notification_preferences' => $user->notification_preferences,
            ],
        ];

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="account-data-'.now()->format('Y-m-d').'.json"');
    }

    /**
     * Delete the authenticated user's account.
     *
     * Requires the user to type "DELETE" as confirmation.
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'confirmation' => ['required', 'string', 'in:DELETE'],
        ], [
            'confirmation.in' => 'Please type DELETE exactly to confirm account deletion.',
        ]);

        /** @var User $user */
        $user = $request->user();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Account deleted successfully.',
            'redirect' => route('welcome'),
        ]);
    }
}
