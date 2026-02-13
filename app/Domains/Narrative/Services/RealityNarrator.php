<?php

namespace App\Domains\Narrative\Services;

use App\Models\World;
use App\Models\WorldEvent;
use App\Domains\Narrative\LLM\Contracts\LLMProvider;
use App\Domains\World\ValueObjects\PhysicsProfile;
use Illuminate\Support\Facades\Log;

class RealityNarrator
{
    public function __construct(
        protected LLMProvider $llm
    ) {}

    /**
     * Generate a vivid description of physical changes in a world.
     */
    public function narrateInvasion(World $victim, World $aggressor, float $exposure): string
    {
        $systemPrompt = "You are the Cosmic Chronicler. Your task is to describe the horrifying but awe-inspiring process of Reality Rewriting (Terraforming). 
        A source world's physics is invading a target world. 
        Describe how the environment, the sky, the laws of gravity, and the very feeling of existence change as 'External Reality' bleeds in.
        Use evocative, slightly Lovecraftian or epic prose. Respond with a JSON object containing a 'description' field.";

        $userPrompt = "Target World: {$victim->name} ({$victim->genre})
        Aggressor World: {$aggressor->name} ({$aggressor->genre})
        Intensity: " . ($exposure * 100) . "%
        Physics Change: Target is becoming more like Aggressor. 
        Describe the immediate visual and physical effects at the point of impact.";

        try {
            $result = $this->llm->generate($systemPrompt, $userPrompt);
            return $result['description'] ?? "Thực tại bắt đầu rạn nứt khi luồng năng lượng ngoại lai áp đảo quy tắc bản địa.";
        } catch (\Exception $e) {
            Log::error("RealityNarrator Error: " . $e->getMessage());
            return "Vết nứt không gian mở rộng, mang theo những quy tắc vật lý chưa từng thấy.";
        }
    }

    /**
     * Narrate an entropy spike.
     */
    public function narrateEntropySpike(World $world, float $magnitude): string
    {
        $systemPrompt = "Describe an Entropy Spike in a simulation. Entropy is waste heat, chaos, and the decay of information.
        Describe how the world feels 'hotter', 'messier', or 'glitchy' as entropy approaches its cap.
        Respond with a JSON object containing a 'description' field.";

        $userPrompt = "World: {$world->name}
        Magnitude: {$magnitude}
        Current Entropy: {$world->entropy}
        Describe the sensory experience of this chaos.";

        try {
            $result = $this->llm->generate($systemPrompt, $userPrompt);
            return $result['description'] ?? "Sức nóng của sự hỗn loạn bắt đầu làm mờ nhạt ranh giới của vật chất.";
        } catch (\Exception $e) {
            return "Áp lực entropy tăng cao, tạo ra những biến động không xác định trong thực tại.";
        }
    }
}
