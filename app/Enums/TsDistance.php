<?php

namespace App\Enums;

enum TsDistance: string
{
    case Sprint = 'sprint';
    case Mile = 'mile';
    case Medium = 'medium';
    case Long = 'long';
    case Dirt = 'dirt';

    public function label(): string
    {
        return match ($this) {
            self::Sprint => 'Sprint',
            self::Mile => 'Mile',
            self::Medium => 'Medium',
            self::Long => 'Long',
            self::Dirt => 'Dirt',
        };
    }
}
