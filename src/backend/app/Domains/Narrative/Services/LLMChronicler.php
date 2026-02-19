<?php

namespace App\Domains\Narrative\Services;

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Narrative\Contracts\ChroniclerInterface;
use App\Domains\Narrative\LLM\Contracts\LLMProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log;
use App\Services\AI\AIAgentContext;

/**
 * Biên niên sử từ trạng thái mô phỏng: ưu tiên LLM nếu có, không thì dùng template giàu từ vựng.
 * Phù hợp mô hình simulate: state + milestones → narrative.
 */
class LLMChronicler implements ChroniclerInterface
{
    private NarrativeBridge $bridge;
    private ?LLMProvider $llm;
    private ?\App\Domains\Genre\GenreRegistry $registry;

    public function __construct(NarrativeBridge $bridge, ?LLMProvider $llm = null, ?\App\Domains\Genre\GenreRegistry $registry = null)
    {
        $this->bridge = $bridge;
        $this->llm = $llm ?? (function () {
            if (app()->bound(LLMProvider::class)) {
                return app(LLMProvider::class);
            }
            return null;
        })();
        $this->registry = $registry ?? (app()->bound(\App\Domains\Genre\GenreRegistry::class) ? app(\App\Domains\Genre\GenreRegistry::class) : null);
    }

    public function chronicle(Universe $universe): string
    {
        $state = $universe->getState();
        $age = $universe->getAge();
        $rich = $this->bridge->getRichContextForChronicle($state);
        $params = $universe->getParameters();
        $milestones = $params['milestones'] ?? [];

        if ($this->llm !== null) {
            try {
                // Try to find specific genre prompt from parameters or bridge context
                $genreKey = $params['genre'] ?? $rich['genre'] ?? null;
                $genrePrompt = null;
                
                if ($genreKey && $this->registry) {
                    $def = $this->registry->get($genreKey);
                    if ($def) {
                        $genrePrompt = $def->getNarrativePrompt();
                    }
                }

                $text = $this->generateViaLLM($state, $age, $rich, $milestones, $genrePrompt);
                if ($text !== null && $text !== '') {
                    return $text;
                }
            } catch (\Throwable $e) {
                Log::warning('LLMChronicler: LLM failed, using template fallback.', ['error' => $e->getMessage()]);
            }
        }

        return $this->bridge->buildChronicleParagraph($state, $age);
    }

    private function generateViaLLM(WorldStateVector $state, int $age, array $rich, array $milestones, ?string $genrePrompt = null): ?string
    {
        $baseSystemPrompt = "Bạn là người viết biên niên sử cho một thế giới mô phỏng. Nhiệm vụ: trả về đúng một đối tượng JSON với đúng một khóa 'chronicle'. Giá trị của 'chronicle' là một đoạn văn (1–3 câu) bằng tiếng Việt, văn phong biên niên, giàu hình ảnh, mô tả tình hình thế giới tại chu kỳ đã cho. Chỉ trả về JSON, không giải thích thêm.";
        
        $systemPrompt = $baseSystemPrompt;
        if ($genrePrompt) {
            $systemPrompt .= "\n\nGENRE INSTRUCTION: " . $genrePrompt;
        }

        $situationPhrases = array_map(fn ($s) => $s['phrase'], $rich['situations']);
        $milestoneLines = [];
        foreach (array_slice($milestones, -5) as $m) {
            $milestoneLines[] = "Chu kỳ {$m['age']}: {$m['event']} — {$m['description']}";
        }
        $userPrompt = "Chu kỳ hiện tại: {$age}. Thể loại: {$rich['genre']}. Đặc điểm: " . implode(', ', $rich['traits']) . ". "
            . "Tình huống chính: " . implode(' ', $situationPhrases) . ". "
            . "Số liệu (entropy, order, innovation): " . round($state->getEntropy(), 2) . ", " . round($state->getOrder(), 2) . ", " . round($state->getInnovation(), 2) . ". ";
        if (!empty($milestoneLines)) {
            $userPrompt .= "Các mốc gần đây: " . implode('; ', $milestoneLines) . ". ";
        }
        $userPrompt .= "Hãy viết một đoạn biên niên ngắn (khóa 'chronicle') phản ánh trạng thái này.";

        $out = app(AIAgentContext::class)->runWith('narrative.chronicler', fn () => $this->llm->generate($systemPrompt, $userPrompt));
        if (is_array($out) && isset($out['chronicle']) && is_string($out['chronicle'])) {
            return trim($out['chronicle']);
        }
        return null;
    }
}
