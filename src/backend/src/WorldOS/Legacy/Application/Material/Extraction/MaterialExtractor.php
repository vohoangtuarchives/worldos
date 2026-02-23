<?php

namespace WorldOS\Legacy\Application\Material\Extraction;

use WorldOS\Legacy\Application\Material\Extraction\Subprompts\StructuralAnalysisPrompt;
use Illuminate\Support\Facades\Log;

class MaterialExtractor
{
    private MaterialValidator $validator;

    public function __construct(MaterialValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Parse raw text and extract valid material candidates.
     * 
     * @param string $rawText
     * @return array List of valid material arrays matching the schema
     */
    public function extract(string $rawText): array
    {
        $prompt = StructuralAnalysisPrompt::get();
        
        // Mocking the LLM call for now since we don't have a real LLM client integrated in this prototype request.
        // In a real implementation, this would call OpenAI/Anthropic API.
        
        // For demonstration/testing, we return an empty array or simulate parsing if we had a mock LLM.
        // Let's assume the LLM returns a JSON string, which we decode.
        
        // $response = LLM::query($prompt . "\n\nInput:\n" . $rawText);
        // $data = json_decode($response, true);
        
        // Verification logic would go here.
        
        return []; 
    }
}
