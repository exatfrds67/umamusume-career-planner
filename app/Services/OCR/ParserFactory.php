<?php

declare(strict_types=1);

namespace App\Services\OCR;

use App\Services\OCR\Parsers\CharacterStatsParser;
use App\Services\OCR\Parsers\RaceResultParser;
use App\Services\OCR\Parsers\ScreenParserInterface;
use App\Services\OCR\Parsers\SkillListParser;
use App\Services\OCR\Parsers\TrainingSessionParser;

/**
 * Parser Factory
 *
 * Creates appropriate parser instances based on detected screen type.
 *
 * Requirements: Task 5.1.3, Requirement 23.3
 */
class ParserFactory
{
    /**
     * Parser instances cache
     *
     * @var array<string, ScreenParserInterface>
     */
    protected array $parsers = [];

    /**
     * Get parser for screen type
     */
    public function getParser(string $screenType): ?ScreenParserInterface
    {
        // Return cached parser if available
        if (isset($this->parsers[$screenType])) {
            return $this->parsers[$screenType];
        }

        // Create new parser instance
        $parser = match ($screenType) {
            'character_stats' => new CharacterStatsParser,
            'training_session' => new TrainingSessionParser,
            'race_result' => new RaceResultParser,
            'skill_list' => new SkillListParser,
            default => null,
        };

        // Cache parser instance
        if ($parser) {
            $this->parsers[$screenType] = $parser;
        }

        return $parser;
    }

    /**
     * Get all available parsers
     *
     * @return array<string, ScreenParserInterface>
     */
    public function getAllParsers(): array
        $types = ['character_stats', 'training_session', 'race_result', 'skill_list'];

        foreach ($types as $type) {
            if (! isset($this->parsers[$type])) {
                $this->getParser($type);
            }
        }

        return $this->parsers;
    }

    /**
     * Get supported screen types
     *
     * @return array<string>
     */
    public function getSupportedTypes(): array
        return ['character_stats', 'training_session', 'race_result', 'skill_list'];
    }
}
