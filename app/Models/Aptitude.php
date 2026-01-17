<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aptitude extends Model
{
    use HasFactory;

    protected $table = 'ucp_aptitudes';

    protected $fillable = [
        'character_id',
        'distance_type',
        'surface_type',
        'running_style',
        'grade',
    ];

    /**
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
