<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Support Card Model
 * Alias for SupportCardDefinition for easier usage
 *
 * @use HasFactory<\Database\Factories\SupportCardDefinitionFactory>
 */
class SupportCard extends SupportCardDefinition
{
    use HasFactory;
}
