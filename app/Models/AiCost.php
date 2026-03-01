<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $character_id
 * @property string $provider
 * @property string $model
 * @property string|null $request_type
 * @property int $input_tokens
 * @property int $output_tokens
 * @property int $total_tokens
 * @property float $input_cost
 * @property float $output_cost
 * @property float $total_cost
 * @property float|null $response_time
 * @property bool $cached
 * @property string|null $request_summary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Illuminate\Database\Eloquent\Builder<AiCost>
 * @mixin \Illuminate\Database\Query\Builder
 */
class AiCost extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_ai_costs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'character_id',
        'provider',
        'model',
        'request_type',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'input_cost',
        'output_cost',
        'total_cost',
        'response_time',
        'cached',
        'request_summary',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'total_tokens' => 'integer',
            'input_cost' => 'float',
            'output_cost' => 'float',
            'total_cost' => 'float',
            'response_time' => 'float',
            'cached' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this cost record.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the character associated with this cost record.
     *
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
