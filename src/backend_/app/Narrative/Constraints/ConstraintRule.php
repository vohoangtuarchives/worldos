<?php

namespace App\Narrative\Constraints;

use App\Narrative\Values\NarrativeContext;

class ConstraintRule
{
    public function __construct(
        public readonly string $id,
        public readonly array $when,   // Condition map
        public readonly array $forbid, // Forbidden patterns/words
        public readonly string $reason
    ) {}

    public function matches(NarrativeContext $ctx): bool
    {
        // Simple matching logic for now
        foreach ($this->when as $key => $value) {
            $currentValue = $this->getContextValue($ctx, $key);
            if ($currentValue !== $value) {
                return false;
            }
        }
        return true;
    }

    public function violates(string $text): bool
    {
        // Check words
        if (isset($this->forbid['words'])) {
            foreach ($this->forbid['words'] as $word) {
                if (mb_stripos($text, $word) !== false) {
                    return true;
                }
            }
        }

        // Check patterns (regex)
        if (isset($this->forbid['patterns'])) {
            foreach ($this->forbid['patterns'] as $pattern) {
                if (preg_match($pattern, $text)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getContextValue(NarrativeContext $ctx, string $key): mixed
    {
        return match($key) {
            'genre' => $ctx->genre?->key(),
            'tone' => $ctx->tone,
            'stage' => $ctx->powerStage?->value,
            'scope' => $ctx->powerScope?->value ?? $ctx->socialContext?->scope->value,
            'phase' => $ctx->phase,
            'speaker_status' => $ctx->socialContext?->speakerStatus->value,
            'situation' => $ctx->socialContext?->situation->value,
            default => null
        };
    }
}
