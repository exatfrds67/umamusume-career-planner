<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Character;
use Illuminate\Database\Eloquent\Collection;

class EloquentCharacterRepository implements CharacterRepositoryInterface
{
    public function findById(int $id): ?Character
    {
        return Character::with(['aptitudes', 'skills', 'factors'])->find($id);
    }

    /**
     * @return Collection<int, Character>
     */
    public function findByUserId(int $userId): Collection
    {
        return Character::where('user_id', $userId)->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function store(array $attributes): Character
    {
        return Character::create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Character $character, array $attributes): bool
    {
        return $character->update($attributes);
    }

    public function delete(int $id): bool
    {
        return Character::destroy($id) > 0;
    }
}
