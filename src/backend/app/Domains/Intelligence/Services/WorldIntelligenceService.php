<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\World\Aggregates\WorldAggregate;
use App\Domains\Character\Aggregates\CharacterSurvivalAggregate;
use WorldOS\Blueprint\Domain\Legacy\Event\ShockEvent;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceReport;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceSource;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceType;
use App\Domains\Intelligence\Repositories\IntelligenceRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class WorldIntelligenceService
{
    private const INTELLIGENCE_DECAY_RATE = 0.05; // 5% decay per tick
    private const MAX_INTELLIGENCE_AGE = 50; // ticks
    private const INTELLIGENCE_ACCURACY_DECAY = 0.02; // 2% accuracy loss per tick

    public function __construct(
        private readonly IntelligenceAnalyzer $analyzer,
        private readonly IntelligenceRepository $repository,
    ) {}

    public function gatherIntelligence(
        WorldAggregate $world,
        Collection $characters,
        Collection $activeEvents
    ): IntelligenceCollection {
        
        $collection = new IntelligenceCollection($world->id());

        // Gather from multiple sources
        $this->gatherFromCharacters($collection, $characters);
        $this->gatherFromEnvironment($collection, $world);
        $this->gatherFromEvents($collection, $activeEvents);
        $this->gatherFromFactions($collection, $world);
        $this->gatherFromMyths($collection, $world);

        // Analyze and process
        $this->processIntelligence($collection);

        Log::info('Intelligence gathered', [
            'world_id' => $world->id(),
            'total_intelligence' => $collection->count(),
            'sources' => $collection->getSourceBreakdown(),
        ]);

        return $collection;
    }

    private function gatherFromCharacters(
        IntelligenceCollection $collection,
        Collection $characters
    ): void {
        
        foreach ($characters as $character) {
            if (!$character->isAlive()) {
                continue;
            }

            // Character observations
            $observations = $this->extractCharacterObservations($character);
            
            foreach ($observations as $observation) {
                $intelligence = new IntelligenceReport(
                    id: uniqid('int_', true),
                    type: IntelligenceType::CHARACTER_OBSERVATION,
                    source: new IntelligenceSource(
                        type: 'character',
                        id: $character->characterId(),
                        reliability: $this->calculateCharacterReliability($character)
                    ),
                    content: $observation['content'],
                    metadata: $observation['metadata'],
                    timestamp: now(),
                    accuracy: $observation['accuracy'] ?? 0.8,
                    age: 0
                );

                $collection->add($intelligence);
            }
        }
    }

    private function gatherFromEnvironment(
        IntelligenceCollection $collection,
        WorldAggregate $world
    ): void {
        
        $environmentalData = $this->extractEnvironmentalData($world);
        
        foreach ($environmentalData as $data) {
            $intelligence = new IntelligenceReport(
                id: uniqid('int_', true),
                type: IntelligenceType::ENVIRONMENTAL_SCAN,
                source: new IntelligenceSource(
                    type: 'environment',
                    id: $world->id(),
                    reliability: 0.9 // Environmental data is usually reliable
                ),
                content: $data['content'],
                metadata: $data['metadata'],
                timestamp: now(),
                accuracy: 0.85,
                age: 0
            );

            $collection->add($intelligence);
        }
    }

    private function gatherFromEvents(
        IntelligenceCollection $collection,
        Collection $activeEvents
    ): void {
        
        foreach ($activeEvents as $event) {
            $eventIntelligence = $this->extractEventIntelligence($event);
            
            foreach ($eventIntelligence as $intel) {
                $intelligence = new IntelligenceReport(
                    id: uniqid('int_', true),
                    type: IntelligenceType::EVENT_ANALYSIS,
                    source: new IntelligenceSource(
                        type: 'event',
                        id: $event->id(),
                        reliability: $this->calculateEventReliability($event)
                    ),
                    content: $intel['content'],
                    metadata: array_merge($intel['metadata'], ['event_id' => $event->id()]),
                    timestamp: now(),
                    accuracy: $intel['accuracy'] ?? 0.9,
                    age: 0
                );

                $collection->add($intelligence);
            }
        }
    }

    private function gatherFromFactions(
        IntelligenceCollection $collection,
        WorldAggregate $world
    ): void {
        
        $factionData = $this->extractFactionIntelligence($world);
        
        foreach ($factionData as $faction) {
            $intelligence = new IntelligenceReport(
                id: uniqid('int_', true),
                type: IntelligenceType::FACTION_MONITORING,
                source: new IntelligenceSource(
                    type: 'faction',
                    id: $faction['id'],
                    reliability: $faction['reliability'] ?? 0.7
                ),
                content: $faction['content'],
                metadata: $faction['metadata'],
                timestamp: now(),
                accuracy: $faction['accuracy'] ?? 0.75,
                age: 0
            );

            $collection->add($intelligence);
        }
    }

    private function gatherFromMyths(
        IntelligenceCollection $collection,
        WorldAggregate $world
    ): void {
        
        $mythData = $this->extractMythIntelligence($world);
        
        foreach ($mythData as $myth) {
            $intelligence = new IntelligenceReport(
                id: uniqid('int_', true),
                type: IntelligenceType::MYTH_ANALYSIS,
                source: new IntelligenceSource(
                    type: 'myth',
                    id: $myth['id'],
                    reliability: $myth['reliability'] ?? 0.6 // Myths are less reliable
                ),
                content: $myth['content'],
                metadata: $myth['metadata'],
                timestamp: now(),
                accuracy: $myth['accuracy'] ?? 0.6,
                age: 0
            );

            $collection->add($intelligence);
        }
    }

    private function extractCharacterObservations(CharacterSurvivalAggregate $character): array
    {
        $observations = [];
        $riskFactors = $character->riskFactors();

        // Health observations
        if ($riskFactors->injuryState() > 0.5) {
            $observations[] = [
                'content' => "Character {$character->characterId()} shows signs of severe injury",
                'metadata' => [
                    'injury_level' => $riskFactors->injuryState(),
                    'location' => 'unknown',
                    'urgency' => 'high'
                ],
                'accuracy' => 0.9
            ];
        }

        // Behavioral observations
        if ($character->plotArmorFactor() < 0.5) {
            $observations[] = [
                'content' => "Character {$character->characterId()} exhibiting unusual vulnerability patterns",
                'metadata' => [
                    'plot_armor' => $character->plotArmorFactor(),
                    'behavior_type' => 'vulnerable',
                    'risk_level' => 'elevated'
                ],
                'accuracy' => 0.8
            ];
        }

        // Environmental awareness
        if ($riskFactors->environmentalDanger() > 0.6) {
            $observations[] = [
                'content' => "Character {$character->characterId()} detected significant environmental threats",
                'metadata' => [
                    'danger_level' => $riskFactors->environmentalDanger(),
                    'threat_types' => ['natural', 'supernatural'],
                    'survival_instinct' => 'active'
                ],
                'accuracy' => 0.7
            ];
        }

        return $observations;
    }

    private function extractEnvironmentalData(WorldAggregate $world): array
    {
        $data = [];
        $entropy = $world->currentEntropy()->value();

        // Entropy analysis
        $data[] = [
            'content' => "World entropy at {$entropy} - " . $this->getEntropyLevel($entropy),
            'metadata' => [
                'entropy_value' => $entropy,
                'trend' => 'increasing',
                'stability_impact' => $this->calculateStabilityImpact($entropy)
            ],
            'accuracy' => 0.95
        ];

        // Resource analysis
        $resourceLevel = $this->calculateResourceLevel($world);
        $data[] = [
            'content' => "Resource availability at {$resourceLevel}% of normal",
            'metadata' => [
                'resource_level' => $resourceLevel,
                'scarcity_warning' => $resourceLevel < 30,
                'critical_threshold' => 20
            ],
            'accuracy' => 0.85
        ];

        // Faction stability
        $factionStability = $this->calculateFactionStability($world);
        $data[] = [
            'content' => "Faction stability index: {$factionStability}",
            'metadata' => [
                'stability_score' => $factionStability,
                'conflict_probability' => $this->calculateConflictProbability($factionStability),
                'alliance_opportunities' => $this->findAllianceOpportunities($world)
            ],
            'accuracy' => 0.8
        ];

        return $data;
    }

    private function extractEventIntelligence(ShockEvent $event): array
    {
        $intelligence = [];

        // Event impact analysis
        $intelligence[] = [
            'content' => "Event {$event->type()} with severity {$event->severity()} affecting {$event->affectedRegion()}",
            'metadata' => [
                'event_type' => $event->type(),
                'severity' => $event->severity(),
                'affected_area' => $event->affectedRegion(),
                'entropy_impact' => $event->entropyDelta(),
                'duration_estimate' => $this->estimateEventDuration($event)
            ],
            'accuracy' => 0.9
        ];

        // Casualty assessment
        $casualtyEstimate = $this->estimateCasualties($event);
        $intelligence[] = [
            'content' => "Estimated casualties: {$casualtyEstimate['min']}-{$casualtyEstimate['max']} characters",
            'metadata' => [
                'casualty_range' => $casualtyEstimate,
                'vulnerability_factors' => $this->identifyVulnerabilityFactors($event),
                'survivor_locations' => $this->predictSurvivorLocations($event)
            ],
            'accuracy' => 0.7
        ];

        return $intelligence;
    }

    private function extractFactionIntelligence(WorldAggregate $world): array
    {
        $factions = $this->getWorldFactions($world);
        $intelligence = [];

        foreach ($factions as $faction) {
            $powerLevel = $faction['power_level'];
            $stability = $faction['stability'];
            
            $intelligence[] = [
                'id' => $faction['id'],
                'content' => "Faction {$faction['name']} at power level {$powerLevel} with stability {$stability}",
                'metadata' => [
                    'power_level' => $powerLevel,
                    'stability' => $stability,
                    'territory_control' => $faction['territory'],
                    'military_strength' => $faction['military'],
                    'economic_power' => $faction['economy'],
                    'reliability' => $this->calculateFactionReliability($faction)
                ],
                'accuracy' => 0.75,
                'reliability' => $this->calculateFactionReliability($faction)
            ];
        }

        return $intelligence;
    }

    private function extractMythIntelligence(WorldAggregate $world): array
    {
        $myths = $this->getWorldMyths($world);
        $intelligence = [];

        foreach ($myths as $myth) {
            $activity = $myth['activity_level'];
            $stability = $myth['stability'];
            
            $intelligence[] = [
                'id' => $myth['id'],
                'content' => "Myth entity {$myth['name']} showing activity level {$activity}",
                'metadata' => [
                    'activity_level' => $activity,
                    'stability' => $stability,
                    'power_level' => $myth['power'],
                    'influence_radius' => $myth['influence'],
                    'worship_level' => $myth['worship'],
                    'reliability' => 0.6 // Myths are inherently less reliable
                ],
                'accuracy' => 0.6,
                'reliability' => 0.6
            ];
        }

        return $intelligence;
    }

    private function processIntelligence(IntelligenceCollection $collection): void
    {
        // Age existing intelligence
        $collection->ageIntelligence();

        // Remove old intelligence
        $collection->removeOldIntelligence(self::MAX_INTELLIGENCE_AGE);

        // Decay accuracy
        $collection->decayAccuracy(self::INTELLIGENCE_ACCURACY_DECAY);

        // Analyze patterns
        $patterns = $this->analyzer->findPatterns($collection);
        
        // Add pattern intelligence
        foreach ($patterns as $pattern) {
            $collection->add($pattern);
        }

        // Generate summary
        $summary = $this->analyzer->generateSummary($collection);
        $collection->setSummary($summary);
    }

    private function calculateCharacterReliability(CharacterSurvivalAggregate $character): float
    {
        $baseReliability = 0.7;
        
        // Characters with higher narrative weight are more reliable
        $narrativeBonus = $character->narrativeWeight()->storyImportance() * 0.2;
        
        // Injured characters might be less reliable
        $injuryPenalty = $character->riskFactors()->injuryState() * 0.1;
        
        return min(1.0, $baseReliability + $narrativeBonus - $injuryPenalty);
    }

    private function calculateEventReliability(ShockEvent $event): float
    {
        // More severe events are easier to observe accurately
        return 0.5 + ($event->severity() * 0.4);
    }

    private function getEntropyLevel(float $entropy): string
    {
        return match (true) {
            $entropy < 0.2 => 'stable',
            $entropy < 0.5 => 'normal',
            $entropy < 0.8 => 'turbulent',
            default => 'critical'
        };
    }

    private function calculateStabilityImpact(float $entropy): float
    {
        return $entropy * 0.8; // 80% of entropy affects stability
    }

    private function calculateResourceLevel(WorldAggregate $world): float
    {
        // Simplified calculation - in real system would track actual resources
        return max(10, 100 - ($world->currentEntropy()->value() * 50));
    }

    private function calculateFactionStability(WorldAggregate $world): float
    {
        // Simplified - would use actual faction data
        return max(0.1, 1.0 - ($world->currentEntropy()->value() * 0.6));
    }

    private function calculateConflictProbability(float $stability): float
    {
        return max(0.0, 1.0 - $stability);
    }

    private function findAllianceOpportunities(WorldAggregate $world): array
    {
        // Simplified - would analyze actual faction relationships
        return ['trade', 'mutual_defense', 'resource_sharing'];
    }

    private function estimateEventDuration(ShockEvent $event): int
    {
        return match ($event->type()) {
            'plague' => 20,
            'civil_war' => 50,
            'magic_collapse' => 100,
            'famine' => 30,
            'invasion' => 40,
            'earthquake' => 5,
            'myth_awakening' => 200,
            default => 25
        };
    }

    private function estimateCasualties(ShockEvent $event): array
    {
        $severity = $event->severity();
        $base = 5;
        
        return [
            'min' => (int)($base * $severity),
            'max' => (int)($base * $severity * 3),
            'probability' => $severity
        ];
    }

    private function identifyVulnerabilityFactors(ShockEvent $event): array
    {
        return match ($event->type()) {
            'plague' => ['elderly', 'children', 'weak_immune'],
            'civil_war' => ['civilians', 'merchants', 'healers'],
            'magic_collapse' => ['magic_users', 'scholars', 'artifacts'],
            'famine' => ['poor', 'elderly', 'children'],
            'invasion' => ['border_regions', 'military', 'leadership'],
            'earthquake' => ['urban_areas', 'buildings', 'mountain_regions'],
            'myth_awakening' => ['non_believers', 'skeptics', 'rationalists'],
            default => ['general_population']
        };
    }

    private function predictSurvivorLocations(ShockEvent $event): array
    {
        return match ($event->type()) {
            'plague' => ['isolated_communities', 'mountains', 'islands'],
            'civil_war' => ['neutral_territory', 'remote_villages', 'foreign_lands'],
            'magic_collapse' => ['non_magic_areas', 'technology_centers', 'wilderness'],
            'famine' => ['farming_regions', 'coastal_areas', 'trade_routes'],
            'invasion' => ['fortified_cities', 'mountain_fortresses', 'allied_territories'],
            'earthquake' => ['open_fields', 'stable_structures', 'outside_zone'],
            'myth_awakening' => ['sacred_sites', 'ancient_temples', 'protected_areas'],
            default => ['various_locations']
        };
    }

    private function getWorldFactions(WorldAggregate $world): array
    {
        // Simplified - would fetch actual faction data
        return [
            [
                'id' => 'faction_1',
                'name' => 'Northern Alliance',
                'power_level' => 0.7,
                'stability' => 0.8,
                'territory' => 30,
                'military' => 0.6,
                'economy' => 0.7
            ],
            [
                'id' => 'faction_2',
                'name' => 'Southern Coalition',
                'power_level' => 0.5,
                'stability' => 0.6,
                'territory' => 25,
                'military' => 0.4,
                'economy' => 0.5
            ]
        ];
    }

    private function getWorldMyths(WorldAggregate $world): array
    {
        // Simplified - would fetch actual myth data
        return [
            [
                'id' => 'myth_1',
                'name' => 'Ancient Dragon',
                'activity_level' => 0.3,
                'stability' => 0.7,
                'power' => 0.9,
                'influence' => 100,
                'worship' => 0.2
            ],
            [
                'id' => 'myth_2',
                'name' => 'Forest Spirits',
                'activity_level' => 0.6,
                'stability' => 0.4,
                'power' => 0.5,
                'influence' => 50,
                'worship' => 0.4
            ]
        ];
    }

    private function calculateFactionReliability(array $faction): float
    {
        // More stable factions are more reliable sources
        return 0.5 + ($faction['stability'] * 0.3);
    }
}
