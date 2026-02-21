<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'results' => [],
            ]);
        }

        $user = $request->user();
        $results = [];

        if ($user) {
            $characters = Character::query()
                ->where('user_id', $user->id)
                ->where('name', 'like', '%'.$query.'%')
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'name']);

            $results = $characters->map(fn (Character $character): array => [
                'type' => 'Character',
                'id' => $character->id,
                'name' => $character->name,
            ])->all();
        }

        return response()->json([
            'results' => $results,
        ]);
    }
}
