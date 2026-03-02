<?php

namespace App\Services\Narrative;

use App\Models\Chronicle;
use App\Models\Universe;
use Illuminate\Support\Facades\Http;

/**
 * Narrative AI: build prompt from Perceived Archive + events + flavor + residual, call LLM, store Chronicle.
 * Does NOT read canonical archive or modify simulation state.
 */
class NarrativeAiService
{
    public function __construct(
        protected PerceivedArchiveBuilder $perceived,
        protected FlavorTextMapper $flavor,
        protected EventTriggerMapper $eventMapper,
        protected ResidualInjector $residual,
        protected \App\Services\AI\VectorSearchService $vectorSearch
    ) {}

    /**
     * Generate chronicle for universe in tick range. Uses env NARRATIVE_LLM_URL or stub.
     */
    public function generateChronicle(int $universeId, int $fromTick, ?int $toTick = null, string $type = 'chronicle'): ?Chronicle
    {
        $universe = Universe::find($universeId);
        if (! $universe) {
            return null;
        }

        $latest = $universe->snapshots()->orderByDesc('tick')->first();
        $vector = $latest?->state_vector ?? [];
        if (is_array($vector) && isset($vector[0]['state'])) {
            $flat = [];
            foreach ($vector as $z) {
                $flat = array_merge($flat, $z['state'] ?? []);
            }
            $vector = $flat;
        }
        $eventTypes = ['crisis', 'unrest'];
        $perceived = $this->perceived->build($universeId, $eventTypes, $vector, $toTick);

        $prompt = $this->buildPrompt($perceived, $fromTick, $toTick);
        $content = $this->callLlm($prompt);

        if ($content === null) {
            $content = "[Stub: Chronicle would be generated from LLM for ticks {$fromTick}-" . ($toTick ?? 'latest') . "]";
        }

        $vector = $this->vectorSearch->vectorize($content);
        $vectorString = '[' . implode(',', $vector) . ']';

        return Chronicle::create([
            'universe_id' => $universeId,
            'from_tick' => $fromTick,
            'to_tick' => $toTick,
            'type' => $type,
            'content' => $content,
            'perceived_archive_snapshot' => $perceived,
            'embedding' => \Illuminate\Support\Facades\DB::raw("'$vectorString'::vector"),
        ]);
    }

    protected function buildPrompt(array $perceived, int $fromTick, ?int $toTick): string
    {
        $flavor = implode(' ', $perceived['flavor'] ?? []);
        $events = implode(', ', $perceived['events'] ?? []);
        $materials = implode(', ', $perceived['materials'] ?? []);
        $institutions = implode(', ', $perceived['institutions'] ?? []);
        $culture = json_encode($perceived['culture'] ?? []);
        $branches = implode('; ', $perceived['branch_events'] ?? []);
        $entropy = $perceived['metrics']['entropy'] ?? 'unknown';
        $tail = $perceived['residual_prompt_tail'] ?? '';

        return <<<EOT
You are the Chronicle of a simulated universe. 
Time Period: Ticks $fromTick to $toTick.
World State:
- Entropy: $entropy
- Dominant Materials: $materials
- Collective Culture: $culture
- Active Institutions: $institutions
- Recent Events: $events
- Branching History: $branches
- Atmosphere: $flavor

Task: Write a concise, mythic-style chronicle entry (2-3 sentences). Focus on how the material base and cultural shifts influenced the rise or fall of institutions and collective stability.
$tail
EOT;
    }

    protected function callLlm(string $prompt): ?string
    {
        $apiKey = config('services.openai.key');
        
        if ($apiKey) {
            try {
                $response = Http::withToken($apiKey)
                    ->timeout(30)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => config('services.openai.model', 'gpt-4o'),
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are WorldOS, a cosmic simulation narrator.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => 0.7,
                    ]);

                if ($response->successful()) {
                    return $response->json('choices.0.message.content');
                }
            } catch (\Throwable $e) {
                // Fallback to mock if API fails
            }
        }

        return $this->generateMockNarrative($prompt);
    }

    protected function generateMockNarrative(string $prompt): string
    {
        // Extract key info for mock generation
        preg_match('/Entropy: ([\d.]+)/', $prompt, $mEntropy);
        preg_match('/Dominant Materials: (.*)/', $prompt, $mMat);
        
        $entropy = floatval($mEntropy[1] ?? 0);
        $materials = $mMat[1] ?? 'void';
        
        if ($entropy > 0.8) {
            return "Chaos reigns. The structure of $materials collapses under the weight of high entropy ($entropy). Society fragments into isolated pockets of survival.";
        } elseif ($entropy > 0.5) {
            return "Tension rises. The age of $materials faces stagnation as entropy climbs to $entropy. Whispers of change echo through the system.";
        } else {
            return "A golden age. $materials flourishes in a stable order (Entropy: $entropy). The civilization expands its complexity with confidence.";
        }
    }
}
