<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Consent Type Enum
 *
 * Defines the categories of user consent that can be granted or revoked.
 * Each type maps to a specific data sharing or processing activity.
 *
 * @see \App\Models\ConsentRecord
 */
enum ConsentType: string
{
    /**
     * Anonymous usage analytics collection.
     */
    case Analytics = 'analytics';

    /**
     * Cloud backup of user data.
     */
    case CloudBackup = 'cloud_backup';

    /**
     * AI processing of career data for advisory features.
     */
    case AiProcessing = 'ai_processing';

    /**
     * Data sharing with umapyoi.net for character data.
     */
    case ExternalUmapyoi = 'external_umapyoi';

    /**
     * Data sharing with UmamusumeDB.com.
     */
    case ExternalUmamusumeDb = 'external_umamusume_db';

    /**
     * Get a human-readable label for the consent type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Analytics => 'Anonymous Analytics',
            self::CloudBackup => 'Cloud Backup',
            self::AiProcessing => 'AI Processing',
            self::ExternalUmapyoi => 'umapyoi.net Integration',
            self::ExternalUmamusumeDb => 'UmamusumeDB.com Integration',
        };
    }

    /**
     * Get a description of what this consent allows.
     */
    public function description(): string
    {
        return match ($this) {
            self::Analytics => 'Allow collection of anonymous usage data to improve the application.',
            self::CloudBackup => 'Allow automatic cloud backup of your career data.',
            self::AiProcessing => 'Allow AI processing of your career data for personalized training advice.',
            self::ExternalUmapyoi => 'Allow data sharing with umapyoi.net for character insights.',
            self::ExternalUmamusumeDb => 'Allow data sharing with UmamusumeDB.com for community features.',
        };
    }
}
