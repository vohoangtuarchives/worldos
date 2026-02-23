<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Collections\IntelligenceCollection;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceReport;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceType;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceSource;

final class IntelligenceAnalyzer
{
    public function findPatterns(IntelligenceCollection $collection): array
    {
        $patterns = [];

        // Analyze character death patterns
        $deathPatterns = $this->analyzeDeathPatterns($collection);
        $patterns = array_merge($patterns, $deathPatterns);

        // Analyze entropy patterns
        $entropyPatterns = $this->analyzeEntropyPatterns($collection);
        $patterns = array_merge($patterns, $entropyPatterns);

        // Analyze faction conflict patterns
        $conflictPatterns = $this->analyzeConflictPatterns($collection);
        $patterns = array_merge($patterns, $conflictPatterns);

        // Analyze resource patterns
        $resourcePatterns = $this->analyzeResourcePatterns($collection);
        $patterns = array_merge($patterns, $resourcePatterns);

        // Analyze myth activity patterns
        $mythPatterns = $this->analyzeMythPatterns($collection);
        $patterns = array_merge($patterns, $mythPatterns);

        return $patterns;
    }

    public function generateSummary(IntelligenceCollection $collection): IntelligenceSummary
    {
        $summary = new IntelligenceSummary();

        // Threat assessment
        $threats = $this->assessThreats($collection);
        $summary->setThreats($threats);

        // Opportunity identification
        $opportunities = $this->identifyOpportunities($collection);
        $summary->setOpportunities($opportunities);

        // Risk analysis
        $risks = $this->analyzeRisks($collection);
        $summary->setRisks($risks);

        // Recommendations
        $recommendations = $this->generateRecommendations($collection);
        $summary->setRecommendations($recommendations);

        // Overall assessment
        $overall = $this->assessOverallSituation($collection);
        $summary->setOverallAssessment($overall);

        return $summary;
    }

    private function analyzeDeathPatterns(IntelligenceCollection $collection): array
    {
        $patterns = [];
        $deathReports = $collection->getByType(IntelligenceType::CHARACTER_OBSERVATION)
            ->all();

        // Group by character vulnerability
        $vulnerableCharacters = [];
        foreach ($deathReports as $report) {
            if (str_contains($report->content, 'vulnerability') || 
                str_contains($report->content, 'injury')) {
                $vulnerableCharacters[] = $report->source->id;
            }
        }

        if (count($vulnerableCharacters) >= 3) {
            $patterns[] = new IntelligenceReport(
                id: uniqid('pattern_', true),
                type: IntelligenceType::PATTERN_DETECTION,
                source: IntelligenceSource::environment($collection->worldId(), 0.8),
                content: 'Multiple characters showing vulnerability patterns - potential mass casualty event',
                metadata: [
                    'pattern_type' => 'character_vulnerability',
                    'affected_characters' => $vulnerableCharacters,
                    'severity' => 'high',
                    'prediction' => 'increased_death_probability'
                ],
                timestamp: now(),
                accuracy: 0.7,
                age: 0
            );
        }

        return $patterns;
    }

    private function analyzeEntropyPatterns(IntelligenceCollection $collection): array
    {
        $patterns = [];
        $envReports = $collection->getByType(IntelligenceType::ENVIRONMENTAL_SCAN)
            ->all();

        $entropyValues = [];
        foreach ($envReports as $report) {
            if (isset($report->metadata['entropy_value'])) {
                $entropyValues[] = $report->metadata['entropy_value'];
            }
        }

        if (count($entropyValues) >= 3) {
            $avgEntropy = array_sum($entropyValues) / count($entropyValues);
            $maxEntropy = max($entropyValues);
            
            if ($maxEntropy > 0.7) {
                $patterns[] = new IntelligenceReport(
                    id: uniqid('pattern_', true),
                    type: IntelligenceType::PATTERN_DETECTION,
                    source: IntelligenceSource::environment($collection->worldId(), 0.9),
                    content: 'Critical entropy levels detected - world stability at risk',
                    metadata: [
                        'pattern_type' => 'entropy_spike',
                        'average_entropy' => $avgEntropy,
                        'peak_entropy' => $maxEntropy,
                        'trend' => 'increasing',
                        'severity' => $maxEntropy > 0.8 ? 'critical' : 'high'
                    ],
                    timestamp: now(),
                    accuracy: 0.85,
                    age: 0
                );
            }
        }

        return $patterns;
    }

