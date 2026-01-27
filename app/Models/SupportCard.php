<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Support Card Model
 * Alias for SupportCardDefinition for easier usage
 *
 * @property \Illuminate\Database\Eloquent\Relations\Pivot|null $pivot
 *
 * @use HasFactory<\Database\Factories\SupportCardDefinitionFactory>
 */
class SupportCard extends SupportCardDefinition
{
    /** @use HasFactory<\Database\Factories\SupportCardDefinitionFactory> */
    use HasFactory;
}
