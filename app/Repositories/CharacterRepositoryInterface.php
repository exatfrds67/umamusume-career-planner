<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Character;
use Illuminate\Database\Eloquent\Collection;

interface CharacterRepositoryInterface
{
    public function findById(int $id): ?Character;

    /**
     * @return Collection<int, Character>
     */
    public function findByUserId(int $userId): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function store(array $attributes): Character;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Character $character, array $attributes): bool;

    public function delete(int $id): bool;
}