    private function analyzeConflictPatterns(IntelligenceCollection $collection): array
    {
        $patterns = [];
        $factionReports = $collection->getByType(IntelligenceType::FACTION_MONITORING)
            ->all();

        $unstableFactions = [];
        foreach ($factionReports as $report) {
            if (isset($report->metadata['stability']) && $report->metadata['stability'] < 0.5) {
                $unstableFactions[] = $report->source->id;
            }
        }

        if (count($unstableFactions) >= 2) {
            $patterns[] = new IntelligenceReport(
                id: uniqid('pattern_', true),
                type: IntelligenceType::PATTERN_DETECTION,
                source: IntelligenceSource::environment($collection->worldId(), 0.7),
                content: 'Multiple unstable factions detected - high conflict probability',
                metadata: [
                    'pattern_type' => 'faction_instability',
                    'unstable_factions' => $unstableFactions,
                    'conflict_probability' => 0.8,
                    'potential_outcomes' => ['civil_war', 'territory_dispute', 'power_vacuum']
                ],
                timestamp: now(),
                accuracy: 0.75,
                age: 0
            );
        }

        return $patterns;
    }

    private function analyzeResourcePatterns(IntelligenceCollection $collection): array
    {
        $patterns = [];
        $envReports = $collection->getByType(IntelligenceType::ENVIRONMENTAL_SCAN)
            ->all();

        $resourceLevels = [];
        foreach ($envReports as $report) {
            if (isset($report->metadata['resource_level'])) {
                $resourceLevels[] = $report->metadata['resource_level'];
            }
        }

        if (count($resourceLevels) >= 2) {
            $avgResourceLevel = array_sum($resourceLevels) / count($resourceLevels);
            $minResourceLevel = min($resourceLevels);
            
            if ($minResourceLevel < 30) {
                $patterns[] = new IntelligenceReport(
                    id: uniqid('pattern_', true),
                    type: IntelligenceType::PATTERN_DETECTION,
                    source: IntelligenceSource::environment($collection->worldId(), 0.8),
                    content: 'Critical resource scarcity detected - survival threat',
                    metadata: [
                        'pattern_type' => 'resource_depletion',
                        'average_level' => $avgResourceLevel,
                        'critical_level' => $minResourceLevel,
                        'affected_regions' => ['unknown'],
                        'survival_impact' => 'high'
                    ],
                    timestamp: now(),
                    accuracy: 0.8,
                    age: 0
                );
            }
        }

        return $patterns;
    }

    private function analyzeMythPatterns(IntelligenceCollection $collection): array
    {
        $patterns = [];
        $mythReports = $collection->getByType(IntelligenceType::MYTH_ANALYSIS)
            ->all();

        $activeMyths = [];
        foreach ($mythReports as $report) {
            if (isset($report->metadata['activity_level']) && $report->metadata['activity_level'] > 0.7) {
                $activeMyths[] = $report->source->id;
            }
        }

        if (count($activeMyths) >= 2) {
            $patterns[] = new IntelligenceReport(
                id: uniqid('pattern_', true),
                type: IntelligenceType::PATTERN_DETECTION,
                source: IntelligenceSource::myth('pattern_analysis', 0.6),
                content: 'Multiple myth entities showing high activity - supernatural convergence',
                metadata: [
                    'pattern_type' => 'myth_convergence',
                    'active_entities' => $activeMyths,
                    'supernatural_risk' => 'high',
                    'potential_events' => ['myth_awakening', 'divine_intervention', 'reality_distortion']
                ],
                timestamp: now(),
                accuracy: 0.6,
                age: 0
            );
        }

        return $patterns;
    }

    private function assessThreats(IntelligenceCollection $collection): array
    {
        $threats = [];

        // High entropy threat
        $highEntropyReports = array_filter($collection->all(), fn($r) => 
            $r->type === IntelligenceType::ENVIRONMENTAL_SCAN &&
            isset($r->metadata['entropy_value']) &&
            $r->metadata['entropy_value'] > 0.7
        );

        if (!empty($highEntropyReports)) {
            $threats[] = [
                'type' => 'entropy_crisis',
                'severity' => 'high',
                'description' => 'World entropy approaching critical levels',
                'affected_areas' => ['global'],
                'mitigation' => ['reduce_conflicts', 'stabilize_factions', 'resource_management']
            ];
        }

        // Character death threat
        $vulnerableReports = array_filter($collection->all(), fn($r) => 
            $r->type === IntelligenceType::CHARACTER_OBSERVATION &&
            (str_contains($r->content, 'injury') || str_contains($r->content, 'vulnerability'))
        );

        if (count($vulnerableReports) >= 3) {
            $threats[] = [
                'type' => 'mass_casualty',
                'severity' => 'medium',
                'description' => 'Multiple characters at high risk of death',
                'affected_areas' => ['character_population'],
                'mitigation' => ['improve_healthcare', 'reduce_dangers', 'increase_protection']
            ];
        }

        return $threats;
    }

