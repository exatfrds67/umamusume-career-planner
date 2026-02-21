<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\StorageMode;
use App\Http\Controllers\Controller;
use App\Services\LocalStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles local storage mode operations.
 *
 * Provides API endpoints for:
 * - Storage mode detection and switching
 * - Local data validation
 * - Local-to-account data conversion
 * - Draft configuration
 */
class LocalStorageController extends Controller
{
    public function __construct(
        private readonly LocalStorageService $localStorageService,
    ) {}

    /**
     * Get the current storage mode and configuration.
     */
    public function status(Request $request): JsonResponse
    {
        $mode = $this->localStorageService->detectStorageMode($request);

        return response()->json([
            'success' => true,
            'data' => [
                'mode' => $mode->value,
                'label' => $mode->label(),
                'description' => $mode->description(),
                'supports_offline' => $mode->supportsOffline(),
                'requires_auth' => $mode->requiresAuth(),
                'draft_config' => $this->localStorageService->getDraftConfig(),
            ],
        ]);
    }

    /**
     * Switch the storage mode.
     */
    public function switchMode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:local,account'],
        ]);

        $mode = StorageMode::from($validated['mode']);

        // Switching to account mode requires authentication
        if ($mode === StorageMode::ACCOUNT && ! $request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required to switch to account mode.',
            ], 401);
        }

        $this->localStorageService->setStorageMode($request, $mode);

        return response()->json([
            'success' => true,
            'data' => [
                'mode' => $mode->value,
                'label' => $mode->label(),
            ],
        ]);
    }

    /**
     * Validate local storage data before conversion.
     */
    public function validateData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data' => ['required', 'array'],
            'data.uuid' => ['required', 'string'],
            'data.data' => ['required', 'array'],
        ]);

        $result = $this->localStorageService->validateLocalData($validated['data']);

        return response()->json([
            'success' => $result['valid'],
            'data' => $result,
        ]);
    }

    /**
     * Convert local data to account mode (persist to database).
     */
    public function convert(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required to convert local data.',
            ], 401);
        }

        $validated = $request->validate([
            'characters' => ['required', 'array', 'min:1', 'max:50'],
            'characters.*.uuid' => ['required', 'string'],
            'characters.*.data' => ['required', 'array'],
        ]);

        $result = $this->localStorageService->batchConvertToAccount(
            $user,
            $validated['characters'],
        );

        return response()->json([
            'success' => $result['converted'] > 0,
            'data' => $result,
        ]);
    }

    /**
     * Generate a new UUID for local mode entities.
     */
    public function generateUuid(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'uuid' => $this->localStorageService->generateUuid(),
            ],
        ]);
    }
}
