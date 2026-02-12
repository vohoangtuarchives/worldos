<?php

namespace App\Services\AI;

use App\StoryEngine\WorldState;
use App\StoryEngine\CharacterState;
use App\StoryEngine\FactionState;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class IntelligentNPC
{
    private string $apiKey;
    private string $model;
    private array $personalities;
    private array $memoryCache;
    private array $behaviorPatterns;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY'));
        $this->model = config('ai.npc_model', 'gpt-3.5-turbo');
        $this->personalities = $this->loadPersonalities();
        $this->memoryCache = [];
        $this->behaviorPatterns = $this->loadBehaviorPatterns();
    }

    /**
     * Tạo NPC thông minh
     */
    public function createNPC(string $name, string $faction, string $role, array $traits): array
    {
        $npc = [
            'id' => uniqid('npc_'),
            'name' => $name,
            'faction' => $faction,
            'role' => $role,
            'traits' => $traits,
            'personality' => $this->generatePersonality($traits),
            'memory' => [],
            'relationships' => [],
            'goals' => $this->generateGoals($role, $traits),
            'current_action' => null,
            'location' => null,
            'mood' => 'neutral',
            'trust_level' => 50,
            'created_at' => now()->toISOString(),
        ];

        $this->initializeNPCMemory($npc);
        
        Log::info('Intelligent NPC created', [
            'npc_id' => $npc['id'],
            'name' => $name,
            'faction' => $faction,
            'role' => $role,
        ]);

        return $npc;
    }

    /**
     * Tạo personality cho NPC
     */
    protected function generatePersonality(array $traits): array
    {
        $basePersonalities = [
            'brave' => ['courage' => 0.8, 'risk_taking' => 0.7, 'leadership' => 0.6],
            'cautious' => ['courage' => 0.3, 'risk_taking' => 0.2, 'planning' => 0.8],
            'aggressive' => ['aggression' => 0.8, 'dominance' => 0.7, 'impulsiveness' => 0.6],
            'diplomatic' => ['charisma' => 0.8, 'negotiation' => 0.7, 'patience' => 0.6],
            'scholarly' => ['intelligence' => 0.8, 'curiosity' => 0.7, 'wisdom' => 0.6],
            'mysterious' => ['secrecy' => 0.8, 'intrigue' => 0.7, 'observation' => 0.6],
        ];

        $personality = ['base' => 0.5]; // Giá trị mặc định

        foreach ($traits as $trait) {
            if (isset($basePersonalities[$trait])) {
                foreach ($basePersonalities[$trait] as $key => $value) {
                    $personality[$key] = ($personality[$key] ?? 0.5) * 0.7 + $value * 0.3;
                }
            }
        }

        // Chuẩn hóa giá trị
        foreach ($personality as $key => $value) {
            $personality[$key] = max(0, min(1, $value));
        }

        return $personality;
    }

    /**
     * Tạo mục tiêu cho NPC
     */
    protected function generateGoals(string $role, array $traits): array
    {
        $goalTemplates = [
            'leader' => [
                'Mở rộng ảnh hưởng của phe phái',
                'Duy trì hòa bình trong khu vực',
                'Tăng cường sức mạnh quân sự',
                'Cải thiện kinh tế',
            ],
            'advisor' => [
                'Cung cấp lời khuyên sáng suốt',
                'Phân tích tình hình chính trị',
                'Dự đoán các mối đe dọa',
                'Duy trì sự ổn định',
            ],
            'merchant' => [
                'Tối đa hóa lợi nhuận',
                'Mở rộng mạng lưới thương mại',
                'Tìm kiếm cơ hội mới',
                'Xây dựng quan hệ',
            ],
            'scholar' => [
                'Nghiên cứu kiến thức mới',
                'Bảo tồn trí tuệ cổ xưa',
                'Giảng dạy cho thế hệ trẻ',
                'Giải quyết các bí ẩn',
            ],
            'spy' => [
                'Thu thập thông tin tình báo',
                'Thâm nhập vào các phe thù địch',
                'Bảo vệ bí mật phe phái',
                'Phá hoại kẻ thù',
            ],
        ];

        $goals = $goalTemplates[$role] ?? $goalTemplates['advisor'];

        // Thêm mục tiêu dựa trên traits
        if (in_array('aggressive', $traits)) {
            $goals[] = 'Thống trị các phe khác';
        }
        
        if (in_array('diplomatic', $traits)) {
            $goals[] = 'Xây dựng liên minh';
        }

        return array_unique($goals);
    }

    /**
     * Khởi tạo memory cho NPC
     */
    protected function initializeNPCMemory(array &$npc): void
    {
        $npc['memory'] = [
            'short_term' => [], // 10 sự kiện gần nhất
            'long_term' => [], // Sự kiện quan trọng
            'people_met' => [], // Những người đã gặp
            'places_visited' => [], // Những nơi đã đến
            'skills_learned' => [], // Kỹ năng đã học
            'secrets_known' => [], // Bí mật đã biết
        ];
    }

    /**
     * NPC ra quyết định
     */
    public function makeDecision(array $npc, WorldState $world, array $situation): array
    {
        $context = $this->buildDecisionContext($npc, $world, $situation);
        $options = $this->generateOptions($npc, $context);
        $evaluation = $this->evaluateOptions($npc, $options, $context);
        $decision = $this->selectBestOption($evaluation);

        // Lưu quyết định vào memory
        $this->addToMemory($npc, 'decision_made', [
            'situation' => $situation,
            'decision' => $decision,
            'context' => $context,
            'timestamp' => now()->toISOString(),
        ]);

        Log::info('NPC decision made', [
            'npc_id' => $npc['id'],
            'npc_name' => $npc['name'],
            'decision' => $decision['action'],
            'confidence' => $decision['confidence'],
        ]);

        return $decision;
    }

    /**
     * Xây dựng ngữ cảnh ra quyết định
     */
    protected function buildDecisionContext(array $npc, WorldState $world, array $situation): array
    {
        return [
            'npc_state' => [
                'mood' => $npc['mood'],
                'trust_level' => $npc['trust_level'],
                'current_goal' => $npc['goals'][0] ?? null,
                'recent_memories' => array_slice($npc['memory']['short_term'], -3),
            ],
            'world_state' => [
                'public_awareness' => $world->publicAwareness,
                'power_centers' => $world->powerCenters,
                'faction_tensions' => $this->calculateFactionTensions($world->factions),
                'economic_conditions' => $this->assessEconomicConditions($world),
            ],
            'situation' => $situation,
            'personality_factors' => $npc['personality'],
        ];
    }

    /**
     * Tạo các lựa chọn
     */
    protected function generateOptions(array $npc, array $context): array
    {
        $options = [];
        
        // Lựa chọn dựa trên vai trò
        $roleOptions = $this->getRoleBasedOptions($npc['role'], $context);
        $options = array_merge($options, $roleOptions);
        
        // Lựa chọn dựa trên tính cách
        $personalityOptions = $this->getPersonalityBasedOptions($npc['personality'], $context);
        $options = array_merge($options, $personalityOptions);
        
        // Lựa chọn dựa trên tình huống
        $situationOptions = $this->getSituationBasedOptions($context['situation'], $context);
        $options = array_merge($options, $situationOptions);

        return array_unique($options, SORT_REGULAR);
    }

    /**
     * Lấy lựa chọn dựa trên vai trò
     */
    protected function getRoleBasedOptions(string $role, array $context): array
    {
        $roleOptions = [
            'leader' => [
                ['action' => 'call_meeting', 'description' => 'Triệu tập cuộc họp phe phái'],
                ['action' => 'make_alliance', 'description' => 'Đề nghị liên minh'],
                ['action' => 'military_action', 'description' => 'Hành động quân sự'],
                ['action' => 'diplomatic_negotiation', 'description' => 'Đàm phán ngoại giao'],
            ],
            'advisor' => [
                ['action' => 'analyze_situation', 'description' => 'Phân tích tình hình'],
                ['action' => 'provide_advice', 'description' => 'Đưa ra lời khuyên'],
                ['action' => 'gather_information', 'description' => 'Thu thập thông tin'],
                ['action' => 'strategic_planning', 'description' => 'Lập kế hoạch chiến lược'],
            ],
            'merchant' => [
                ['action' => 'seek_profit', 'description' => 'Tìm kiếm lợi nhuận'],
                ['action' => 'expand_trade', 'description' => 'Mở rộng thương mại'],
                ['action' => 'negotiate_deal', 'description' => 'Đàm phán giao dịch'],
                ['action' => 'assess_market', 'description' => 'Đánh giá thị trường'],
            ],
            'scholar' => [
                ['action' => 'research', 'description' => 'Nghiên cứu'],
                ['action' => 'share_knowledge', 'description' => 'Chia sẻ kiến thức'],
                ['action' => 'seek_wisdom', 'description' => 'Tìm kiếm trí tuệ'],
                ['action' => 'document_findings', 'description' => 'Ghi lại phát hiện'],
            ],
        ];

        return $roleOptions[$role] ?? $roleOptions['advisor'];
    }

    /**
     * Lấy lựa chọn dựa trên tính cách
     */
    protected function getPersonalityBasedOptions(array $personality, array $context): array
    {
        $options = [];

        if (($personality['courage'] ?? 0.5) > 0.7) {
            $options[] = ['action' => 'bold_action', 'description' => 'Hành động dũng cảm'];
        }

        if (($personality['caution'] ?? 0.5) > 0.7) {
            $options[] = ['action' => 'careful_planning', 'description' => 'Lập kế hoạch cẩn thận'];
        }

        if (($personality['aggression'] ?? 0.5) > 0.7) {
            $options[] = ['action' => 'aggressive_response', 'description' => 'Phản ứng mạnh mẽ'];
        }

        if (($personality['charisma'] ?? 0.5) > 0.7) {
            $options[] = ['action' => 'persuade_others', 'description' => 'Thuyết phục người khác'];
        }

        return $options;
    }

    /**
     * Lấy lựa chọn dựa trên tình huống
     */
    protected function getSituationBasedOptions(array $situation, array $context): array
    {
        $options = [];
        $situationType = $situation['type'] ?? 'general';

        switch ($situationType) {
            case 'conflict':
                $options[] = ['action' => 'mediate_conflict', 'description' => 'Hòa giải xung đột'];
                $options[] = ['action' => 'take_sides', 'description' => 'Chọn phe'];
                $options[] = ['action' => 'remain_neutral', 'description' => 'Giữ trung lập'];
                break;
                
            case 'opportunity':
                $options[] = ['action' => 'seize_opportunity', 'description' => 'Nắm bắt cơ hội'];
                $options[] = ['action' => 'analyze_opportunity', 'description' => 'Phân tích cơ hội'];
                $options[] = ['action' => 'share_opportunity', 'description' => 'Chia sẻ cơ hội'];
                break;
                
            case 'crisis':
                $options[] = ['action' => 'emergency_response', 'description' => 'Phản ứng khẩn cấp'];
                $options[] = ['action' => 'seek_help', 'description' => 'Tìm kiếm sự giúp đỡ'];
                $options[] = ['action' => 'protect_assets', 'description' => 'Bảo vệ tài sản'];
                break;
        }

        return $options;
    }

    /**
     * Đánh giá các lựa chọn
     */
    protected function evaluateOptions(array $npc, array $options, array $context): array
    {
        $evaluations = [];

        foreach ($options as $option) {
            $score = $this->calculateOptionScore($npc, $option, $context);
            $evaluations[] = array_merge($option, [
                'score' => $score,
                'confidence' => $this->calculateConfidence($npc, $option, $context),
            ]);
        }

        // Sắp xếp theo điểm số
        usort($evaluations, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $evaluations;
    }

    /**
     * Tính điểm cho lựa chọn
     */
    protected function calculateOptionScore(array $npc, array $option, array $context): float
    {
        $score = 0.5; // Điểm cơ bản

        // Điểm phù hợp với vai trò
        $roleFit = $this->calculateRoleFit($npc['role'], $option['action']);
        $score += $roleFit * 0.3;

        // Điểm phù hợp với tính cách
        $personalityFit = $this->calculatePersonalityFit($npc['personality'], $option['action']);
        $score += $personalityFit * 0.3;

        // Điểm phù hợp với tình huống
        $situationFit = $this->calculateSituationFit($option['action'], $context['situation']);
        $score += $situationFit * 0.2;

        // Điểm dựa trên kinh nghiệm quá khứ
        $experienceFit = $this->calculateExperienceFit($npc, $option['action']);
        $score += $experienceFit * 0.2;

        return max(0, min(1, $score));
    }

    /**
     * Tính độ phù hợp vai trò
     */
    protected function calculateRoleFit(string $role, string $action): float
    {
        $roleActions = [
            'leader' => ['call_meeting', 'make_alliance', 'military_action', 'diplomatic_negotiation'],
            'advisor' => ['analyze_situation', 'provide_advice', 'gather_information', 'strategic_planning'],
            'merchant' => ['seek_profit', 'expand_trade', 'negotiate_deal', 'assess_market'],
            'scholar' => ['research', 'share_knowledge', 'seek_wisdom', 'document_findings'],
        ];

        return in_array($action, $roleActions[$role] ?? []) ? 1.0 : 0.3;
    }

    /**
     * Tính độ phù hợp tính cách
     */
    protected function calculatePersonalityFit(array $personality, string $action): float
    {
        $actionPersonalities = [
            'bold_action' => ['courage' => 0.8, 'risk_taking' => 0.7],
            'careful_planning' => ['caution' => 0.8, 'planning' => 0.7],
            'aggressive_response' => ['aggression' => 0.8, 'dominance' => 0.7],
            'persuade_others' => ['charisma' => 0.8, 'negotiation' => 0.7],
        ];

        if (!isset($actionPersonalities[$action])) {
            return 0.5;
        }

        $requiredTraits = $actionPersonalities[$action];
        $fit = 0;

        foreach ($requiredTraits as $trait => $requiredValue) {
            $actualValue = $personality[$trait] ?? 0.5;
            $fit += min($actualValue, $requiredValue);
        }

        return $fit / count($requiredTraits);
    }

    /**
     * Tính độ phù hợp tình huống
     */
    protected function calculateSituationFit(string $action, array $situation): float
    {
        $situationType = $situation['type'] ?? 'general';
        
        $situationActions = [
            'conflict' => ['mediate_conflict', 'take_sides', 'remain_neutral'],
            'opportunity' => ['seize_opportunity', 'analyze_opportunity', 'share_opportunity'],
            'crisis' => ['emergency_response', 'seek_help', 'protect_assets'],
        ];

        return in_array($action, $situationActions[$situationType] ?? []) ? 1.0 : 0.5;
    }

    /**
     * Tính độ phù hợp kinh nghiệm
     */
    protected function calculateExperienceFit(array $npc, string $action): float
    {
        // Kiểm tra xem NPC đã thực hiện hành động này trước đây chưa
        $recentMemories = $npc['memory']['short_term'] ?? [];
        
        foreach ($recentMemories as $memory) {
            if (isset($memory['action']) && $memory['action'] === $action) {
                return 0.8; // Có kinh nghiệm
            }
        }

        return 0.5; // Không có kinh nghiệm
    }

    /**
     * Tính độ tự tin
     */
    protected function calculateConfidence(array $npc, array $option, array $context): float
    {
        $baseConfidence = 0.7;
        
        // Điều chỉnh dựa trên tâm trạng
        $moodAdjustment = $npc['mood'] === 'confident' ? 0.2 : ($npc['mood'] === 'nervous' ? -0.2 : 0);
        
        // Điều chỉnh dựa trên độ phức tạp của tình huống
        $complexity = count($context['situation']['factors'] ?? []);
        $complexityAdjustment = min(0, -0.1 * $complexity);
        
        return max(0.1, min(1.0, $baseConfidence + $moodAdjustment + $complexityAdjustment));
    }

    /**
     * Chọn lựa chọn tốt nhất
     */
    protected function selectBestOption(array $evaluations): array
    {
        if (empty($evaluations)) {
            return [
                'action' => 'wait',
                'description' => 'Chờ đợi thêm thông tin',
                'score' => 0.5,
                'confidence' => 0.5,
            ];
        }

        $best = $evaluations[0];
        
        // Thêm yếu tố ngẫu nhiên để tạo sự tự nhiên
        if ($best['confidence'] < 0.8 && rand(1, 100) <= 20) {
            // 20% cơ hội chọn lựa chọn thứ hai nếu không quá tự tin
            if (count($evaluations) > 1) {
                $best = $evaluations[1];
            }
        }

        return $best;
    }

    /**
     * Thêm vào memory của NPC
     */
    protected function addToMemory(array &$npc, string $type, array $data): void
    {
        $memory = [
            'type' => $type,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];

        // Thêm vào short-term memory
        array_unshift($npc['memory']['short_term'], $memory);
        $npc['memory']['short_term'] = array_slice($npc['memory']['short_term'], 0, 10);

        // Thêm vào long-term memory nếu quan trọng
        if ($this->isImportantMemory($type, $data)) {
            $npc['memory']['long_term'][] = $memory;
        }
    }

    /**
     * Kiểm tra memory có quan trọng không
     */
    protected function isImportantMemory(string $type, array $data): bool
    {
        $importantTypes = ['decision_made', 'major_event', 'relationship_change', 'goal_achieved'];
        return in_array($type, $importantTypes);
    }

    /**
     * Tính toán căng thẳng giữa các phe
     */
    protected function calculateFactionTensions(array $factions): float
    {
        if (count($factions) < 2) {
            return 0.0;
        }

        // Logic đơn giản để tính căng thẳng
        $tension = 0.0;
        $factionCount = count($factions);

        foreach ($factions as $faction) {
            $power = $faction->militaryPower ?? 5;
            $cohesion = $faction->cohesion ?? 50;
            
            // Căng thẳng tăng khi sức mạnh cao nhưng gắn kết thấp
            $tension += ($power / 10) * (1 - $cohesion / 100);
        }

        return $tension / $factionCount;
    }

    /**
     * Đánh giá điều kiện kinh tế
     */
    protected function assessEconomicConditions(WorldState $world): string
    {
        $awareness = $world->publicAwareness;
        $powerCenters = $world->powerCenters;

        if ($awareness > 7 && $powerCenters > 3) {
            return 'prosperous';
        } elseif ($awareness > 5 && $powerCenters > 2) {
            return 'stable';
        } elseif ($awareness > 3) {
            return 'developing';
        } else {
            return 'struggling';
        }
    }

    /**
     * Tải personalities
     */
    protected function loadPersonalities(): array
    {
        return [
            'brave' => ['courage' => 0.8, 'risk_taking' => 0.7],
            'cautious' => ['courage' => 0.3, 'risk_taking' => 0.2],
            'aggressive' => ['aggression' => 0.8, 'dominance' => 0.7],
            'diplomatic' => ['charisma' => 0.8, 'negotiation' => 0.7],
            'scholarly' => ['intelligence' => 0.8, 'curiosity' => 0.7],
            'mysterious' => ['secrecy' => 0.8, 'intrigue' => 0.7],
        ];
    }

    /**
     * Tải behavior patterns
     */
    protected function loadBehaviorPatterns(): array
    {
        return [
            'daily_routine' => [
                'morning' => ['plan_day', 'check_status'],
                'afternoon' => ['execute_tasks', 'interact'],
                'evening' => ['reflect', 'plan_tomorrow'],
            ],
            'interaction_styles' => [
                'formal' => ['respectful', 'professional'],
                'casual' => ['friendly', 'relaxed'],
                'aggressive' => ['dominant', 'challenging'],
            ],
        ];
    }

    /**
     * Lấy thống kê NPC
     */
    public function getStatistics(): array
    {
        return [
            'npc_count' => count($this->memoryCache),
            'api_key_configured' => !empty($this->apiKey),
            'model' => $this->model,
            'personalities_loaded' => count($this->personalities),
        ];
    }
}
