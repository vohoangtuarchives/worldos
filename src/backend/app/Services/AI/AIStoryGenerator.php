<?php

namespace App\Services\AI;

use App\StoryEngine\WorldState;
use App\StoryEngine\CharacterState;
use App\StoryEngine\Seed;
use Tuzy\Domain\World\ValueObject\WorldLawProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AIStoryGenerator
{
    private string $apiKey;
    private string $model;
    private array $prompts;
    private array $storyCache;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY'));
        $this->model = config('ai.story_model', 'gpt-3.5-turbo');
        $this->prompts = $this->loadPrompts();
        $this->storyCache = [];
    }

    /**
     * Tạo cốt truyện dựa trên trạng thái thế giới
     */
    public function generateStory(WorldState $world, CharacterState $character, array $seeds): array
    {
        $cacheKey = $this->generateCacheKey($world, $character, $seeds);
        
        if (isset($this->storyCache[$cacheKey])) {
            return $this->storyCache[$cacheKey];
        }

        try {
            $context = $this->buildContext($world, $character, $seeds);
            $prompt = $this->buildStoryPrompt($context);
            $story = $this->callAI($prompt);
            
            $parsedStory = $this->parseStoryResponse($story);
            $this->storyCache[$cacheKey] = $parsedStory;
            
            Log::info('AI story generated successfully', [
                'world_id' => $world->id ?? 'unknown',
                'story_events' => count($parsedStory['events']),
                'cache_key' => $cacheKey,
            ]);
            
            return $parsedStory;
            
        } catch (\Exception $e) {
            Log::error('AI story generation failed', [
                'error' => $e->getMessage(),
                'world_id' => $world->id ?? 'unknown',
            ]);
            
            return $this->generateFallbackStory($world, $character, $seeds);
        }
    }

    /**
     * Xây dựng ngữ cảnh cho AI
     */
    protected function buildContext(WorldState $world, CharacterState $character, array $seeds): array
    {
        return [
            'world_state' => [
                'public_awareness' => $world->publicAwareness,
                'power_centers' => $world->powerCenters,
                'tier_index' => $world->tierIndex,
                'faction_count' => count($world->factions),
            ],
            'factions' => $this->getFactionContext($world->factions),
            'seeds' => $this->getSeedContext($seeds),
            'character_state' => $this->getCharacterContext($character),
            'world_laws' => $this->getWorldLawContext($world),
        ];
    }

    /**
     * Lấy ngữ cảnh các phe phái
     */
    protected function getFactionContext(array $factions): array
    {
        $context = [];
        
        foreach ($factions as $faction) {
            $context[] = [
                'name' => $faction->name,
                'type' => $faction->type,
                'cohesion' => $faction->cohesion ?? 50,
                'power_level' => $faction->militaryPower ?? 5,
                'economic_status' => $faction->economy ?? 'stable',
            ];
        }
        
        return $context;
    }

    /**
     * Lấy ngữ cảnh các seed
     */
    protected function getSeedContext(array $seeds): array
    {
        $context = [];
        
        foreach ($seeds as $seed) {
            $context[] = [
                'type' => $seed->type,
                'dimension' => $seed->dimension,
                'severity' => $seed->severity,
                'description' => $this->getSeedDescription($seed),
            ];
        }
        
        return $context;
    }

    /**
     * Lấy ngữ cảnh nhân vật
     */
    protected function getCharacterContext(CharacterState $character): array
    {
        return [
            'name' => $character->name ?? 'Unknown',
            'role' => $character->role ?? 'Observer',
            'power_level' => $character->powerLevel ?? 1,
            'influence' => $character->influence ?? 0,
            'alignment' => $character->alignment ?? 'neutral',
        ];
    }

    /**
     * Lấy ngữ cảnh quy luật thế giới
     */
    protected function getWorldLawContext(WorldState $world): array
    {
        // Lấy profile quy luật nếu có
        $profileCache = new \App\Services\WorldLawProfileCache();
        $profile = $profileCache->preloadWorldLawProfile($world);
        
        if ($profile) {
            return [
                'power_ceiling' => $profile->powerCeiling ?? 10,
                'technology_limit' => $profile->technologyLimit ?? 8,
                'social_cohesion_min' => $profile->socialCohesionMin ?? 20,
                'economic_balance_factor' => $profile->economicBalanceFactor ?? 0.5,
            ];
        }
        
        return [
            'power_ceiling' => 10,
            'technology_limit' => 8,
            'social_cohesion_min' => 20,
            'economic_balance_factor' => 0.5,
        ];
    }

    /**
     * Xây dựng prompt cho AI
     */
    protected function buildStoryPrompt(array $context): string
    {
        $template = $this->prompts['story_generation'] ?? $this->getDefaultStoryPrompt();
        
        return $this->replaceTemplateVariables($template, $context);
    }

    /**
     * Gọi API AI
     */
    protected function callAI(string $prompt): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API key not configured');
        }

        $response = Http::timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Bạn là một nhà văn fantasy tài năng, chuyên tạo ra các câu chuyện về thế giới giả tưởng với các phe phái, chính trị, và ma thuật.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 2000,
            'temperature' => 0.8,
        ]);

        if (!$response->successful()) {
            throw new \Exception('AI API call failed: ' . $response->body());
        }

        return $response->json('choices.0.message.content');
    }

    /**
     * Phân tích phản hồi từ AI
     */
    protected function parseStoryResponse(string $response): array
    {
        // Tìm các sự kiện trong phản hồi
        $events = $this->extractEvents($response);
        $narrative = $this->extractNarrative($response);
        $consequences = $this->extractConsequences($response);
        
        return [
            'events' => $events,
            'narrative' => $narrative,
            'consequences' => $consequences,
            'raw_response' => $response,
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Trích xuất sự kiện từ phản hồi
     */
    protected function extractEvents(string $response): array
    {
        $events = [];
        
        // Sử dụng regex để tìm các sự kiện
        preg_match_all('/\*\*(Sự kiện \d+):\*\*\s*(.+?)(?=\*\*Sự kiện \d+:|\*\*Hậu quả:\*\*|$)/s', $response, $matches);
        
        if (!empty($matches[1]) && !empty($matches[2])) {
            foreach ($matches[1] as $index => $eventTitle) {
                $events[] = [
                    'title' => trim($eventTitle),
                    'description' => trim($matches[2][$index]),
                    'type' => $this->classifyEventType($matches[2][$index]),
                    'severity' => $this->estimateEventSeverity($matches[2][$index]),
                ];
            }
        }
        
        // Nếu không tìm thấy sự kiện theo format, tạo từ toàn bộ phản hồi
        if (empty($events)) {
            $events[] = [
                'title' => 'Sự kiện chính',
                'description' => $response,
                'type' => 'narrative',
                'severity' => 5,
            ];
        }
        
        return $events;
    }

    /**
     * Trích xuất phần tường thuật
     */
    protected function extractNarrative(string $response): string
    {
        // Tìm phần tường thuật trong phản hồi
        if (preg_match('/\*\*Tường thuật:\*\*\s*(.+?)(?=\*\*|$)/s', $response, $matches)) {
            return trim($matches[1]);
        }
        
        return $response;
    }

    /**
     * Trích xuất hậu quả
     */
    protected function extractConsequences(string $response): array
    {
        $consequences = [];
        
        if (preg_match('/\*\*Hậu quả:\*\*\s*(.+?)(?=$|\*\*)/s', $response, $matches)) {
            $consequenceText = trim($matches[1]);
            
            // Phân tách các hậu quả
            $lines = explode("\n", $consequenceText);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && (str_starts_with($line, '-') || str_starts_with($line, '*'))) {
                    $consequences[] = trim(ltrim($line, '-* '));
                }
            }
        }
        
        return $consequences;
    }

    /**
     * Phân loại loại sự kiện
     */
    protected function classifyEventType(string $description): string
    {
        $keywords = [
            'political' => ['chính trị', 'quyền lực', 'phe phái', 'chiến tranh', 'hòa bình'],
            'economic' => ['kinh tế', 'thương mại', 'tài nguyên', 'thuế', 'giá cả'],
            'social' => ['xã hội', 'con người', 'văn hóa', 'tôn giáo', 'gia đình'],
            'military' => ['quân sự', 'chiến đấu', 'trận chiến', 'lính', 'vũ khí'],
            'magical' => ['ma thuật', 'phép thuật', 'linh hồn', '咒语', 'bùa chú'],
        ];
        
        $description = strtolower($description);
        
        foreach ($keywords as $type => $words) {
            foreach ($words as $word) {
                if (strpos($description, $word) !== false) {
                    return $type;
                }
            }
        }
        
        return 'general';
    }

    /**
     * Ước tính mức độ nghiêm trọng của sự kiện
     */
    protected function estimateEventSeverity(string $description): int
    {
        $severityKeywords = [
            9 => ['thảm họa', 'hủy diệt', 'tuyệt chủng', 'sụp đổ'],
            8 => ['khủng hoảng', 'nổi loạn', 'chiến tranh', 'đại dịch'],
            7 => ['xung đột', 'khó khăn', 'thất bại', 'mất mát'],
            6 => ['thay đổi', 'biến động', 'chuyển đổi'],
            5 => ['phát triển', 'cải tiến', 'tiến bộ'],
            4 => ['hòa bình', 'ổn định', 'bình thường'],
            3 => ['nhỏ', 'ít', 'hạn chế'],
        ];
        
        $description = strtolower($description);
        
        foreach ($severityKeywords as $severity => $words) {
            foreach ($words as $word) {
                if (strpos($description, $word) !== false) {
                    return $severity;
                }
            }
        }
        
        return 5; // Mặc định
    }

    /**
     * Tạo cốt truyện dự phòng khi AI thất bại
     */
    protected function generateFallbackStory(WorldState $world, CharacterState $character, array $seeds): array
    {
        $events = [];
        
        foreach ($seeds as $seed) {
            $events[] = [
                'title' => "Sự kiện {$seed->type}",
                'description' => $this->getSeedDescription($seed),
                'type' => 'seed_based',
                'severity' => $seed->severity,
            ];
        }
        
        return [
            'events' => $events,
            'narrative' => 'Thế giới tiếp tục phát triển với các sự kiện đang diễn ra.',
            'consequences' => ['Các phe phái tiếp tục tương tác và phát triển'],
            'raw_response' => 'Fallback story generated',
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Lấy mô tả seed
     */
    protected function getSeedDescription(Seed $seed): string
    {
        $descriptions = [
            'POWER_GAP' => 'Khoảng trống quyền lực xuất hiện, các phe phái tranh giành ảnh hưởng',
            'CRISIS' => 'Khủng hoảng xã hội lan rộng, người dân hoang mang',
            'OPPORTUNITY' => 'Cơ hội kinh tế mới mở ra, thương mại phát triển',
            'MYSTERY' => 'Sự kiện bí ẩn xảy ra, không ai hiểu rõ nguyên nhân',
            'CONFLICT' => 'Xung đột nổ ra giữa các phe phái',
        ];
        
        return $descriptions[$seed->type] ?? "Sự kiện {$seed->type} với mức độ {$seed->severity}";
    }

    /**
     * Tạo cache key
     */
    protected function generateCacheKey(WorldState $world, CharacterState $character, array $seeds): string
    {
        $worldHash = md5(json_encode([
            $world->publicAwareness,
            $world->powerCenters,
            $world->tierIndex,
            count($world->factions),
        ]));
        
        $seedHash = md5(json_encode(array_map(fn($s) => $s->type . $s->severity, $seeds)));
        
        return "story_{$worldHash}_{$seedHash}";
    }

    /**
     * Tải các prompt templates
     */
    protected function loadPrompts(): array
    {
        return [
            'story_generation' => file_get_contents(resource_path('ai/prompts/story_generation.txt')),
        ];
    }

    /**
     * Lấy prompt mặc định
     */
    protected function getDefaultStoryPrompt(): string
    {
        return <<<PROMPT
Dựa trên thông tin thế giới sau đây, hãy tạo một câu chuyện hấp dẫn:

THÔNG TIN THẾ GIỚI:
- Nhận thức công chúng: {world_state.public_awareness}/10
- Số trung tâm quyền lực: {world_state.power_centers}
- Chỉ số tầng lớp: {world_state.tier_index}
- Số phe phái: {world_state.faction_count}

CÁC PHE PHÁI:
{factions}

SỰ KIỆN ĐANG DIỄN RA:
{seeds}

YÊU CẦU:
1. Tạo 2-3 sự kiện chính phù hợp với ngữ cảnh
2. Mỗi sự kiện có mô tả chi tiết
3. Phân loại mức độ ảnh hưởng (1-10)
4. Mô tả hậu quả của các sự kiện

Định dạng:
**Sự kiện 1:** [Tên sự kiện]
[Mô tả chi tiết]

**Sự kiện 2:** [Tên sự kiện]
[Mô tả chi tiết]

**Hậu quả:**
- [Hậu quả 1]
- [Hậu quả 2]

Hãy viết bằng tiếng Việt với phong cách fantasy.
PROMPT;
    }

    /**
     * Thay thế biến trong template
     */
    protected function replaceTemplateVariables(string $template, array $context): string
    {
        $replacements = [
            '{world_state.public_awareness}' => $context['world_state']['public_awareness'],
            '{world_state.power_centers}' => $context['world_state']['power_centers'],
            '{world_state.tier_index}' => $context['world_state']['tier_index'],
            '{world_state.faction_count}' => $context['world_state']['faction_count'],
            '{factions}' => $this->formatFactions($context['factions']),
            '{seeds}' => $this->formatSeeds($context['seeds']),
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Định dạng thông tin phe phái
     */
    protected function formatFactions(array $factions): string
    {
        $formatted = [];
        
        foreach ($factions as $faction) {
            $formatted[] = "- {$faction['name']} ({$faction['type']}): Sức mạnh {$faction['power_level']}/10, Gắn kết {$faction['cohesion']}%";
        }
        
        return implode("\n", $formatted);
    }

    /**
     * Định dạng thông tin seed
     */
    protected function formatSeeds(array $seeds): string
    {
        $formatted = [];
        
        foreach ($seeds as $seed) {
            $formatted[] = "- {$seed['type']} ({$seed['dimension']}): Mức độ {$seed['severity']}/10 - {$seed['description']}";
        }
        
        return implode("\n", $formatted);
    }

    /**
     * Xóa cache
     */
    public function clearCache(): void
    {
        $this->storyCache = [];
    }

    /**
     * Lấy thống kê
     */
    public function getStatistics(): array
    {
        return [
            'cache_size' => count($this->storyCache),
            'api_key_configured' => !empty($this->apiKey),
            'model' => $this->model,
        ];
    }
}
