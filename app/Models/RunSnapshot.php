<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $career_id
 * @property int|null $turn_number
 * @property string|null $trigger_type
 * @property string|null $description
 * @property array<string, mixed> $snapshot_data
 * @property string|null $checksum
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class RunSnapshot extends Model
{
    /** @use HasFactory<\Database\Factories\RunSnapshotFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'ucp_run_snapshots';

    /** @var list<string> */
    protected $fillable = [
        'career_id',
        'turn_number',
        'trigger_type',
        'description',
        'snapshot_data',
        'checksum',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'career_id' => 'integer',
            'turn_number' => 'integer',
            'snapshot_data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Career, $this>
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }
}
