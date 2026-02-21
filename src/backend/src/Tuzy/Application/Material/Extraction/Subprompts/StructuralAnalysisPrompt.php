<?php

namespace Tuzy\Application\Material\Extraction\Subprompts;

class StructuralAnalysisPrompt
{
    public static function get(): string
    {
        return <<<EOT
You are a structural historian AI. Your task is to extract abstract, reusable material units from the provided text.
Ignore specific names, places, dates, and narrative details. Focus ONLY on the underlying cause-and-effect structures.

For the input text, identify candidates that fit the following schema:

{
    "candidate_materials": [
        {
            "code": "UNIQUE_UPPERCASE_CODE",
            "ontology": "symbolic | institutional | behavioral",
            "function": "legitimizing | stabilizing | transformative | destructive",
            "default_lifecycle": "dormant | active",
            "description": "Abstract description of the pattern without proper nouns.",
            "preconditions": ["List of abstract requirements"],
            "pressure_inputs": ["What triggers this pattern?"],
            "pressure_outputs": ["What pressure does this exert on the world?"],
            "incompatible_with": ["Codes of conflicting materials"],
            "mutation_axes": ["How can this pattern distort over time?"]
        }
    ]
}

RULES:
1. Code must be concise (e.g., DIVINE_KINGSHIP).
2. Description must be universal.
3. Preconditions must be structural (e.g., "centralized authority", not "King Arthur").
4. Output valid JSON only.
EOT;
    }
}
