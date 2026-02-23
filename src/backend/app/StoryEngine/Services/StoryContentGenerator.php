<?php

namespace App\StoryEngine\Services;

use App\Models\Story;
use App\Domains\Narrative\LLM\Contracts\LLMProvider;
use App\StoryEngine\Seed;
use App\StoryEngine\Services\NarrativeAssembler;

class StoryContentGenerator
{
    public function __construct(
        protected LLMProvider $llm,
        protected \WorldOS\World\Application\Services\WorldLawValidator $validator,
        protected \App\Domains\World\Contracts\ClaimExtractorInterface $extractor,
        protected \App\Domains\WorldManagement\Services\AIGovernanceService $governance,
        protected NarrativeAssembler $assembler
    ) {}

    public function generate(Story $story, Seed $seed): array
    {
        // 1. Build Context
        $worldState = $story->world_state;
        $charState = $story->character_state;
        
        $constraints = "";
        $world = null;
        if ($story->world_id) {
             $world = \App\Models\World::find($story->world_id);
             if ($world) {
                 $constraints = $this->validator->getSystemConstraints($world->law_profile);
             }
        }

        $systemPrompt = $this->buildSystemPrompt($constraints);
        $userPrompt = $this->buildUserPrompt($story, $seed, $worldState, $charState);

        // 2. Loop for Generation & Validation (ADR-0005)
        $attempts = 0;
        $maxAttempts = 3;
        $lastError = "";

        while ($attempts < $maxAttempts) {
            $attempts++;
            
            try {
                // Determine prompt for retry
                $currentSystemPrompt = $systemPrompt;
                if ($lastError) {
                    $currentSystemPrompt .= "\n\nPREVIOUS GENERATION REJECTED:\n" . $lastError . "\nPlease correct this violations.";
                }

                $response = $this->llm->generate($currentSystemPrompt, $userPrompt);
                $content = $response['content'] ?? '';
                
                // 3. Extract Claims
                $claims = $this->extractor->extract($content);
                
                // 4. Validate Claims
                $violations = [];
                // Check if world exists to validate against
                $isValidProjection = true; 
                if ($world) {
                    $isValidProjection = $this->validator->validateClaims($world->law_profile, $claims, $violations);
                }

                // 5. Log Governance (ADR-0007)
                if ($world) {
                    $genId = $this->governance->logGeneration(
                        $world->id,
                        $currentSystemPrompt,
                        $userPrompt,
                        $content,
                        $isValidProjection ? 'ACCEPTED' : 'REJECTED',
                        $isValidProjection ? null : $violations,
                        $attempts
                    );
                    
                    // Log Claims
                    $this->governance->logClaims($genId, $claims, $this->validator, $world->law_profile);
                }

                if (!$isValidProjection) {
                    $lastError = implode("\n- ", $violations);
                    // Retry
                    continue; 
                }

                // Assemble rich narrative layers
                $rich = $this->assembler->assemble($story, $response, $seed);

                // Success!
                return [
                    'title' => $response['title'] ?? 'Untitled Chapter',
                    'content' => $content,
                    'rich' => $rich,
                ];

            } catch (\Exception $e) {
                 // Log Failure?
                 if ($world) {
                     $this->governance->logGeneration(
                        $world->id,
                        $systemPrompt,
                        $userPrompt,
                        "ERROR: " . $e->getMessage(),
                        'FAILED',
                        ['Exception' => $e->getMessage()],
                        $attempts
                    );
                 }

                return [
                    'title' => 'Error Generating Chapter',
                    'content' => "AI Generation Error: " . $e->getMessage(),
                ];
            }
        }

        // Failed after retries
        return [
            'title' => 'Chapter Generation Failed',
            'content' => "The Narrator struggled to conform to World Laws. Violations:\n" . $lastError,
            'rich' => null,
        ];
    }

    protected function buildSystemPrompt(string $constraints = ""): string
    {
        return <<<EOT
You are the Narrator of a Cultivation (Xianxia) World.
Your goal is to write a single chapter based on a specific "Story Seed" (a conflict or event).

TONE:
- Epic, mythical, but grounded in human nature.
- Use Xianxia terminology (Qi, Sects, Dao, Tribulation).
- Focus on the protagonist's struggle against the world.
- DO NOT be moralizing. The world is harsh.

SYSTEM CONSTRAINTS (ADR-0004):
{$constraints}

FORMAT:
Return a JSON object:
{
    "title": "The poetic title of the chapter",
    "content": "The full text of the chapter (approx 500-800 words). Use markdown for formatting."
}
EOT;
    }

    protected function buildUserPrompt(Story $story, Seed $seed, array $worldState, array $charState): string
    {
        // Context variables
        $tierName = $this->getTierName($charState['powerTier'] ?? 0);
        $awareness = $worldState['publicAwareness'] ?? 0;
        
        return <<<EOT
STORY CONTEXT:
Title: {$story->title}
World Status: Public Awareness of User is {$awareness}/100.
Protagonist Status: Power Tier {$charState['powerTier']} ({$tierName}).

CURRENT EVENT (SEED):
Type: {$seed->type}
Dimension: {$seed->dimension}
Severity: {$seed->severity}

INSTRUCTION:
Write the next chapter resolving this seed. 
If the seed is 'POWER_GAP', focus on the gap between the protagonist and their enemies.
If 'SOCIAL_PRESSURE', focus on rumors, reputation, or family expectations.
If 'ESCALATION_DEBT', focus on consequences of past actions catching up.

Make sure to advance the plot towards the resolution of this specific seed.
EOT;
    }

    protected function getTierName(int $tier): string
    {
        $tiers = [
            0 => 'Mortal',
            1 => 'Qi Condensation',
            2 => 'Foundation Establishment',
            3 => 'Golden Core',
            4 => 'Nascent Soul',
            5 => 'Spirit Severing',
            6 => 'Void Refining',
            7 => 'Integration',
            8 => 'Mahayana',
            9 => 'Tribulation Transcendent',
        ];
        
        return $tiers[$tier] ?? "God Tier {$tier}";
    }
}
