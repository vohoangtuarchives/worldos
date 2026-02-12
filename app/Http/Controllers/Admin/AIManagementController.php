<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AI\AIIntegrationService;
use App\Services\AI\AIStoryGenerator;
use App\Services\AI\IntelligentNPC;
use App\Services\AI\DynamicWorldEventGenerator;
use App\Services\AI\PredictiveAnalytics;
use App\StoryEngine\WorldState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AIManagementController extends Controller
{
    private AIIntegrationService $aiService;
    private AIStoryGenerator $storyGenerator;
    private IntelligentNPC $npcManager;
    private DynamicWorldEventGenerator $eventGenerator;
    private PredictiveAnalytics $analytics;

    public function __construct(
        AIIntegrationService $aiService,
        AIStoryGenerator $storyGenerator,
        IntelligentNPC $npcManager,
        DynamicWorldEventGenerator $eventGenerator,
        PredictiveAnalytics $analytics
    ) {
        $this->aiService = $aiService;
        $this->storyGenerator = $storyGenerator;
        $this->npcManager = $npcManager;
        $this->eventGenerator = $eventGenerator;
        $this->analytics = $analytics;
    }

    /**
     * Dashboard quản lý AI
     */
    public function dashboard()
    {
        $stats = $this->aiService->getAIStatistics();
        $settings = $this->aiService->getAIStatistics()['settings'] ?? [];
        
        return view('admin.ai.dashboard', compact('stats', 'settings'));
    }

    /**
     * Tích hợp AI cho thế giới
     */
    public function integrateWorld(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'world_id' => 'required|exists:worlds,id',
            'generate_npcs' => 'boolean',
            'npc_count' => 'integer|min:1|max:20',
            'generate_events' => 'boolean',
            'generate_stories' => 'boolean',
            'run_analytics' => 'boolean',
            'generate_interactions' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $world = WorldState::findOrFail($request->world_id);
            
            $options = [
                'generate_npcs' => $request->generate_npcs ?? true,
                'npc_count' => $request->npc_count ?? 5,
                'generate_events' => $request->generate_events ?? true,
                'generate_stories' => $request->generate_stories ?? true,
                'run_analytics' => $request->run_analytics ?? true,
                'generate_interactions' => $request->generate_interactions ?? true,
            ];

            $result = $this->aiService->integrateAIForWorld($world, $options);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('AI world integration failed', [
                'error' => $e->getMessage(),
                'world_id' => $request->world_id,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo cốt truyện AI
     */
    public function generateStory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'world_id' => 'required|exists:worlds,id',
            'character_id' => 'nullable|string',
            'seeds' => 'array',
            'seeds.*.type' => 'required|string',
            'seeds.*.dimension' => 'required|string',
            'seeds.*.severity' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $world = WorldState::findOrFail($request->world_id);
            $character = new CharacterState();
            
            // Tạo seeds từ request
            $seeds = [];
            foreach ($request->seeds ?? [] as $seedData) {
                $seeds[] = new Seed($seedData['type'], $seedData['dimension'], $seedData['severity']);
            }

            $story = $this->storyGenerator->generateStory($world, $character, $seeds);

            return response()->json([
                'success' => true,
                'story' => $story,
            ]);

        } catch (\Exception $e) {
            Log::error('AI story generation failed', [
                'error' => $e->getMessage(),
                'world_id' => $request->world_id,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo NPC thông minh
     */
    public function createNPC(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'faction' => 'required|string|max:255',
            'role' => 'required|string|in:leader,advisor,merchant,scholar,spy,diplomat,general,influencer',
            'traits' => 'array',
            'traits.*' => 'string|in:brave,cautious,aggressive,diplomatic,scholarly,mysterious',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $npc = $this->npcManager->createNPC(
                $request->name,
                $request->faction,
                $request->role,
                $request->traits ?? ['neutral']
            );

            return response()->json([
                'success' => true,
                'npc' => $npc,
            ]);

        } catch (\Exception $e) {
            Log::error('NPC creation failed', [
                'error' => $e->getMessage(),
                'npc_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * NPC ra quyết định
     */
    public function npcDecision(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'npc_id' => 'required|string',
            'world_id' => 'required|exists:worlds,id',
            'situation' => 'required|array',
            'situation.type' => 'required|string',
            'situation.description' => 'required|string',
            'situation.urgency' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $world = WorldState::findOrFail($request->world_id);
            
            // Lấy NPC (trong thực tế sẽ lấy từ database)
            $npc = $this->getNPCById($request->npc_id);
            
            if (!$npc) {
                return response()->json([
                    'success' => false,
                    'error' => 'NPC not found',
                ], 404);
            }

            $decision = $this->npcManager->makeDecision($npc, $world, $request->situation);

            return response()->json([
                'success' => true,
                'decision' => $decision,
            ]);

        } catch (\Exception $e) {
            Log::error('NPC decision failed', [
                'error' => $e->getMessage(),
                'npc_id' => $request->npc_id,
                'world_id' => $request->world_id,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo sự kiện thế giới động
     */
    public function generateEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'world_id' => 'required|exists:worlds,id',
            'npc_ids' => 'array',
            'npc_ids.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $world = WorldState::findOrFail($request->world_id);
            
            // Lấy NPCs
            $npcs = [];
            foreach ($request->npc_ids ?? [] as $npcId) {
                $npc = $this->getNPCById($npcId);
                if ($npc) {
                    $npcs[] = $npc;
                }
            }

            $event = $this->eventGenerator->generateDynamicEvent($world, $npcs);

            return response()->json([
                'success' => true,
                'event' => $event,
            ]);

        } catch (\Exception $e) {
            Log::error('Dynamic event generation failed', [
                'error' => $e->getMessage(),
                'world_id' => $request->world_id,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Phân tích dự đoán
     */
    public function runAnalytics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'world_id' => 'required|exists:worlds,id',
            'analysis_type' => 'required|string|in:world_trends,social_network,economic_development,player_behavior',
            'historical_events' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $world = WorldState::findOrFail($request->world_id);
            $historicalEvents = $request->historical_events ?? [];

            switch ($request->analysis_type) {
                case 'world_trends':
                    $result = $this->analytics->analyzeWorldTrends($world, $historicalEvents);
                    break;
                case 'social_network':
                    $result = $this->analytics->analyzeSocialNetwork($world->factions, []);
                    break;
                case 'economic_development':
                    $result = $this->analytics->predictEconomicDevelopment($world, []);
                    break;
                case 'player_behavior':
                    $result = $this->analytics->analyzePlayerBehavior([], []);
                    break;
                default:
                    throw new \Exception('Invalid analysis type');
            }

            return response()->json([
                'success' => true,
                'analysis' => $result,
                'analysis_type' => $request->analysis_type,
            ]);

        } catch (\Exception $e) {
            Log::error('Analytics failed', [
                'error' => $e->getMessage(),
                'world_id' => $request->world_id,
                'analysis_type' => $request->analysis_type,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cài đặt AI
     */
    public function settings()
    {
        $stats = $this->aiService->getAIStatistics();
        $settings = $stats['settings'] ?? [];
        
        return view('admin.ai.settings', compact('settings', 'stats'));
    }

    /**
     * Cập nhật cài đặt AI
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enabled' => 'boolean',
            'story_generation' => 'boolean',
            'intelligent_npcs' => 'boolean',
            'dynamic_events' => 'boolean',
            'predictive_analytics' => 'boolean',
            'max_npcs_per_world' => 'integer|min:1|max:50',
            'max_events_per_world' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $settings = [
                'enabled' => $request->enabled ?? false,
                'story_generation' => [
                    'enabled' => $request->story_generation ?? false,
                ],
                'intelligent_npcs' => [
                    'enabled' => $request->intelligent_npcs ?? false,
                ],
                'dynamic_events' => [
                    'enabled' => $request->dynamic_events ?? false,
                ],
                'predictive_analytics' => [
                    'enabled' => $request->predictive_analytics ?? false,
                ],
                'max_npcs_per_world' => $request->max_npcs_per_world ?? 10,
                'max_events_per_world' => $request->max_events_per_world ?? 20,
            ];

            $this->aiService->updateAISettings($settings);

            Log::info('AI settings updated', [
                'settings' => $settings,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cài đặt AI đã được cập nhật',
                'settings' => $settings,
            ]);

        } catch (\Exception $e) {
            Log::error('AI settings update failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bật/tắt AI
     */
    public function toggleAI(Request $request)
    {
        try {
            $enabled = $request->boolean('enabled', false);
            $this->aiService->setAIEnabled($enabled);

            Log::info('AI toggled', [
                'enabled' => $enabled,
            ]);

            return response()->json([
                'success' => true,
                'message' => $enabled ? 'AI đã được bật' : 'AI đã được tắt',
                'enabled' => $enabled,
            ]);

        } catch (\Exception $e) {
            Log::error('AI toggle failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa cache AI
     */
    public function clearCache()
    {
        try {
            $this->aiService->clearAICache();

            Log::info('AI cache cleared');

            return response()->json([
                'success' => true,
                'message' => 'Cache AI đã được xóa',
            ]);

        } catch (\Exception $e) {
            Log::error('AI cache clear failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy thống kê AI
     */
    public function statistics()
    {
        try {
            $stats = $this->aiService->getAIStatistics();

            return response()->json([
                'success' => true,
                'statistics' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('AI statistics failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kiểm tra trạng thái AI
     */
    public function status()
    {
        try {
            $stats = $this->aiService->getAIStatistics();
            
            return response()->json([
                'success' => true,
                'status' => [
                    'ai_enabled' => $stats['ai_enabled'] ?? false,
                    'services_running' => [
                        'story_generator' => !empty($stats['story_generator_stats']),
                        'npc_manager' => !empty($stats['npc_manager_stats']),
                        'event_generator' => !empty($stats['event_generator_stats']),
                        'analytics' => !empty($stats['analytics_stats']),
                    ],
                    'api_keys_configured' => $stats['story_generator_stats']['api_key_configured'] ?? false,
                    'cache_status' => 'active',
                    'last_updated' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('AI status check failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Lấy NPC theo ID
     */
    protected function getNPCById(string $npcId): ?array
    {
        // Trong thực tế, sẽ lấy từ database
        // Hiện tại trả về null để demo
        return null;
    }
}
