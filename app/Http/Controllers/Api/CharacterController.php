<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Character\StoreCharacterRequest;
use App\Http\Requests\Character\UpdateCharacterRequest;
use App\Models\Character;
use App\Repositories\CharacterRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    public function __construct(
        protected CharacterRepositoryInterface $repository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json([
                'message' => 'Authentication required.',
            ], 401);
        }

        $userId = $user->id ?? throw new \Exception('User required');
        $characters = $this->repository->findByUserId($userId);

        return response()->json($characters);
    }

    public function store(StoreCharacterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        if ($user === null) {
            return response()->json([
                'message' => 'Authentication required.',
            ], 401);
        }

        $data['user_id'] = $user->id ?? throw new \Exception('User required');

        $character = $this->repository->store($data);

        return response()->json($character, 201);
    }

    public function show(Character $character): JsonResponse
    {
        $this->authorize('view', $character);

        return response()->json($character->load(['aptitudes', 'skills', 'factors']));
    }

    public function update(UpdateCharacterRequest $request, Character $character): JsonResponse
    {
        $this->authorize('update', $character);

        $this->repository->update($character, $request->validated());

        return response()->json($character->fresh());
    }

    public function destroy(Character $character): JsonResponse
    {
        $this->authorize('delete', $character);

        $this->repository->delete($character->id);

        return response()->json(null, 204);
    }
}
