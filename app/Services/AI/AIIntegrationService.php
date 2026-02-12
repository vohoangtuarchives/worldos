<?php

namespace App\Services\AI;

use App\StoryEngine\WorldState;
use App\StoryEngine\CharacterState;
use App\StoryEngine\Seed;
use Illuminate\Support\Facades\Log;

class AIIntegrationService
{
    private AIStoryGenerator $storyGenerator;
    private IntelligentNPC $npcManager;
    private DynamicWorldEventGenerator $eventGenerator;
    private PredictiveAnalytics $analytics;
    private array $aiSettings;
    private bool $aiEnabled;

    public function __construct()
    {
        $this->storyGenerator = new AIStoryGenerator();
        $this->npcManager = new IntelligentNPC();
        $this->eventGenerator = new DynamicWorldEventGenerator();
        $this->analytics = new PredictiveAnalytics();
        
        $this->aiSettings = $this->loadAISettings();
        $this->aiEnabled = $this->aiSettings['enabled'] ?? false;
    }

    /**
     * Tích hợp AI đầy đủ cho thế giới
     */
    public function integrateAIForWorld(WorldState $world, array $options = []): array
    {
        if (!$this->aiEnabled) {
            return $this->generateFallbackIntegration($world);
        }

        try {
            $results = [];
            
            // 1. Tạo NPC thông minh
            if ($options['generate_npcs'] ?? true) {
                $results['npcs'] = $this->generateIntelligentNPCs($world, $options['npc_count'] ?? 5);
            }
            
            // 2. Tạo sự kiện thế giới động
            if ($options['generate_events'] ?? true) {
                $results['events'] = $this->generateDynamicEvents($world, $results['npcs'] ?? []);
            }
            
            // 3. Tạo cốt truyện AI
            if ($options['generate_stories'] ?? true) {
                $results['stories'] = $this->generateAIStories($world, $results['npcs'] ?? [], $results['events'] ?? []);
            }
            
            // 4. Phân tích dự đoán
            if ($options['run_analytics'] ?? true) {
                $results['analytics'] = $this->runPredictiveAnalytics($world, $results['events'] ?? []);
            }
            
            // 5. Tạo tương tác NPC
            if ($options['generate_interactions'] ?? true) {
                $results['interactions'] = $this->generateNPCInteractions($results['npcs'] ?? [], $world);
            }
            
            $this->logIntegrationSuccess($world, $results);
            
            return [
                'success' => true,
                'world_id' => $world->id ?? 'unknown',
                'integration_results' => $results,
                'ai_settings_used' => $this->aiSettings,
                'processed_at' => now()->toISOString(),
            ];
            
        } catch (\Exception $e) {
            Log::error('AI integration failed', [
                'error' => $e->getMessage(),
                'world_id' => $world->id ?? 'unknown',
                'options' => $options,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'fallback_results' => $this->generateFallbackIntegration($world),
            ];
        }
    }

    /**
     * Tạo NPC thông minh
     */
    protected function generateIntelligentNPCs(WorldState $world, int $count): array
    {
        $npcs = [];
        $factionNames = $this->extractFactionNames($world);
        
        for ($i = 0; $i < $count; $i++) {
            $faction = $factionNames[array_rand($factionNames)] ?? 'independent';
            $role = $this->selectNPCRole($world);
            $traits = $this->generateNPCTraits($role);
            
            $npc = $this->npcManager->createNPC(
                $this->generateNPCName($faction, $role),
                $faction,
                $role,
                $traits
            );
            
            $npcs[] = $npc;
        }
        
        Log::info('Intelligent NPCs generated', [
            'world_id' => $world->id ?? 'unknown',
            'npc_count' => count($npcs),
        ]);
        
        return $npcs;
    }

    /**
     * Tạo sự kiện thế giới động
     */
    protected function generateDynamicEvents(WorldState $world, array $npcs): array
    {
        $events = [];
        $eventCount = $this->calculateEventCount($world);
        
        for ($i = 0; $i < $eventCount; $i++) {
            $event = $this->eventGenerator->generateDynamicEvent($world, $npcs);
            $events[] = $event;
        }
        
        Log::info('Dynamic world events generated', [
            'world_id' => $world->id ?? 'unknown',
            'event_count' => count($events),
        ]);
        
        return $events;
    }

    /**
     * Tạo cốt truyện AI
     */
    protected function generateAIStories(WorldState $world, array $npcs, array $events): array
    {
        $stories = [];
        $seeds = $this->extractSeedsFromEvents($events);
        
        // Tạo cốt truyện chính
        $mainStory = $this->storyGenerator->generateStory($world, new CharacterState(), $seeds);
        $stories['main'] = $mainStory;
        
        // Tạo cốt truyện cho các NPC quan trọng
        $importantNPCs = array_filter($npcs, fn($npc) => ($npc['influence'] ?? 0) > 5);
        
        foreach ($importantNPCs as $npc) {
            $npcStory = $this->generateNPCStory($npc, $world, $events);
            $stories['npc_' . $npc['id']] = $npcStory;
        }
        
        Log::info('AI stories generated', [
            'world_id' => $world->id ?? 'unknown',
            'story_count' => count($stories),
        ]);
        
        return $stories;
    }

    /**
     * Chạy phân tích dự đoán
     */
    protected function runPredictiveAnalytics(WorldState $world, array $events): array
    {
        $analytics = [];
        
        // Phân tích xu hướng thế giới
        $analytics['world_trends'] = $this->analytics->analyzeWorldTrends($world, $events);
        
        // Phân tích mạng xã hội
        $analytics['social_network'] = $this->analytics->analyzeSocialNetwork($world->factions, []);
        
        // Phân tích kinh tế
        $analytics['economic_development'] = $this->analytics->predictEconomicDevelopment($world, []);
        
        Log::info('Predictive analytics completed', [
            'world_id' => $world->id ?? 'unknown',
            'analytics_types' => array_keys($analytics),
        ]);
        
        return $analytics;
    }

    /**
     * Tạo tương tác NPC
     */
    protected function generateNPCInteractions(array $npcs, WorldState $world): array
    {
        $interactions = [];
        
        // Tạo tương tác giữa các NPC
        for ($i = 0; $i < count($npcs); $i++) {
            for ($j = $i + 1; $j < count($npcs); $j++) {
                if ($this->shouldInteract($npcs[$i], $npcs[$j])) {
                    $interaction = $this->createNPCInteraction($npcs[$i], $npcs[$j], $world);
                    $interactions[] = $interaction;
                }
            }
        }
        
        Log::info('NPC interactions generated', [
            'world_id' => $world->id ?? 'unknown',
            'interaction_count' => count($interactions),
        ]);
        
        return $interactions;
    }

    /**
     * Tạo cốt truyện cho NPC
     */
    protected function generateNPCStory(array $npc, WorldState $world, array $events): array
    {
        $characterState = new CharacterState();
        $characterState->name = $npc['name'];
        $characterState->role = $npc['role'];
        $characterState->influence = $npc['influence'] ?? 0;
        
        // Lọc các sự kiện liên quan đến NPC
        $relevantEvents = array_filter($events, function($event) use ($npc) {
            return $this->isEventRelevantToNPC($event, $npc);
        });
        
        $seeds = $this->extractSeedsFromEvents($relevantEvents);
        
        return $this->storyGenerator->generateStory($world, $characterState, $seeds);
    }

    /**
     * Các phương thức hỗ trợ
     */
    protected function extractFactionNames(WorldState $world): array
    {
        return array_map(fn($faction) => $faction->name, $world->factions);
    }

    protected function selectNPCRole(WorldState $world): string
    {
        $roles = ['leader', 'advisor', 'merchant', 'scholar', 'spy', 'diplomat'];
        
        // Chọn vai trò dựa trên trạng thái thế giới
        if ($world->powerCenters > 3) {
            $roles[] = 'general';
        }
        
        if ($world->publicAwareness > 7) {
            $roles[] = 'influencer';
        }
        
        return $roles[array_rand($roles)];
    }

    protected function generateNPCTraits(string $role): array
    {
        $traitSets = [
            'leader' => ['brave', 'diplomatic'],
            'advisor' => ['scholarly', 'cautious'],
            'merchant' => ['diplomatic', 'opportunistic'],
            'scholar' => ['scholarly', 'mysterious'],
            'spy' => ['mysterious', 'cautious'],
            'diplomat' => ['diplomatic', 'cautious'],
            'general' => ['brave', 'aggressive'],
            'influencer' => ['diplomatic', 'charismatic'],
        ];
        
        return $traitSets[$role] ?? ['neutral'];
    }

    protected function generateNPCName(string $faction, string $role): string
    {
        $firstNames = ['An', 'Bình', 'Chi', 'Dũng', 'Gia', 'Hà', 'Linh', 'Minh', 'Nam', 'Quân'];
        $lastNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Huỳnh', 'Võ', 'Hoàng'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    protected function calculateEventCount(WorldState $world): int
    {
        $baseCount = 3;
        $complexity = ($world->publicAwareness + $world->powerCenters) / 20;
        
        return max(1, min(10, (int)($baseCount * (1 + $complexity))));
    }

    protected function extractSeedsFromEvents(array $events): array
    {
        $seeds = [];
        
        foreach ($events as $event) {
            $seedType = $this->mapEventTypeToSeed($event['type']);
            $severity = $event['severity'] ?? 5;
            
            $seeds[] = new Seed($seedType, 'global', $severity);
        }
        
        return $seeds;
    }

    protected function mapEventTypeToSeed(string $eventType): string
    {
        $mapping = [
            'conflict' => 'CONFLICT',
            'opportunity' => 'OPPORTUNITY',
            'crisis' => 'CRISIS',
            'discovery' => 'MYSTERY',
            'alliance' => 'OPPORTUNITY',
            'betrayal' => 'CONFLICT',
            'celebration' => 'OPPORTUNITY',
        ];
        
        return $mapping[$eventType] ?? 'MYSTERY';
    }

    protected function shouldInteract(array $npc1, array $npc2): bool
    {
        // NPC cùng phe có khả năng tương tác cao hơn
        if ($npc1['faction'] === $npc2['faction']) {
            return rand(1, 100) <= 70;
        }
        
        // NPC phe khác nhau có khả năng tương tác thấp hơn
        return rand(1, 100) <= 30;
    }

    protected function createNPCInteraction(array $npc1, array $npc2, WorldState $world): array
    {
        $interactionTypes = ['dialogue', 'trade', 'conflict', 'cooperation'];
        $type = $interactionTypes[array_rand($interactionTypes)];
        
        return [
            'id' => uniqid('interaction_'),
            'type' => $type,
            'participants' => [$npc1['id'], $npc2['id']],
            'location' => $this->selectInteractionLocation($world),
            'topic' => $this->selectInteractionTopic($npc1, $npc2, $type),
            'outcome' => $this->generateInteractionOutcome($npc1, $npc2, $type),
            'created_at' => now()->toISOString(),
        ];
    }

    protected function selectInteractionLocation(WorldState $world): string
    {
        $locations = ['Thủ đô', 'Thị trường', 'Đền thờ', 'Quán rượu', 'Thư viện', 'Căng cứ'];
        return $locations[array_rand($locations)];
    }

    protected function selectInteractionTopic(array $npc1, array $npc2, string $type): string
    {
        $topics = [
            'dialogue' => ['Thời tiết', 'Chính trị', 'Kinh tế', 'Văn hóa'],
            'trade' => ['Hàng hóa', 'Giá cả', 'Thỏa thuận', 'Giao dịch'],
            'conflict' => ['Tranh chấp', 'Quan điểm', 'Lợi ích', 'Sự thật'],
            'cooperation' => ['Dự án', 'Mục tiêu', 'Kế hoạch', 'Chiến lược'],
        ];
        
        $possibleTopics = $topics[$type] ?? $topics['dialogue'];
        return $possibleTopics[array_rand($possibleTopics)];
    }

    protected function generateInteractionOutcome(array $npc1, array $npc2, string $type): array
    {
        $outcomes = [
            'dialogue' => ['information_shared', 'relationship_changed'],
            'trade' => ['goods_exchanged', 'agreement_made'],
            'conflict' => ['tension_increased', 'resolution_achieved'],
            'cooperation' => ['project_started', 'alliance_formed'],
        ];
        
        $possibleOutcomes = $outcomes[$type] ?? ['neutral'];
        $outcome = $possibleOutcomes[array_rand($possibleOutcomes)];
        
        return [
            'result' => $outcome,
            'impact_on_npc1' => $this->calculateInteractionImpact($npc1, $outcome),
            'impact_on_npc2' => $this->calculateInteractionImpact($npc2, $outcome),
        ];
    }

    protected function calculateInteractionImpact(array $npc, string $outcome): array
    {
        $impacts = [
            'information_shared' => ['knowledge' => +1, 'trust' => 0],
            'relationship_changed' => ['trust' => +1, 'influence' => 0],
            'goods_exchanged' => ['wealth' => +1, 'satisfaction' => 0],
            'agreement_made' => ['trust' => +1, 'cooperation' => +1],
            'tension_increased' => ['trust' => -1, 'stress' => +1],
            'resolution_achieved' => ['stress' => -1, 'trust' => +1],
            'project_started' => ['purpose' => +1, 'cooperation' => +1],
            'alliance_formed' => ['power' => +1, 'security' => +1],
        ];
        
        return $impacts[$outcome] ?? ['neutral' => 0];
    }

    protected function isEventRelevantToNPC(array $event, array $npc): bool
    {
        // Kiểm tra xem NPC có tham gia sự kiện không
        foreach ($event['participants'] ?? [] as $participant) {
            if ($participant['name'] === $npc['name']) {
                return true;
            }
        }
        
        // Kiểm tra xem sự kiện có liên quan đến phe của NPC không
        foreach ($event['participants'] ?? [] as $participant) {
            if ($participant['type'] === 'faction' && $participant['name'] === $npc['faction']) {
                return true;
            }
        }
        
        return false;
    }

    protected function generateFallbackIntegration(WorldState $world): array
    {
        return [
            'npcs' => [],
            'events' => [],
            'stories' => [],
            'analytics' => [
                'world_trends' => [
                    'trends' => [
                        [
                            'category' => 'general',
                            'title' => 'Xu hướng ổn định',
                            'direction' => 'stable',
                            'confidence' => 0.5,
                        ],
                    ],
                ],
            ],
            'interactions' => [],
        ];
    }

    protected function logIntegrationSuccess(WorldState $world, array $results): void
    {
        Log::info('AI integration completed successfully', [
            'world_id' => $world->id ?? 'unknown',
            'npcs_generated' => count($results['npcs'] ?? []),
            'events_generated' => count($results['events'] ?? []),
            'stories_generated' => count($results['stories'] ?? []),
            'analytics_completed' => !empty($results['analytics']),
            'interactions_generated' => count($results['interactions'] ?? []),
        ]);
    }

    /**
     * Tải cài đặt AI
     */
    protected function loadAISettings(): array
    {
        return [
            'enabled' => config('ai.enabled', false),
            'story_generation' => config('ai.story_generation.enabled', true),
            'intelligent_npcs' => config('ai.intelligent_npcs.enabled', true),
            'dynamic_events' => config('ai.dynamic_events.enabled', true),
            'predictive_analytics' => config('ai.predictive_analytics.enabled', true),
            'max_npcs_per_world' => config('ai.max_npcs_per_world', 10),
            'max_events_per_world' => config('ai.max_events_per_world', 20),
        ];
    }

    /**
     * Bật/tắt AI
     */
    public function setAIEnabled(bool $enabled): void
    {
        $this->aiEnabled = $enabled;
        $this->aiSettings['enabled'] = $enabled;
    }

    /**
     * Kiểm tra AI có được bật không
     */
    public function isAIEnabled(): bool
    {
        return $this->aiEnabled;
    }

    /**
     * Lấy thống kê AI
     */
    public function getAIStatistics(): array
    {
        return [
            'ai_enabled' => $this->aiEnabled,
            'settings' => $this->aiSettings,
            'story_generator_stats' => $this->storyGenerator->getStatistics(),
            'npc_manager_stats' => $this->npcManager->getStatistics(),
            'event_generator_stats' => $this->eventGenerator->getStatistics(),
            'analytics_stats' => $this->analytics->getStatistics(),
        ];
    }

    /**
     * Xóa cache AI
     */
    public function clearAICache(): void
    {
        $this->storyGenerator->clearCache();
        $this->analytics->clearCache();
    }

    /**
     * Cập nhật cài đặt AI
     */
    public function updateAISettings(array $settings): void
    {
        $this->aiSettings = array_merge($this->aiSettings, $settings);
        $this->aiEnabled = $this->aiSettings['enabled'] ?? false;
    }
}