    private function identifyOpportunities(IntelligenceCollection $collection): array
    {
        $opportunities = [];

        // Alliance opportunities
        $stableFactions = array_filter($collection->all(), fn($r) => 
            $r->type === IntelligenceType::FACTION_MONITORING &&
            isset($r->metadata['stability']) &&
            $r->metadata['stability'] > 0.7
        );

        if (count($stableFactions) >= 2) {
            $opportunities[] = [
                'type' => 'alliance_formation',
                'potential' => 'high',
                'description' => 'Multiple stable factions could form alliances',
                'benefits' => ['stability_increase', 'resource_sharing', 'conflict_reduction'],
                'requirements' => ['diplomacy', 'mutual_interest', 'trust_building']
            ];
        }

        // Resource abundance
        $resourceReports = array_filter($collection->all(), fn($r) => 
            $r->type === IntelligenceType::ENVIRONMENTAL_SCAN &&
            isset($r->metadata['resource_level']) &&
            $r->metadata['resource_level'] > 70
        );

        if (!empty($resourceReports)) {
            $opportunities[] = [
                'type' => 'resource_exploitation',
                'potential' => 'medium',
                'description' => 'Abundant resources available for development',
                'benefits' => ['economic_growth', 'population_increase', 'stability'],
                'requirements' => ['extraction_infrastructure', 'distribution_systems']
            ];
        }

        return $opportunities;
    }

    private function analyzeRisks(IntelligenceCollection $collection): array
    {
        $risks = [];

        // Calculate overall risk score
        $reliableReports = $collection->getReliable();
        $highUrgencyReports = $collection->getHighUrgency();
        
        $riskScore = ($highUrgencyReports->count() / max(1, $reliableReports->count())) * 100;

        if ($riskScore > 50) {
            $risks[] = [
                'type' => 'systemic_risk',
                'level' => 'high',
                'score' => $riskScore,
                'factors' => ['high_urgency_events', 'intelligence_overload', 'coordination_failure'],
                'mitigation' => ['prioritize_threats', 'improve_intelligence_quality', 'centralize_command']
            ];
        }

        return $risks;
    }

    private function generateRecommendations(IntelligenceCollection $collection): array
    {
        $recommendations = [];

        $threatCount = count($this->assessThreats($collection));
        $opportunityCount = count($this->identifyOpportunities($collection));

        if ($threatCount > $opportunityCount) {
            $recommendations[] = 'Focus on threat mitigation - immediate dangers outweigh opportunities';
            $recommendations[] = 'Increase defensive measures and emergency preparedness';
        } elseif ($opportunityCount > $threatCount) {
            $recommendations[] = 'Pursue available opportunities - favorable conditions for growth';
            $recommendations[] = 'Allocate resources to expansion and development';
        } else {
            $recommendations[] = 'Maintain balanced approach - manage threats while pursuing opportunities';
        }

        // Intelligence quality recommendations
        $avgAccuracy = $collection->getAverageAccuracy();
        if ($avgAccuracy < 0.7) {
            $recommendations[] = 'Improve intelligence gathering - current accuracy below optimal';
        }

        return $recommendations;
    }

    private function assessOverallSituation(IntelligenceCollection $collection): array
    {
        $reliableCount = $collection->getReliable()->count();
        $totalCount = $collection->count();
        $highUrgencyCount = $collection->getHighUrgency()->count();

        $situationScore = ($reliableCount / max(1, $totalCount)) * 50 - ($highUrgencyCount * 10);
        
        $status = match (true) {
            $situationScore > 30 => 'stable',
            $situationScore > 10 => 'cautious',
            $situationScore > -10 => 'concerning',
            default => 'critical'
        };

        return [
            'status' => $status,
            'score' => $situationScore,
            'intelligence_quality' => $reliableCount / max(1, $totalCount),
            'threat_level' => $highUrgencyCount / max(1, $totalCount),
            'recommendation' => $this->getSituationRecommendation($status)
        ];
    }

    private function getSituationRecommendation(string $status): string
    {
        return match ($status) {
            'stable' => 'Continue current operations with routine monitoring',
            'cautious' => 'Increase vigilance and prepare contingency plans',
            'concerning' => 'Implement defensive measures and prioritize threats',
            'critical' => 'Emergency protocols activated - immediate action required',
            default => 'Assess situation and determine appropriate response'
        };
    }
}

final class IntelligenceSummary
{
    private array $threats = [];
    private array $opportunities = [];
    private array $risks = [];
    private array $recommendations = [];
    private array $overallAssessment = [];

    public function setThreats(array $threats): void
    {
        $this->threats = $threats;
    }

    public function setOpportunities(array $opportunities): void
    {
        $this->opportunities = $opportunities;
    }

    public function setRisks(array $risks): void
    {
        $this->risks = $risks;
    }

    public function setRecommendations(array $recommendations): void
    {
        $this->recommendations = $recommendations;
    }

    public function setOverallAssessment(array $assessment): void
    {
        $this->overallAssessment = $assessment;
    }

    public function toArray(): array
    {
        return [
            'threats' => $this->threats,
            'opportunities' => $this->opportunities,
            'risks' => $this->risks,
            'recommendations' => $this->recommendations,
            'overall_assessment' => $this->overallAssessment,
            'summary' => [
                'threat_count' => count($this->threats),
                'opportunity_count' => count($this->opportunities),
                'risk_count' => count($this->risks),
                'recommendation_count' => count($this->recommendations),
                'status' => $this->overallAssessment['status'] ?? 'unknown'
            ]
        ];
    }
}
