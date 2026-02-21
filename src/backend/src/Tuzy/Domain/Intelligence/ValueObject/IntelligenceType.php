<?php

declare(strict_types=1);

namespace Tuzy\Domain\Intelligence\ValueObject;

enum IntelligenceType: string
{
    case CHARACTER_OBSERVATION = 'character_observation';
    case ENVIRONMENTAL_SCAN = 'environmental_scan';
    case EVENT_ANALYSIS = 'event_analysis';
    case FACTION_MONITORING = 'faction_monitoring';
    case MYTH_ANALYSIS = 'myth_analysis';
    case PATTERN_DETECTION = 'pattern_detection';
    case THREAT_ASSESSMENT = 'threat_assessment';
    case OPPORTUNITY_IDENTIFICATION = 'opportunity_identification';
    case RESOURCE_TRACKING = 'resource_tracking';
    case PREDICTION = 'prediction';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::CHARACTER_OBSERVATION => 'Character Observation',
            self::ENVIRONMENTAL_SCAN => 'Environmental Scan',
            self::EVENT_ANALYSIS => 'Event Analysis',
            self::FACTION_MONITORING => 'Faction Monitoring',
            self::MYTH_ANALYSIS => 'Myth Analysis',
            self::PATTERN_DETECTION => 'Pattern Detection',
            self::THREAT_ASSESSMENT => 'Threat Assessment',
            self::OPPORTUNITY_IDENTIFICATION => 'Opportunity Identification',
            self::RESOURCE_TRACKING => 'Resource Tracking',
            self::PREDICTION => 'Prediction',
        };
    }

    public function getPriority(): int
    {
        return match ($this) {
            self::THREAT_ASSESSMENT => 1,
            self::EVENT_ANALYSIS => 2,
            self::CHARACTER_OBSERVATION => 3,
            self::FACTION_MONITORING => 4,
            self::ENVIRONMENTAL_SCAN => 5,
            self::OPPORTUNITY_IDENTIFICATION => 6,
            self::RESOURCE_TRACKING => 7,
            self::MYTH_ANALYSIS => 8,
            self::PATTERN_DETECTION => 9,
            self::PREDICTION => 10,
        };
    }

    public function getReliabilityWeight(): float
    {
        return match ($this) {
            self::ENVIRONMENTAL_SCAN => 0.9,
            self::EVENT_ANALYSIS => 0.8,
            self::CHARACTER_OBSERVATION => 0.7,
            self::FACTION_MONITORING => 0.6,
            self::RESOURCE_TRACKING => 0.7,
            self::THREAT_ASSESSMENT => 0.8,
            self::OPPORTUNITY_IDENTIFICATION => 0.6,
            self::MYTH_ANALYSIS => 0.4,
            self::PATTERN_DETECTION => 0.5,
            self::PREDICTION => 0.3,
        };
    }

    public function isActionable(): bool
    {
        return match ($this) {
            self::THREAT_ASSESSMENT,
            self::OPPORTUNITY_IDENTIFICATION,
            self::EVENT_ANALYSIS => true,
            default => false
        };
    }

    public function getDecayRate(): float
    {
        return match ($this) {
            self::CHARACTER_OBSERVATION => 0.05,
            self::ENVIRONMENTAL_SCAN => 0.03,
            self::EVENT_ANALYSIS => 0.04,
            self::FACTION_MONITORING => 0.06,
            self::MYTH_ANALYSIS => 0.02,
            self::PATTERN_DETECTION => 0.08,
            self::THREAT_ASSESSMENT => 0.07,
            self::OPPORTUNITY_IDENTIFICATION => 0.09,
            self::RESOURCE_TRACKING => 0.05,
            self::PREDICTION => 0.10,
        };
    }
}
