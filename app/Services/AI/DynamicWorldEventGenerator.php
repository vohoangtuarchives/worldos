<?php

namespace App\Services\AI;

use App\StoryEngine\WorldState;
use App\StoryEngine\CharacterState;
use App\StoryEngine\FactionState;
use App\StoryEngine\Seed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DynamicWorldEventGenerator
{
    private string $apiKey;
    private string $model;
    private array $eventTemplates;
    private array $worldHistory;
    private array $eventProbabilities;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY'));
        $this->model = config('ai.event_model', 'gpt-3.5-turbo');
        $this->eventTemplates = $this->loadEventTemplates();
        $this->worldHistory = [];
        $this->eventProbabilities = $this->initializeEventProbabilities();
    }

    /**
     * Tạo sự kiện thế giới động dựa trên trạng thái hiện tại
     */
    public function generateDynamicEvent(WorldState $world, array $activeNPCs = []): array
    {
        try {
            $context = $this->buildEventContext($world, $activeNPCs);
            $eventType = $this->selectEventType($context);
            $event = $this->generateEvent($eventType, $context);
            
            $this->recordEventInHistory($event);
            $this->updateEventProbabilities($event, $context);
            
            Log::info('Dynamic world event generated', [
                'event_type' => $eventType,
                'event_title' => $event['title'],
                'world_id' => $world->id ?? 'unknown',
            ]);
            
            return $event;
            
        } catch (\Exception $e) {
            Log::error('Dynamic event generation failed', [
                'error' => $e->getMessage(),
                'world_id' => $world->id ?? 'unknown',
            ]);
            
            return $this->generateFallbackEvent($world);
        }
    }

    /**
     * Xây dựng ngữ cảnh cho sự kiện
     */
    protected function buildEventContext(WorldState $world, array $activeNPCs): array
    {
        return [
            'world_state' => [
                'public_awareness' => $world->publicAwareness,
                'power_centers' => $world->powerCenters,
                'tier_index' => $world->tierIndex,
                'faction_count' => count($world->factions),
            ],
            'factions' => $this->analyzeFactions($world->factions),
            'npcs' => $this->analyzeNPCs($activeNPCs),
            'recent_events' => $this->getRecentEvents(5),
            'world_mood' => $this->calculateWorldMood($world),
            'tension_level' => $this->calculateTensionLevel($world),
            'opportunity_level' => $this->calculateOpportunityLevel($world),
        ];
    }

    /**
     * Phân tích các phe phái
     */
    protected function analyzeFactions(array $factions): array
    {
        $analysis = [];
        
        foreach ($factions as $faction) {
            $analysis[] = [
                'name' => $faction->name,
                'type' => $faction->type,
                'power_level' => $faction->militaryPower ?? 5,
                'cohesion' => $faction->cohesion ?? 50,
                'economic_status' => $faction->economy ?? 'stable',
                'aggression_level' => $this->calculateAggressionLevel($faction),
                'diplomatic_stance' => $this->calculateDiplomaticStance($faction),
            ];
        }
        
        return $analysis;
    }

    /**
     * Phân tích các NPC
     */
    protected function analyzeNPCs(array $npcs): array
    {
        $analysis = [];
        
        foreach ($npcs as $npc) {
            $analysis[] = [
                'name' => $npc['name'] ?? 'Unknown',
                'faction' => $npc['faction'] ?? 'independent',
                'role' => $npc['role'] ?? 'commoner',
                'influence' => $npc['influence'] ?? 0,
                'current_goal' => $npc['goals'][0] ?? 'survival',
                'mood' => $npc['mood'] ?? 'neutral',
                'recent_actions' => $this->getRecentNPCActions($npc),
            ];
        }
        
        return $analysis;
    }

    /**
     * Chọn loại sự kiện dựa trên ngữ cảnh
     */
    protected function selectEventType(array $context): string
    {
        $probabilities = $this->eventProbabilities;
        
        // Điều chỉnh xác suất dựa trên ngữ cảnh
        if ($context['tension_level'] > 0.7) {
            $probabilities['conflict'] *= 1.5;
            $probabilities['crisis'] *= 1.3;
        }
        
        if ($context['opportunity_level'] > 0.7) {
            $probabilities['opportunity'] *= 1.5;
            $probabilities['discovery'] *= 1.3;
        }
        
        if ($context['world_mood'] === 'prosperous') {
            $probabilities['celebration'] *= 1.5;
            $probabilities['alliance'] *= 1.3;
        }
        
        if ($context['world_mood'] === 'tense') {
            $probabilities['conflict'] *= 1.5;
            $probabilities['betrayal'] *= 1.3;
        }
        
        // Chuẩn hóa xác suất
        $total = array_sum($probabilities);
        foreach ($probabilities as $key => $value) {
            $probabilities[$key] = $value / $total;
        }
        
        // Chọn ngẫu nhiên dựa trên xác suất
        $random = mt_rand() / mt_getrandmax();
        $cumulative = 0;
        
        foreach ($probabilities as $type => $probability) {
            $cumulative += $probability;
            if ($random <= $cumulative) {
                return $type;
            }
        }
        
        return 'general'; // Mặc định
    }

    /**
     * Tạo sự kiện chi tiết
     */
    protected function generateEvent(string $eventType, array $context): array
    {
        $template = $this->eventTemplates[$eventType] ?? $this->eventTemplates['general'];
        
        $event = [
            'id' => uniqid('event_'),
            'type' => $eventType,
            'title' => $this->generateEventTitle($eventType, $context),
            'description' => $this->generateEventDescription($template, $context),
            'participants' => $this->selectEventParticipants($eventType, $context),
            'location' => $this->selectEventLocation($eventType, $context),
            'severity' => $this->calculateEventSeverity($eventType, $context),
            'duration' => $this->calculateEventDuration($eventType),
            'consequences' => $this->generateEventConsequences($eventType, $context),
            'requirements' => $this->generateEventRequirements($eventType, $context),
            'rewards' => $this->generateEventRewards($eventType, $context),
            'created_at' => now()->toISOString(),
            'expires_at' => $this->calculateEventExpiration($eventType),
        ];
        
        return $event;
    }

    /**
     * Tạo tiêu đề sự kiện
     */
    protected function generateEventTitle(string $eventType, array $context): string
    {
        $titleTemplates = [
            'conflict' => [
                'Xung đột nổ ra tại {location}',
                'Chiến tranh giữa {faction1} và {faction2}',
                'Cuộc tấn công bất ngờ',
                'Sự đối đầu căng thẳng',
            ],
            'opportunity' => [
                'Cơ hội kinh tế mới xuất hiện',
                'Khám phá tài nguyên quý giá',
                'Thỏa thuận thương mại có lợi',
                'Con đường mới mở ra',
            ],
            'crisis' => [
                'Khủng hoảng {crisis_type} lan rộng',
                'Thảm họa tấn công {location}',
                'Hệ thống sụp đổ',
                'Tình trạng khẩn cấp',
            ],
            'discovery' => [
                'Phát hiện quan trọng tại {location}',
                'Bí mật cổ xưa được tiết lộ',
                'Công nghệ mới được tìm thấy',
                'Kiến thức thất truyền được phục hồi',
            ],
            'alliance' => [
                'Liên minh được hình thành',
                'Thỏa thuận hòa bình ký kết',
                'Hợp tác chiến lược',
                'Sự đoàn kết các phe phái',
            ],
            'betrayal' => [
                'Sự phản bội gây chấn động',
                'Liên minh bị phá vỡ',
                'Kẻ phản bội bị lộ',
                'Niềm tin bị phản bội',
            ],
            'celebration' => [
                'Lễ hội lớn được tổ chức',
                'Ngày chiến thắng được kỷ niệm',
                'Sự kiện vui vẻ',
                'Thời điểm ăn mừng',
            ],
        ];
        
        $templates = $titleTemplates[$eventType] ?? $titleTemplates['general'];
        $template = $templates[array_rand($templates)];
        
        return $this->replaceTitleVariables($template, $context);
    }

    /**
     * Tạo mô tả sự kiện
     */
    protected function generateEventDescription(array $template, array $context): string
    {
        $description = $template['description'] ?? 'Một sự kiện quan trọng đang diễn ra.';
        
        // Thêm chi tiết dựa trên ngữ cảnh
        $details = [];
        
        if ($context['tension_level'] > 0.7) {
            $details[] = 'Bầu không khí căng thẳng bao trùm.';
        }
        
        if ($context['opportunity_level'] > 0.7) {
            $details[] = 'Nhiều cơ hội mới đang mở ra.';
        }
        
        if (!empty($context['npcs'])) {
            $influentialNPCs = array_filter($context['npcs'], fn($npc) => ($npc['influence'] ?? 0) > 5);
            if (!empty($influentialNPCs)) {
                $npcNames = array_map(fn($npc) => $npc['name'], $influentialNPCs);
                $details[] = 'Các nhân vật quan trọng như ' . implode(', ', $npcNames) . ' đang theo dõi.';
            }
        }
        
        if (!empty($details)) {
            $description .= ' ' . implode(' ', $details);
        }
        
        return $description;
    }

    /**
     * Chọn người tham gia sự kiện
     */
    protected function selectEventParticipants(string $eventType, array $context): array
    {
        $participants = [];
        
        // Chọn phe phái tham gia
        if (!empty($context['factions'])) {
            $factionCount = min(3, count($context['factions']));
            $selectedFactions = array_rand($context['factions'], $factionCount);
            
            if (!is_array($selectedFactions)) {
                $selectedFactions = [$selectedFactions];
            }
            
            foreach ($selectedFactions as $index) {
                $participants[] = [
                    'type' => 'faction',
                    'name' => $context['factions'][$index]['name'],
                    'role' => $this->determineFactionRole($eventType, $context['factions'][$index]),
                ];
            }
        }
        
        // Chọn NPC tham gia
        if (!empty($context['npcs'])) {
            $npcCount = min(2, count($context['npcs']));
            $selectedNPCs = array_rand($context['npcs'], $npcCount);
            
            if (!is_array($selectedNPCs)) {
                $selectedNPCs = [$selectedNPCs];
            }
            
            foreach ($selectedNPCs as $index) {
                $participants[] = [
                    'type' => 'npc',
                    'name' => $context['npcs'][$index]['name'],
                    'role' => $this->determineNPCRole($eventType, $context['npcs'][$index]),
                ];
            }
        }
        
        return $participants;
    }

    /**
     * Chọn địa điểm sự kiện
     */
    protected function selectEventLocation(string $eventType, array $context): string
    {
        $locations = [
            'conflict' => ['Biên giới', 'Thủ đô', 'Căng cứ quân sự', 'Vùng chiến sự'],
            'opportunity' => ['Thị trường', 'Cảng biển', 'Mỏ mỏ', 'Trung tâm thương mại'],
            'crisis' => ['Thành phố', 'Làng mạc', 'Cơ sở hạ tầng', 'Khu dân cư'],
            'discovery' => ['Di tích cổ', 'Thư viện', 'Phòng thí nghiệm', 'Vùng đất xa xôi'],
            'alliance' => ['Hội trường', 'Cung điện', 'Ngoại giao quán', 'Trung tâm hội nghị'],
            'betrayal' => ['Hội nghị bí mật', 'Nơi hẹn ước', 'Trụ sở phe', 'Vùng trung lập'],
            'celebration' => ['Quảng trường', 'Đền thờ', 'Nhà hát', 'Công viên'],
        ];
        
        $eventLocations = $locations[$eventType] ?? $locations['general'];
        return $eventLocations[array_rand($eventLocations)];
    }

    /**
     * Tính mức độ nghiêm trọng của sự kiện
     */
    protected function calculateEventSeverity(string $eventType, array $context): int
    {
        $baseSeverity = [
            'conflict' => 7,
            'opportunity' => 4,
            'crisis' => 9,
            'discovery' => 5,
            'alliance' => 3,
            'betrayal' => 8,
            'celebration' => 2,
        ];
        
        $severity = $baseSeverity[$eventType] ?? 5;
        
        // Điều chỉnh dựa trên ngữ cảnh
        if ($context['tension_level'] > 0.8) {
            $severity = min(10, $severity + 2);
        }
        
        if ($context['opportunity_level'] > 0.8 && in_array($eventType, ['opportunity', 'discovery'])) {
            $severity = min(10, $severity + 1);
        }
        
        return max(1, min(10, $severity));
    }

    /**
     * Tính thời lượng sự kiện
     */
    protected function calculateEventDuration(string $eventType): array
    {
        $durations = [
            'conflict' => ['start' => 'immediate', 'end' => '1_week'],
            'opportunity' => ['start' => 'immediate', 'end' => '3_days'],
            'crisis' => ['start' => 'immediate', 'end' => '2_weeks'],
            'discovery' => ['start' => 'immediate', 'end' => '1_day'],
            'alliance' => ['start' => 'ceremony', 'end' => 'permanent'],
            'betrayal' => ['start' => 'immediate', 'end' => '1_week'],
            'celebration' => ['start' => 'ceremony', 'end' => '1_day'],
        ];
        
        return $durations[$eventType] ?? $durations['general'];
    }

    /**
     * Tạo hậu quả sự kiện
     */
    protected function generateEventConsequences(string $eventType, array $context): array
    {
        $consequences = [];
        
        switch ($eventType) {
            case 'conflict':
                $consequences = [
                    'power_shift' => 'Sức mạnh các phe thay đổi',
                    'casualties' => 'Có thương vong',
                    'territory_changes' => 'Lãnh thổ có thể thay đổi',
                    'economic_impact' => 'Kinh tế bị ảnh hưởng',
                ];
                break;
                
            case 'opportunity':
                $consequences = [
                    'economic_growth' => 'Kinh tế tăng trưởng',
                    'new_relationships' => 'Quan hệ mới hình thành',
                    'resource_gain' => 'Thu được tài nguyên',
                    'influence_increase' => 'Ảnh hưởng tăng lên',
                ];
                break;
                
            case 'crisis':
                $consequences = [
                    'instability' => 'Tình trạng bất ổn',
                    'losses' => 'Mất mát tài sản và người',
                    'urgent_action_needed' => 'Cần hành động khẩn cấp',
                    'long_term_effects' => 'Tác động dài hạn',
                ];
                break;
                
            case 'discovery':
                $consequences = [
                    'knowledge_gain' => 'Thu được kiến thức mới',
                    'technological_advancement' => 'Công nghệ tiến bộ',
                    'new_possibilities' => 'Khả năng mới mở ra',
                    'competitive_advantage' => 'Lợi thế cạnh tranh',
                ];
                break;
                
            case 'alliance':
                $consequences = [
                    'stability_increase' => 'Sự ổn định tăng',
                    'shared_resources' => 'Chia sẻ tài nguyên',
                    'mutual_defense' => 'Phòng thủ chung',
                    'diplomatic_benefits' => 'Lợi ích ngoại giao',
                ];
                break;
                
            case 'betrayal':
                $consequences = [
                    'trust_breakdown' => 'Sự tin cậy bị phá vỡ',
                    'revenge_cycles' => 'Vòng luẩn quẩn trả thù',
                    'instability' => 'Bất ổn gia tăng',
                    'isolation' => 'Sự cô lập',
                ];
                break;
                
            case 'celebration':
                $consequences = [
                    'morale_boost' => 'Tinh thần tăng cao',
                    'social_cohesion' => 'Sự gắn kết xã hội',
                    'cultural_enrichment' => 'Văn hóa phong phú thêm',
                    'positive_relations' => 'Quan hệ tích cực',
                ];
                break;
        }
        
        return $consequences;
    }

    /**
     * Tạo yêu cầu sự kiện
     */
    protected function generateEventRequirements(string $eventType, array $context): array
    {
        $requirements = [];
        
        switch ($eventType) {
            case 'conflict':
                $requirements = [
                    'military_strength' => 'Sức mạnh quân sự',
                    'resources' => 'Tài nguyên chiến tranh',
                    'leadership' => 'Sự lãnh đạo',
                    'strategy' => 'Chiến lược',
                ];
                break;
                
            case 'opportunity':
                $requirements = [
                    'capital' => 'Vốn đầu tư',
                    'skills' => 'Kỹ năng phù hợp',
                    'connections' => 'Mối quan hệ',
                    'timing' => 'Thời điểm thích hợp',
                ];
                break;
                
            case 'crisis':
                $requirements = [
                    'emergency_response' => 'Phản ứng khẩn cấp',
                    'resources' => 'Tài nguyên cứu trợ',
                    'coordination' => 'Sự phối hợp',
                    'leadership' => 'Sự lãnh đạo',
                ];
                break;
                
            case 'discovery':
                $requirements = [
                    'exploration' => 'Sự khám phá',
                    'knowledge' => 'Kiến thức',
                    'tools' => 'Công cụ phù hợp',
                    'curiosity' => 'Sự tò mò',
                ];
                break;
                
            case 'alliance':
                $requirements = [
                    'trust' => 'Sự tin cậy',
                    'diplomacy' => 'Ngoại giao',
                    'compromise' => 'Sự thỏa hiệp',
                    'mutual_benefits' => 'Lợi ích chung',
                ];
                break;
                
            case 'betrayal':
                $requirements = [
                    'opportunity' => 'Cơ hội phản bội',
                    'motivation' => 'Động cơ',
                    'secrecy' => 'Sự bí mật',
                    'timing' => 'Thời điểm',
                ];
                break;
                
            case 'celebration':
                $requirements = [
                    'resources' => 'Tài nguyên tổ chức',
                    'participants' => 'Người tham gia',
                    'venue' => 'Địa điểm',
                    'planning' => 'Kế hoạch',
                ];
                break;
        }
        
        return $requirements;
    }

    /**
     * Tạo phần thưởng sự kiện
     */
    protected function generateEventRewards(string $eventType, array $context): array
    {
        $rewards = [];
        
        switch ($eventType) {
            case 'conflict':
                $rewards = [
                    'victory_glory' => 'Vinh quang chiến thắng',
                    'territory' => 'Lãnh thổ',
                    'resources' => 'Tài nguyên',
                    'power' => 'Sức mạnh',
                ];
                break;
                
            case 'opportunity':
                $rewards = [
                    'wealth' => 'Sự giàu có',
                    'connections' => 'Mối quan hệ',
                    'reputation' => 'Uy tín',
                    'growth' => 'Sự phát triển',
                ];
                break;
                
            case 'crisis':
                $rewards = [
                    'hero_status' => 'Tình trạng anh hùng',
                    'unity' => 'Sự đoàn kết',
                    'lessons_learned' => 'Bài học kinh nghiệm',
                    'resilience' => 'Sự kiên cường',
                ];
                break;
                
            case 'discovery':
                $rewards = [
                    'knowledge' => 'Kiến thức',
                    'innovation' => 'Sự đổi mới',
                    'advantage' => 'Lợi thế',
                    'prestige' => 'Prestige',
                ];
                break;
                
            case 'alliance':
                $rewards = [
                    'security' => 'An ninh',
                    'prosperity' => 'Sự thịnh vượng',
                    'stability' => 'Sự ổn định',
                    'influence' => 'Ảnh hưởng',
                ];
                break;
                
            case 'betrayal':
                $rewards = [
                    'short_term_gains' => 'Lợi ích ngắn hạn',
                    'power_shift' => 'Chuyển đổi quyền lực',
                    'revenge' => 'Sự trả thù',
                    'chaos' => 'Hỗn loạn',
                ];
                break;
                
            case 'celebration':
                $rewards = [
                    'happiness' => 'Hạnh phúc',
                    'unity' => 'Sự đoàn kết',
                    'culture' => 'Văn hóa',
                    'memories' => 'Ký ức',
                ];
                break;
        }
        
        return $rewards;
    }

    /**
     * Tính thời gian hết hạn sự kiện
     */
    protected function calculateEventExpiration(string $eventType): string
    {
        $expirationTimes = [
            'conflict' => '+1 week',
            'opportunity' => '+3 days',
            'crisis' => '+2 weeks',
            'discovery' => '+1 day',
            'alliance' => '+1 month',
            'betrayal' => '+1 week',
            'celebration' => '+1 day',
        ];
        
        return $expirationTimes[$eventType] ?? '+1 week';
    }

    /**
     * Tạo sự kiện dự phòng
     */
    protected function generateFallbackEvent(WorldState $world): array
    {
        return [
            'id' => uniqid('event_'),
            'type' => 'general',
            'title' => 'Sự kiện thông thường',
            'description' => 'Một sự kiện đang diễn ra trong thế giới.',
            'participants' => [],
            'location' => 'Không xác định',
            'severity' => 3,
            'duration' => ['start' => 'immediate', 'end' => '1_day'],
            'consequences' => ['Thế giới tiếp tục phát triển'],
            'requirements' => ['Sự quan sát'],
            'rewards' => ['Kinh nghiệm'],
            'created_at' => now()->toISOString(),
            'expires_at' => '+1 day',
        ];
    }

    /**
     * Các phương thức hỗ trợ
     */
    protected function calculateAggressionLevel(FactionState $faction): float
    {
        $power = $faction->militaryPower ?? 5;
        $cohesion = $faction->cohesion ?? 50;
        
        return ($power / 10) * (1 - $cohesion / 100);
    }

    protected function calculateDiplomaticStance(FactionState $faction): string
    {
        $cohesion = $faction->cohesion ?? 50;
        
        if ($cohesion > 70) return 'friendly';
        if ($cohesion > 40) return 'neutral';
        return 'hostile';
    }

    protected function calculateWorldMood(WorldState $world): string
    {
        $awareness = $world->publicAwareness;
        $powerCenters = $world->powerCenters;
        
        if ($awareness > 7 && $powerCenters > 3) return 'prosperous';
        if ($awareness > 5) return 'stable';
        if ($awareness > 3) return 'developing';
        return 'tense';
    }

    protected function calculateTensionLevel(WorldState $world): float
    {
        $tension = 0.0;
        
        foreach ($world->factions as $faction) {
            $aggression = $this->calculateAggressionLevel($faction);
            $tension += $aggression;
        }
        
        return min(1.0, $tension / count($world->factions));
    }

    protected function calculateOpportunityLevel(WorldState $world): float
    {
        $awareness = $world->publicAwareness;
        $stability = 1.0 - $this->calculateTensionLevel($world);
        
        return ($awareness / 10) * $stability;
    }

    protected function getRecentEvents(int $count): array
    {
        return array_slice($this->worldHistory, -$count);
    }

    protected function getRecentNPCActions(array $npc): array
    {
        return $npc['recent_actions'] ?? [];
    }

    protected function determineFactionRole(string $eventType, array $faction): string
    {
        $roles = [
            'conflict' => ['aggressor', 'defender', 'neutral'],
            'opportunity' => ['beneficiary', 'partner', 'observer'],
            'alliance' => ['founder', 'member', 'observer'],
        ];
        
        $possibleRoles = $roles[$eventType] ?? ['participant'];
        return $possibleRoles[array_rand($possibleRoles)];
    }

    protected function determineNPCRole(string $eventType, array $npc): string
    {
        $roles = [
            'conflict' => ['leader', 'soldier', 'mediator'],
            'opportunity' => ['investor', 'trader', 'advisor'],
            'crisis' => ['responder', 'victim', 'helper'],
            'discovery' => ['explorer', 'scholar', 'witness'],
        ];
        
        $possibleRoles = $roles[$eventType] ?? ['participant'];
        return $possibleRoles[array_rand($possibleRoles)];
    }

    protected function replaceTitleVariables(string $template, array $context): string
    {
        // Thay thế các biến trong template
        $replacements = [
            '{location}' => $this->selectEventLocation('general', $context),
            '{faction1}' => $context['factions'][0]['name'] ?? 'Phe 1',
            '{faction2}' => $context['factions'][1]['name'] ?? 'Phe 2',
            '{crisis_type}' => 'kinh tế',
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    protected function recordEventInHistory(array $event): void
    {
        array_unshift($this->worldHistory, $event);
        $this->worldHistory = array_slice($this->worldHistory, 0, 100); // Giữ 100 sự kiện gần nhất
    }

    protected function updateEventProbabilities(array $event, array $context): void
    {
        // Cập nhật xác suất dựa trên kết quả sự kiện
        $eventType = $event['type'];
        $success = $this->evaluateEventSuccess($event, $context);
        
        if ($success) {
            $this->eventProbabilities[$eventType] *= 1.1; // Tăng xác suất cho sự kiện thành công
        } else {
            $this->eventProbabilities[$eventType] *= 0.9; // Giảm xác suất cho sự kiện thất bại
        }
        
        // Chuẩn hóa lại xác suất
        $total = array_sum($this->eventProbabilities);
        foreach ($this->eventProbabilities as $key => $value) {
            $this->eventProbabilities[$key] = $value / $total;
        }
    }

    protected function evaluateEventSuccess(array $event, array $context): bool
    {
        // Logic đơn giản để đánh giá thành công
        return ($event['severity'] ?? 5) <= 7; // Sự kiện không quá nghiêm trọng là thành công
    }

    protected function loadEventTemplates(): array
    {
        return [
            'conflict' => [
                'description' => 'Một cuộc xung đột đã nổ ra giữa các lực lượng khác nhau.',
                'duration' => 'short_term',
            ],
            'opportunity' => [
                'description' => 'Một cơ hội quý giá đã xuất hiện.',
                'duration' => 'short_term',
            ],
            'crisis' => [
                'description' => 'Một cuộc khủng hoảng đang đe dọa sự ổn định.',
                'duration' => 'medium_term',
            ],
            'discovery' => [
                'description' => 'Một phát hiện quan trọng đã được thực hiện.',
                'duration' => 'immediate',
            ],
            'alliance' => [
                'description' => 'Các phe phái đã hình thành liên minh.',
                'duration' => 'long_term',
            ],
            'betrayal' => [
                'description' => 'Một sự phản bội đã xảy ra.',
                'duration' => 'short_term',
            ],
            'celebration' => [
                'description' => 'Một sự kiện vui vẻ đang diễn ra.',
                'duration' => 'immediate',
            ],
            'general' => [
                'description' => 'Một sự kiện đang diễn ra trong thế giới.',
                'duration' => 'short_term',
            ],
        ];
    }

    protected function initializeEventProbabilities(): array
    {
        return [
            'conflict' => 0.2,
            'opportunity' => 0.2,
            'crisis' => 0.1,
            'discovery' => 0.15,
            'alliance' => 0.15,
            'betrayal' => 0.1,
            'celebration' => 0.1,
        ];
    }

    /**
     * Lấy thống kê
     */
    public function getStatistics(): array
    {
        return [
            'events_generated' => count($this->worldHistory),
            'api_key_configured' => !empty($this->apiKey),
            'model' => $this->model,
            'event_probabilities' => $this->eventProbabilities,
        ];
    }
}
