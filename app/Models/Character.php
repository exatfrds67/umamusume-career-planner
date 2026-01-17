<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @use HasFactory<\Database\Factories\CharacterFactory>
 */
class Character extends Model
{
    use HasFactory;

    protected $table = 'ucp_characters';

    protected $fillable = [
        'user_id',
        'uuid',
        'name',
        'avatar_url',
        'scenario_type',
        'career_stage',
        'current_turn',
        'current_stats',
        'stat_priorities',
        'stat_breakpoints',
        'energy_level',
        'mood_status',
        'conditions',
        'days_until_race',
        'goals',
        'race_schedule',
        'training_plan',
        'growth_rates',
        'inherited_factors',
        'legacy_parents',
        'team_composition',
        'facility_levels',
        'spirit_burst_data',
        'status',
        'completion_data',
    ];

    protected function casts(): array
    {
        return [
            'current_stats' => 'array',
            'stat_priorities' => 'array',
            'stat_breakpoints' => 'array',
            'conditions' => 'array',
            'goals' => 'array',
            'race_schedule' => 'array',
            'training_plan' => 'array',
            'growth_rates' => 'array',
            'inherited_factors' => 'array',
            'legacy_parents' => 'array',
            'team_composition' => 'array',
            'facility_levels' => 'array',
            'spirit_burst_data' => 'array',
            'completion_data' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($character) {
            if (empty($character->uuid)) {
                $character->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Aptitude, $this>
     */
    public function aptitudes(): HasMany
    {
        return $this->hasMany(Aptitude::class);
    }

    /**
     * @return HasMany<Factor, $this>
     */
    public function factors(): HasMany
    {
        return $this->hasMany(Factor::class);
    }

    /**
     * @return HasMany<SkillAcquisition, $this>
     */
    public function skillAcquisitions(): HasMany
    {
        return $this->hasMany(SkillAcquisition::class);
    }

    /**
     * @return HasMany<Career, $this>
     */
    public function careers(): HasMany
    {
        return $this->hasMany(Career::class);
    }

    /**
     * @return HasMany<CharacterSupportCard, $this>
     */
    public function supportCards(): HasMany
    {
        return $this->hasMany(CharacterSupportCard::class);
    }

    // Helper methods for stat access
    public function getStat(string $stat): int
    {
        return $this->current_stats[$stat] ?? 0;
    }

    public function getStatGrade(int $statValue): string
    {
        return match (true) {
            $statValue >= 1200 => 'SS',
            $statValue >= 1100 => 'S',
            $statValue >= 1000 => 'A+',
            $statValue >= 900 => 'A',
            $statValue >= 800 => 'B+',
            $statValue >= 700 => 'B',
            $statValue >= 600 => 'C+',
            $statValue >= 500 => 'C',
            $statValue >= 400 => 'D+',
            $statValue >= 300 => 'D',
            $statValue >= 200 => 'E+',
            $statValue >= 100 => 'E',
            $statValue >= 50 => 'F',
            default => 'G+',
        };
    }

    public function getProgressPercentage(): float
    {
        if (empty($this->goals['target_stats'])) {
            return 0;
        }

        $stats = ['speed', 'stamina', 'power', 'guts', 'wit'];
        $totalProgress = 0;
        $statCount = 0;

        foreach ($stats as $stat) {
            if (isset($this->goals['target_stats'][$stat]) && $this->goals['target_stats'][$stat] > 0) {
                $current = $this->getStat($stat);
                $target = $this->goals['target_stats'][$stat];
                $progress = min(100, ($current / $target) * 100);
                $totalProgress += $progress;
                $statCount++;
            }
        }

        return $statCount > 0 ? round($totalProgress / $statCount, 1) : 0;
    }
}
