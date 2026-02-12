<?php

namespace App\Services\AI;

use App\StoryEngine\WorldState;
use App\StoryEngine\CharacterState;
use App\StoryEngine\FactionState;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PredictiveAnalytics
{
    private string $apiKey;
    private string $model;
    private array $historicalData;
    private array $predictionModels;
    private array $analyticsCache;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY'));
        $this->model = config('ai.analytics_model', 'gpt-3.5-turbo');
        $this->historicalData = [];
        $this->predictionModels = $this->initializePredictionModels();
        $this->analyticsCache = [];
    }

    /**
     * Phân tích và dự đoán xu hướng thế giới
     */
    public function analyzeWorldTrends(WorldState $world, array $historicalEvents = []): array
    {
        try {
            $context = $this->buildAnalyticsContext($world, $historicalEvents);
            $trends = $this->predictTrends($context);
            $risks = $this->assessRisks($context);
            $opportunities = $this->identifyOpportunities($context);
            $recommendations = $this->generateRecommendations($context, $trends, $risks, $opportunities);
            
            $analysis = [
                'trends' => $trends,
                'risks' => $risks,
                'opportunities' => $opportunities,
                'recommendations' => $recommendations,
                'confidence_score' => $this->calculateConfidenceScore($context),
                'time_horizon' => '6_months',
                'analyzed_at' => now()->toISOString(),
            ];
            
            $this->cacheAnalysis($world->id ?? 'unknown', $analysis);
            
            Log::info('World trends analysis completed', [
                'world_id' => $world->id ?? 'unknown',
                'trends_count' => count($trends),
                'risks_count' => count($risks),
                'opportunities_count' => count($opportunities),
            ]);
            
            return $analysis;
            
        } catch (\Exception $e) {
            Log::error('World trends analysis failed', [
                'error' => $e->getMessage(),
                'world_id' => $world->id ?? 'unknown',
            ]);
            
            return $this->generateFallbackAnalysis($world);
        }
    }

    /**
     * Phân tích hành vi người chơi
     */
    public function analyzePlayerBehavior(array $playerActions, array $worldStates): array
    {
        $patterns = $this->identifyBehaviorPatterns($playerActions);
        $preferences = $this->extractPlayerPreferences($playerActions);
        $skillLevel = $this->assessPlayerSkill($playerActions, $worldStates);
        $engagement = $this->calculateEngagement($playerActions);
        
        return [
            'behavior_patterns' => $patterns,
            'preferences' => $preferences,
            'skill_level' => $skillLevel,
            'engagement_score' => $engagement,
            'recommendations' => $this->generatePlayerRecommendations($patterns, $preferences, $skillLevel),
            'analyzed_at' => now()->toISOString(),
        ];
    }

    /**
     * Dự đoán phát triển kinh tế
     */
    public function predictEconomicDevelopment(WorldState $world, array $economicIndicators): array
    {
        $currentTrend = $this->calculateEconomicTrend($economicIndicators);
        $factors = $this->identifyEconomicFactors($world, $economicIndicators);
        $projections = $this->generateEconomicProjections($currentTrend, $factors);
        $risks = $this->assessEconomicRisks($factors);
        
        return [
            'current_trend' => $currentTrend,
            'key_factors' => $factors,
            'projections' => $projections,
            'economic_risks' => $risks,
            'confidence_level' => $this->calculateEconomicConfidence($factors),
            'time_horizon' => '3_months',
            'analyzed_at' => now()->toISOString(),
        ];
    }

    /**
     * Phân tích mạng xã hội
     */
    public function analyzeSocialNetwork(array $factions, array $relationships): array
    {
        $networkMetrics = $this->calculateNetworkMetrics($factions, $relationships);
        $influenceAnalysis = $this->analyzeInfluenceNetwork($factions, $relationships);
        $clustering = $this->identifySocialClusters($factions, $relationships);
        $bridges = $this->identifyNetworkBridges($relationships);
        
        return [
            'network_metrics' => $networkMetrics,
            'influence_analysis' => $influenceAnalysis,
            'social_clusters' => $clustering,
            'network_bridges' => $bridges,
            'recommendations' => $this->generateNetworkRecommendations($networkMetrics, $clustering),
            'analyzed_at' => now()->toISOString(),
        ];
    }

    /**
     * Xây dựng ngữ cảnh phân tích
     */
    protected function buildAnalyticsContext(WorldState $world, array $historicalEvents): array
    {
        return [
            'current_state' => [
                'public_awareness' => $world->publicAwareness,
                'power_centers' => $world->powerCenters,
                'tier_index' => $world->tierIndex,
                'faction_count' => count($world->factions),
            ],
            'factions' => $this->analyzeFactionsForAnalytics($world->factions),
            'historical_events' => $this->processHistoricalEvents($historicalEvents),
            'time_series_data' => $this->buildTimeSeriesData($historicalEvents),
            'environmental_factors' => $this->assessEnvironmentalFactors($world),
        ];
    }

    /**
     * Dự đoán xu hướng
     */
    protected function predictTrends(array $context): array
    {
        $trends = [];
        
        // Xu hướng chính trị
        $politicalTrend = $this->predictPoliticalTrend($context);
        $trends[] = $politicalTrend;
        
        // Xu hướng kinh tế
        $economicTrend = $this->predictEconomicTrend($context);
        $trends[] = $economicTrend;
        
        // Xu hướng xã hội
        $socialTrend = $this->predictSocialTrend($context);
        $trends[] = $socialTrend;
        
        // Xu hướng công nghệ
        $technologicalTrend = $this->predictTechnologicalTrend($context);
        $trends[] = $technologicalTrend;
        
        return $trends;
    }

    /**
     * Đánh giá rủi ro
     */
    protected function assessRisks(array $context): array
    {
        $risks = [];
        
        // Rủi ro chính trị
        if ($context['current_state']['power_centers'] > 5) {
            $risks[] = [
                'type' => 'political_instability',
                'description' => 'Quá nhiều trung tâm quyền lực có thể gây bất ổn',
                'probability' => 0.7,
                'impact' => 'high',
                'mitigation' => 'Tăng cường ngoại giao và liên minh',
            ];
        }
        
        // Rủi ro kinh tế
        $economicVolatility = $this->calculateEconomicVolatility($context);
        if ($economicVolatility > 0.6) {
            $risks[] = [
                'type' => 'economic_crisis',
                'description' => 'Biến động kinh tế cao có thể dẫn đến khủng hoảng',
                'probability' => $economicVolatility,
                'impact' => 'high',
                'mitigation' => 'Đa dạng hóa kinh tế và dự trữ tài nguyên',
            ];
        }
        
        // Rủi ro xã hội
        $socialTension = $this->calculateSocialTension($context);
        if ($socialTension > 0.7) {
            $risks[] = [
                'type' => 'social_unrest',
                'description' => 'Căng thẳng xã hội cao có thể gây nổi loạn',
                'probability' => $socialTension,
                'impact' => 'medium',
                'mitigation' => 'Cải thiện phúc lợi và đối thoại xã hội',
            ];
        }
        
        return $risks;
    }

    /**
     * Xác định cơ hội
     */
    protected function identifyOpportunities(array $context): array
    {
        $opportunities = [];
        
        // Cơ hội kinh tế
        if ($context['current_state']['public_awareness'] > 6) {
            $opportunities[] = [
                'type' => 'economic_growth',
                'description' => 'Nhận thức công chúng cao tạo cơ hội kinh tế mới',
                'potential' => 'high',
                'requirements' => ['Đầu tư', 'Nhân lực', 'Thị trường'],
                'timeframe' => '3-6_months',
            ];
        }
        
        // Cơ hội liên minh
        $alliancePotential = $this->calculateAlliancePotential($context);
        if ($alliancePotential > 0.6) {
            $opportunities[] = [
                'type' => 'strategic_alliance',
                'description' => 'Tiềm năng hình thành liên minh chiến lược',
                'potential' => 'medium',
                'requirements' => ['Sự tin cậy', 'Lợi ích chung', 'Ngoại giao'],
                'timeframe' => '1-3_months',
            ];
        }
        
        // Cơ hội công nghệ
        $techOpportunity = $this->identifyTechOpportunity($context);
        if ($techOpportunity) {
            $opportunities[] = [
                'type' => 'technological_advancement',
                'description' => 'Cơ hội tiến bộ công nghệ',
                'potential' => 'high',
                'requirements' => ['Nghiên cứu', 'Tài nguyên', 'Chuyên gia'],
                'timeframe' => '6-12_months',
            ];
        }
        
        return $opportunities;
    }

    /**
     * Tạo khuyến nghị
     */
    protected function generateRecommendations(array $context, array $trends, array $risks, array $opportunities): array
    {
        $recommendations = [];
        
        // Khuyến nghị dựa trên xu hướng
        foreach ($trends as $trend) {
            if ($trend['direction'] === 'negative') {
                $recommendations[] = [
                    'priority' => 'high',
                    'category' => 'trend_mitigation',
                    'action' => 'Giảm thiểu xu hướng tiêu cực: ' . $trend['title'],
                    'description' => $trend['mitigation'],
                    'timeline' => 'immediate',
                ];
            }
        }
        
        // Khuyến nghị dựa trên rủi ro
        foreach ($risks as $risk) {
            if ($risk['probability'] > 0.5 && $risk['impact'] === 'high') {
                $recommendations[] = [
                    'priority' => 'critical',
                    'category' => 'risk_management',
                    'action' => 'Quản lý rủi ro: ' . $risk['type'],
                    'description' => $risk['mitigation'],
                    'timeline' => 'immediate',
                ];
            }
        }
        
        // Khuyến nghị dựa trên cơ hội
        foreach ($opportunities as $opportunity) {
            if ($opportunity['potential'] === 'high') {
                $recommendations[] = [
                    'priority' => 'medium',
                    'category' => 'opportunity_seizure',
                    'action' => 'Nắm bắt cơ hội: ' . $opportunity['type'],
                    'description' => 'Tận dụng ' . $opportunity['description'],
                    'timeline' => $opportunity['timeframe'],
                ];
            }
        }
        
        // Sắp xếp theo ưu tiên
        usort($recommendations, function ($a, $b) {
            $priorities = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            return $priorities[$b['priority']] <=> $priorities[$a['priority']];
        });
        
        return array_slice($recommendations, 0, 10); // Giới hạn 10 khuyến nghị
    }

    /**
     * Phân tích các phe phái cho analytics
     */
    protected function analyzeFactionsForAnalytics(array $factions): array
    {
        $analysis = [];
        
        foreach ($factions as $faction) {
            $analysis[] = [
                'name' => $faction->name,
                'type' => $faction->type,
                'power_level' => $faction->militaryPower ?? 5,
                'economic_strength' => $this->calculateFactionEconomicStrength($faction),
                'social_influence' => $this->calculateFactionSocialInfluence($faction),
                'stability' => $faction->cohesion ?? 50,
                'growth_trend' => $this->calculateFactionGrowthTrend($faction),
            ];
        }
        
        return $analysis;
    }

    /**
     * Xử lý sự kiện lịch sử
     */
    protected function processHistoricalEvents(array $events): array
    {
        $processed = [];
        
        foreach ($events as $event) {
            $processed[] = [
                'type' => $event['type'] ?? 'unknown',
                'severity' => $event['severity'] ?? 5,
                'timestamp' => $event['created_at'] ?? now(),
                'participants' => $event['participants'] ?? [],
                'consequences' => $event['consequences'] ?? [],
                'impact_score' => $this->calculateEventImpact($event),
            ];
        }
        
        return $processed;
    }

    /**
     * Xây dựng dữ liệu time series
     */
    protected function buildTimeSeriesData(array $events): array
    {
        $timeSeries = [];
        
        // Nhóm sự kiện theo thời gian
        $groupedEvents = [];
        foreach ($events as $event) {
            $date = substr($event['created_at'] ?? now(), 0, 10); // YYYY-MM-DD
            if (!isset($groupedEvents[$date])) {
                $groupedEvents[$date] = [];
            }
            $groupedEvents[$date][] = $event;
        }
        
        // Tính toán các chỉ số cho mỗi ngày
        foreach ($groupedEvents as $date => $dayEvents) {
            $timeSeries[] = [
                'date' => $date,
                'event_count' => count($dayEvents),
                'average_severity' => array_sum(array_column($dayEvents, 'severity')) / count($dayEvents),
                'total_impact' => array_sum(array_column($dayEvents, 'impact_score')),
                'conflict_events' => count(array_filter($dayEvents, fn($e) => $e['type'] === 'conflict')),
                'opportunity_events' => count(array_filter($dayEvents, fn($e) => $e['type'] === 'opportunity')),
            ];
        }
        
        return $timeSeries;
    }

    /**
     * Đánh giá yếu tố môi trường
     */
    protected function assessEnvironmentalFactors(WorldState $world): array
    {
        return [
            'political_stability' => $this->calculatePoliticalStability($world),
            'economic_health' => $this->calculateEconomicHealth($world),
            'social_cohesion' => $this->calculateSocialCohesion($world),
            'technological_level' => $this->calculateTechnologicalLevel($world),
            'external_threats' => $this->assessExternalThreats($world),
        ];
    }

    /**
     * Các phương thức dự đoán cụ thể
     */
    protected function predictPoliticalTrend(array $context): array
    {
        $stability = $context['environmental_factors']['political_stability'];
        $factionCount = $context['current_state']['faction_count'];
        
        $direction = $stability > 0.7 ? 'stable' : ($stability > 0.4 ? 'volatile' : 'unstable');
        $confidence = $stability;
        
        return [
            'category' => 'political',
            'title' => 'Xu hướng chính trị',
            'direction' => $direction,
            'description' => "Tình hình chính trị được dự đoán sẽ {$direction}",
            'confidence' => $confidence,
            'key_factors' => ['Sự ổn định phe phái', 'Số lượng phe phái', 'Căng thẳng khu vực'],
            'projection' => $this->projectPoliticalState($direction, $context),
        ];
    }

    protected function predictEconomicTrend(array $context): array
    {
        $economicHealth = $context['environmental_factors']['economic_health'];
        $publicAwareness = $context['current_state']['public_awareness'];
        
        $direction = $economicHealth > 0.7 ? 'growing' : ($economicHealth > 0.4 ? 'stable' : 'declining');
        $confidence = $economicHealth;
        
        return [
            'category' => 'economic',
            'title' => 'Xu hướng kinh tế',
            'direction' => $direction,
            'description' => "Kinh tế được dự đoán sẽ {$direction}",
            'confidence' => $confidence,
            'key_factors' => ['Sức khỏe kinh tế', 'Nhận thức công chúng', 'Thương mại'],
            'projection' => $this->projectEconomicState($direction, $context),
        ];
    }

    protected function predictSocialTrend(array $context): array
    {
        $socialCohesion = $context['environmental_factors']['social_cohesion'];
        $powerCenters = $context['current_state']['power_centers'];
        
        $direction = $socialCohesion > 0.7 ? 'harmonious' : ($socialCohesion > 0.4 ? 'neutral' : 'tense');
        $confidence = $socialCohesion;
        
        return [
            'category' => 'social',
            'title' => 'Xu hướng xã hội',
            'direction' => $direction,
            'description' => "Xã hội được dự đoán sẽ {$direction}",
            'confidence' => $confidence,
            'key_factors' => ['Sự gắn kết xã hội', 'Số trung tâm quyền lực', 'Văn hóa'],
            'projection' => $this->projectSocialState($direction, $context),
        ];
    }

    protected function predictTechnologicalTrend(array $context): array
    {
        $techLevel = $context['environmental_factors']['technological_level'];
        $publicAwareness = $context['current_state']['public_awareness'];
        
        $direction = $techLevel > 0.7 ? 'advancing' : ($techLevel > 0.4 ? 'stable' : 'stagnant');
        $confidence = $techLevel;
        
        return [
            'category' => 'technological',
            'title' => 'Xu hướng công nghệ',
            'direction' => $direction,
            'description' => "Công nghệ được dự đoán sẽ {$direction}",
            'confidence' => $confidence,
            'key_factors' => ['Trình độ công nghệ', 'Nhận thức công chúng', 'Nghiên cứu'],
            'projection' => $this->projectTechnologicalState($direction, $context),
        ];
    }

    /**
     * Các phương thức tính toán hỗ trợ
     */
    protected function calculateConfidenceScore(array $context): float
    {
        $dataQuality = $this->assessDataQuality($context);
        $modelAccuracy = $this->getModelAccuracy();
        $contextComplexity = $this->assessContextComplexity($context);
        
        return ($dataQuality + $modelAccuracy + (1 - $contextComplexity)) / 3;
    }

    protected function calculateEconomicVolatility(array $context): float
    {
        // Logic đơn giản để tính biến động kinh tế
        $factions = $context['factions'];
        $economicStrengths = array_column($factions, 'economic_strength');
        
        if (empty($economicStrengths)) {
            return 0.5;
        }
        
        $mean = array_sum($economicStrengths) / count($economicStrengths);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $economicStrengths)) / count($economicStrengths);
        
        return min(1.0, sqrt($variance));
    }

    protected function calculateSocialTension(array $context): float
    {
        $factions = $context['factions'];
        $totalTension = 0;
        
        foreach ($factions as $faction) {
            $stability = $faction['stability'] ?? 50;
            $tension = (100 - $stability) / 100;
            $totalTension += $tension;
        }
        
        return count($factions) > 0 ? $totalTension / count($factions) : 0.5;
    }

    protected function calculateAlliancePotential(array $context): float
    {
        $factions = $context['factions'];
        $potential = 0;
        
        foreach ($factions as $faction) {
            $stability = $faction['stability'] ?? 50;
            if ($stability > 60) {
                $potential += 0.3;
            }
        }
        
        return min(1.0, $potential);
    }

    protected function identifyTechOpportunity(array $context): bool
    {
        $techLevel = $context['environmental_factors']['technological_level'];
        $publicAwareness = $context['current_state']['public_awareness'];
        
        return $techLevel > 0.5 && $publicAwareness > 6;
    }

    protected function generateFallbackAnalysis(WorldState $world): array
    {
        return [
            'trends' => [
                [
                    'category' => 'general',
                    'title' => 'Xu hướng ổn định',
                    'direction' => 'stable',
                    'description' => 'Thế giới đang trong trạng thái tương đối ổn định',
                    'confidence' => 0.5,
                ],
            ],
            'risks' => [],
            'opportunities' => [],
            'recommendations' => [
                [
                    'priority' => 'medium',
                    'category' => 'monitoring',
                    'action' => 'Tiếp tục theo dõi tình hình',
                    'description' => 'Giám sát các chỉ số và xu hướng',
                    'timeline' => 'ongoing',
                ],
            ],
            'confidence_score' => 0.5,
            'time_horizon' => '6_months',
            'analyzed_at' => now()->toISOString(),
        ];
    }

    /**
     * Cache phân tích
     */
    protected function cacheAnalysis(string $worldId, array $analysis): void
    {
        $this->analyticsCache[$worldId] = [
            'analysis' => $analysis,
            'cached_at' => now()->toISOString(),
        ];
    }

    /**
     * Lấy phân tích từ cache
     */
    public function getCachedAnalysis(string $worldId): ?array
    {
        if (!isset($this->analyticsCache[$worldId])) {
            return null;
        }
        
        $cached = $this->analyticsCache[$worldId];
        $cacheAge = now()->diffInMinutes(\Carbon\Carbon::parse($cached['cached_at']));
        
        // Cache có hiệu lực trong 1 giờ
        if ($cacheAge > 60) {
            unset($this->analyticsCache[$worldId]);
            return null;
        }
        
        return $cached['analysis'];
    }

    /**
     * Khởi tạo các mô hình dự đoán
     */
    protected function initializePredictionModels(): array
    {
        return [
            'political' => ['accuracy' => 0.75, 'type' => 'trend_analysis'],
            'economic' => ['accuracy' => 0.70, 'type' => 'time_series'],
            'social' => ['accuracy' => 0.65, 'type' => 'network_analysis'],
            'technological' => ['accuracy' => 0.60, 'type' => 'innovation_curve'],
        ];
    }

    /**
     * Các phương thức hỗ trợ khác
     */
    protected function getModelAccuracy(): float
    {
        $accuracies = array_column($this->predictionModels, 'accuracy');
        return array_sum($accuracies) / count($accuracies);
    }

    protected function assessDataQuality(array $context): float
    {
        $dataPoints = 0;
        
        if (!empty($context['factions'])) $dataPoints++;
        if (!empty($context['historical_events'])) $dataPoints++;
        if (!empty($context['time_series_data'])) $dataPoints++;
        
        return min(1.0, $dataPoints / 3);
    }

    protected function assessContextComplexity(array $context): float
    {
        $complexity = 0;
        
        $complexity += count($context['factions']) * 0.1;
        $complexity += count($context['historical_events']) * 0.05;
        $complexity += count($context['time_series_data']) * 0.02;
        
        return min(1.0, $complexity);
    }

    /**
     * Lấy thống kê
     */
    public function getStatistics(): array
    {
        return [
            'analyses_performed' => count($this->analyticsCache),
            'api_key_configured' => !empty($this->apiKey),
            'model' => $this->model,
            'prediction_models' => $this->predictionModels,
        ];
    }

    /**
     * Xóa cache
     */
    public function clearCache(): void
    {
        $this->analyticsCache = [];
    }
}
