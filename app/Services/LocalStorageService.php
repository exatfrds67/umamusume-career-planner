<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StorageMode;
use App\Models\Character;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Local Storage Service
 *
 * Manages the dual storage mode architecture:
 * - Local mode: data stored in browser localStorage with UUID identifiers
 * - Account mode: data stored in database with numeric IDs
 *
 * Handles storage mode detection, UUID generation/validation,
 * and local-to-account data conversion.
 *
 * @see \App\Enums\StorageMode
 */
class LocalStorageService
{
    /**
     * Detect the current storage mode from the request context.
     */
    public function detectStorageMode(?Request $request = null): StorageMode
    {
        return StorageMode::fromRequest($request);
    }

    /**
     * Set the storage mode in the session.
     */
    public function setStorageMode(Request $request, StorageMode $mode): void
    {
        $request->session()->put('storage_mode', $mode->value);
    }

    /**
     * Generate a new UUID for local mode entities.
     */
    public function generateUuid(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Validate that a given string is a proper UUID.
     */
    public function isValidUuid(string $value): bool
    {
        return StorageMode::isUuid($value);
    }

    /**
     * Build the data structure expected by the frontend localStorage manager.
     *
     * @param  array<string, mixed>  $characterData
     * @return array<string, mixed>
     */
    public function buildLocalStoragePayload(array $characterData): array
    {
        return [
            'uuid' => $characterData['uuid'] ?? $this->generateUuid(),
            'version' => 1,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
            'data' => $characterData,
            'checksum' => $this->calculateChecksum($characterData),
        ];
    }

    /**
     * Validate data coming from localStorage (treated as untrusted per AGENTS.md).
     *
     * @param  array<string, mixed>  $data
     * @return array{valid: bool, errors: string[]}
     */
    public function validateLocalData(array $data): array
    {
        $errors = [];

        $uuid = $data['uuid'] ?? null;
        if (! is_string($uuid) || ! $this->isValidUuid($uuid)) {
            $errors[] = 'Invalid or missing UUID.';
        }

        if (empty($data['data']) || ! is_array($data['data'])) {
            $errors[] = 'Missing data payload.';

            return [
                'valid' => false,
                'errors' => $errors,
            ];
        }

        /** @var array<string, mixed> $payload */
        $payload = $data['data'];

        if (isset($payload['name']) && is_string($payload['name']) && strlen($payload['name']) > 255) {
            $errors[] = 'Character name exceeds maximum length.';
        }

        $statFields = ['speed', 'stamina', 'power', 'guts', 'wit'];
        foreach ($statFields as $stat) {
            if (isset($payload[$stat]) && is_numeric($payload[$stat])) {
                $value = (int) $payload[$stat];
                if ($value < 0 || $value > 2000) {
                    $errors[] = "Stat '{$stat}' out of valid range (0-2000).";
                }
            }
        }

        if (isset($data['checksum']) && is_string($data['checksum'])) {
            $expected = $this->calculateChecksum($payload);
            if ($data['checksum'] !== $expected) {
                $errors[] = 'Data integrity check failed (checksum mismatch).';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Convert local mode data to account mode (persist to database).
     *
     * @param  array<string, mixed>  $localData
     * @return array{success: bool, character_id: int|null, errors: string[]}
     */
    public function convertToAccount(User $user, array $localData): array
    {
        $validation = $this->validateLocalData($localData);
        if (! $validation['valid']) {
            return [
                'success' => false,
                'character_id' => null,
                'errors' => $validation['errors'],
            ];
        }

        /** @var array<string, mixed> $data */
        $data = $localData['data'];

        $duplicate = $this->findDuplicate($user, $data);
        if ($duplicate) {
            return [
                'success' => false,
                'character_id' => $duplicate->id,
                'errors' => ["A character with this name already exists (ID: {$duplicate->id})."],
            ];
        }

        try {
            return DB::transaction(function () use ($user, $data, $localData) {
                $character = Character::create([
                    'user_id' => $user->id,
                    'name' => $data['name'] ?? 'Unnamed Character',
                    'speed' => $data['speed'] ?? 0,
                    'stamina' => $data['stamina'] ?? 0,
                    'power' => $data['power'] ?? 0,
                    'guts' => $data['guts'] ?? 0,
                    'wit' => $data['wit'] ?? 0,
                    'status' => 'active',
                    'scenario_type' => $data['scenario_type'] ?? null,
                    'current_turn' => $data['current_turn'] ?? 1,
                    'energy_level' => $data['energy_level'] ?? 100,
                    'mood_status' => $data['mood_status'] ?? 'normal',
                    'available_sp' => $data['available_sp'] ?? 0,
                    'local_uuid' => $localData['uuid'] ?? null,
                ]);

                return [
                    'success' => true,
                    'character_id' => $character->id,
                    'errors' => [],
                ];
            });
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'character_id' => null,
                'errors' => ['Failed to convert local data: '.$e->getMessage()],
            ];
        }
    }

    /**
     * Batch convert multiple local characters to account mode.
     *
     * @param  array<int, array<string, mixed>>  $localCharacters
     * @return array{converted: int, skipped: int, errors: array<string, string[]>}
     */
    public function batchConvertToAccount(User $user, array $localCharacters): array
    {
        $converted = 0;
        $skipped = 0;
        $errors = [];

        foreach ($localCharacters as $i => $localData) {
            $result = $this->convertToAccount($user, $localData);
            if ($result['success']) {
                $converted++;
            } else {
                $skipped++;
                $errors["item_{$i}"] = $result['errors'];
            }
        }

        return [
            'converted' => $converted,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Get the draft auto-save configuration.
     *
     * @return array{interval_ms: int, max_drafts: int, storage_key: string}
     */
    public function getDraftConfig(): array
    {
        return [
            'interval_ms' => 30000, // 30 seconds per spec
            'max_drafts' => 10,
            'storage_key' => 'ucp_drafts',
        ];
    }

    /**
     * Calculate a simple checksum for data integrity verification.
     *
     * @param  array<string, mixed>  $data
     */
    private function calculateChecksum(array $data): string
    {
        return hash('xxh3', json_encode($data, JSON_THROW_ON_ERROR));
    }

    /**
     * Check if a character with similar data already exists for this user.
     *
     * @param  array<string, mixed>  $data
     */
    private function findDuplicate(User $user, array $data): ?Character
    {
        if (empty($data['name'])) {
            return null;
        }

        return Character::where('user_id', $user->id)
            ->where('name', $data['name'])
            ->first();
    }
}
