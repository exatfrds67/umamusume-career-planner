<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Privacy-focused data export service.
 *
 * Generates a complete JSON export of all personal data for a user,
 * supporting GDPR-style data portability (Right to Access / Right to Portability).
 *
 * @see \App\Models\User
 */
class DataExportService
{
    /**
     * Generate a complete personal data export for the given user.
     *
     * @return array{
     *     schema_version: string,
     *     exported_at: string,
     *     user: array<string, mixed>,
     *     characters: array<int, array<string, mixed>>,
     *     careers: array<int, array<string, mixed>>,
     *     consent_records: array<int, array<string, mixed>>,
     *     deletion_requests: array<int, array<string, mixed>>,
     *     preferences: array<int, array<string, mixed>>,
     *     ai_conversations: array<int, array<string, mixed>>,
     *     metadata: array{total_characters: int, total_careers: int, export_format: string}
     * }
     */
    public function generateExport(User $user): array
    {
        $user->load([
            'characters.careers.trainingSessions',
            'characters.careers.races',
            'consentRecords',
            'deletionRequests',
            'userPreferences',
            'aiConversations',
        ]);

        return [
            'schema_version' => '1.0.0',
            'exported_at' => now()->toIso8601String(),
            'user' => $this->exportUserProfile($user),
            'characters' => $this->exportCharacters($user),
            'careers' => $this->exportCareers($user),
            'consent_records' => $this->exportConsentRecords($user),
            'deletion_requests' => $this->exportDeletionRequests($user),
            'preferences' => $this->exportPreferences($user),
            'ai_conversations' => $this->exportAiConversations($user),
            'metadata' => [
                'total_characters' => $user->characters->count(),
                'total_careers' => $user->characters->sum(fn ($char) => $char->careers->count()),
                'export_format' => 'json',
            ],
        ];
    }

    /**
     * Generate the export and save it to a file, returning the file path.
     */
    public function generateExportFile(User $user): string
    {
        $data = $this->generateExport($user);
        $filename = "privacy-exports/user-{$user->id}-".now()->format('Y-m-d-His').'.json';

        Storage::put($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '');

        return $filename;
    }

    /**
     * @return array<string, mixed>
     */
    private function exportUserProfile(User $user): array
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid ?? null,
            'name' => $user->name,
            'email' => $user->email,
            'bio' => $user->bio,
            'is_admin' => $user->is_admin,
            'accessibility_settings' => $user->accessibility_settings,
            'notification_preferences' => $user->notification_preferences,
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function exportCharacters(User $user): array
    {
        return $user->characters->map(fn ($character) => [
            'id' => $character->id,
            'name' => $character->name,
            'title' => $character->title ?? null,
            'rarity' => $character->rarity ?? null,
            'created_at' => $character->created_at?->toIso8601String(),
            'updated_at' => $character->updated_at?->toIso8601String(),
        ])->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function exportCareers(User $user): array
    {
        $careers = [];

        foreach ($user->characters as $character) {
            foreach ($character->careers as $career) {
                $careers[] = [
                    'id' => $career->id,
                    'character_id' => $career->character_id,
                    'character_name' => $character->name,
                    'status' => $career->status ?? null,
                    'current_turn' => $career->current_turn ?? null,
                    'training_sessions_count' => $career->trainingSessions->count(),
                    'races_count' => $career->races->count(),
                    'created_at' => $career->created_at?->toIso8601String(),
                    'updated_at' => $career->updated_at?->toIso8601String(),
                ];
            }
        }

        return $careers;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function exportConsentRecords(User $user): array
    {
        return $user->consentRecords->map(fn ($record) => [
            'consent_type' => $record->consent_type->value,
            'granted' => $record->granted,
            'granted_at' => $record->granted_at?->toIso8601String(),
            'revoked_at' => $record->revoked_at?->toIso8601String(),
            'created_at' => $record->created_at->toIso8601String(),
        ])->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function exportDeletionRequests(User $user): array
    {
        return $user->deletionRequests->map(fn ($request) => [
            'status' => $request->status->value,
            'reason' => $request->reason,
            'grace_period_ends_at' => $request->grace_period_ends_at->toIso8601String(),
            'created_at' => $request->created_at->toIso8601String(),
        ])->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function exportPreferences(User $user): array
    {
        return $user->userPreferences->map(fn ($pref) => [
            'key' => $pref->key ?? $pref->preference_key ?? null,
            'value' => $pref->value ?? $pref->preference_value ?? null,
            'created_at' => $pref->created_at?->toIso8601String(),
        ])->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function exportAiConversations(User $user): array
    {
        return $user->aiConversations->map(fn ($conv) => [
            'id' => $conv->id,
            'title' => $conv->title ?? null,
            'model' => $conv->model ?? null,
            'created_at' => $conv->created_at?->toIso8601String(),
        ])->values()->all();
    }
}
