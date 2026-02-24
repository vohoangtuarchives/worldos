<?php

namespace WorldOS\Legacy\Application\Material\Extraction;

/**
 * ConceptNormalizer - Extract concepts from raw data using LLM
 * 
 * AI Role: Extract and suggest concepts, NOT define laws.
 */
class ConceptNormalizer
{
    private const PROMPT_TEMPLATE = <<<'PROMPT'
Analyze this historical data and extract material concepts that represent historical forces and pressures.

**Data Source:** {source_type}
**Title:** {title}
**Content:**
{content}

**Instructions:**
Extract concepts representing:
1. Economic pressures (subsistence, inequality, resource conflicts)
2. Social structures (labor organization, centralization, infrastructure)
3. Symbolic forces (myths, beliefs, identities, legitimacy)
4. Memory patterns (trauma, nostalgia, grievances, historical distortion)
5. External interactions (migration, trade, threats, cultural friction)

**Rules:**
- Focus on FORCES and PRESSURES, not specific events or people
- Use UPPERCASE_SNAKE_CASE for codes
- Strength: 0.0 (weak) to 1.0 (strong)
- Pressure outputs: -1.0 to 1.0 (negative = decrease, positive = increase)

**Output Format (JSON):**
```json
{
  "concepts": [
    {
      "code": "MATERIAL_CODE",
      "name": "Human Readable Name",
      "type": "economic|social|symbolic|memory|interaction",
      "ontology": "institutional|behavioral|symbolic",
      "function": "stabilizing|destabilizing|transformative",
      "evidence": "Quote or summary from source",
      "strength": 0.7,
      "suggested_outputs": {
        "subsistence_base": -0.4,
        "trauma_density": 0.6
      },
      "decay_rate": 1.0,
      "legacy_outputs": {
        "type": "historical_trace",
        "strength": 0.5
      }
    }
  ]
}
```

Extract 3-5 most significant concepts.
PROMPT;

    public function __construct(
        private ?object $llmService = null
    ) {
        // LLM service will be injected (OpenAI, Anthropic, etc.)
        // For now, use placeholder
    }

    /**
     * Normalize raw data into structured concepts.
     * 
     * @param array $rawData From DataExtractor
     * @return array Extracted concepts
     */
    public function normalize(array $rawData): array
    {
        $prompt = $this->buildPrompt($rawData);
        
        // If no LLM service, return mock data for testing
        if (!$this->llmService) {
            return $this->mockNormalize($rawData);
        }

        $response = $this->llmService->complete($prompt);
        return $this->parseResponse($response);
    }

    /**
     * Build LLM prompt from raw data.
     */
    private function buildPrompt(array $rawData): string
    {
        return str_replace(
            ['{source_type}', '{title}', '{content}'],
            [
                $rawData['source_type'] ?? 'unknown',
                $rawData['title'] ?? 'Untitled',
                $this->truncateContent($rawData['content'] ?? '', 4000),
            ],
            self::PROMPT_TEMPLATE
        );
    }

    /**
     * Parse LLM response to structured concepts.
     */
    private function parseResponse(string $response): array
    {
        // Extract JSON from response (may be wrapped in markdown)
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $response, $matches)) {
            $json = $matches[1];
        } elseif (preg_match('/(\{.*\})/s', $response, $matches)) {
            $json = $matches[1];
        } else {
            throw new \RuntimeException('Failed to parse LLM response as JSON');
        }

        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON in LLM response: ' . json_last_error_msg());
        }

        return $data['concepts'] ?? [];
    }

    /**
     * Truncate content to max length.
     */
    private function truncateContent(string $content, int $maxLength): string
    {
        if (strlen($content) <= $maxLength) {
            return $content;
        }

        return substr($content, 0, $maxLength) . '... [truncated]';
    }

    /**
     * Mock normalization for testing (no LLM).
     */
    private function mockNormalize(array $rawData): array
    {
        // Return placeholder concepts based on source type
        $title = $rawData['title'] ?? 'Unknown';
        
        return [
            [
                'code' => 'EXTRACTED_PRESSURE',
                'name' => "Pressure from {$title}",
                'type' => 'economic',
                'ontology' => 'behavioral',
                'function' => 'destabilizing',
                'evidence' => 'Mock extraction from: ' . substr($rawData['content'] ?? '', 0, 100),
                'strength' => 0.6,
                'suggested_outputs' => [
                    'subsistence_base' => -0.3,
                    'resource_pressure' => 0.4,
                ],
                'decay_rate' => 1.0,
                'legacy_outputs' => [
                    'type' => 'historical_trace',
                    'strength' => 0.5,
                ],
            ],
        ];
    }
}
