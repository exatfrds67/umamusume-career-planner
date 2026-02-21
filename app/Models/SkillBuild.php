<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillBuild extends Model
{
    /** @use HasFactory<\Database\Factories\SkillBuildFactory> */
    use HasFactory;

    protected $table = 'ucp_skill_builds';

    protected $fillable = [
        'user_id',
        'character_id',
        'name',
        'category',
        'meta_tier',
        'description',
        'skill_ids',
        'total_sp_cost',
        'optimized_cost',
        'tags',
        'is_template',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'skill_ids' => 'array',
            'tags' => 'array',
            'is_template' => 'boolean',
            'total_sp_cost' => 'integer',
            'optimized_cost' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the skills included in this build.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Skill>
     */
    public function skills(): \Illuminate\Database\Eloquent\Collection
    {
        $ids = $this->skill_ids ?? [];

        return Skill::whereIn('id', $ids)->get();
    }

    /**
     * Calculate potential SP savings from hints.
     */
    public function potentialSavings(): int
    {
        return $this->total_sp_cost - $this->optimized_cost;
    }
}
