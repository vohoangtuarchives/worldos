<?php

namespace App\Services\AI;

use App\Models\AIFeatureAgentConfig;

class AIFeatureAgentResolver
{
    public function resolve(?string $featureKey): array
    {
        $featureKey = $featureKey ?: 'global.default';

        $dbConfig = AIFeatureAgentConfig::query()
            ->where('feature_key', $featureKey)
            ->where('enabled', true)
            ->first();

        if ($dbConfig) {
            return [
                'feature_key' => $featureKey,
                'agent_name' => $dbConfig->agent_name,
                'provider' => $dbConfig->provider,
                'model' => $dbConfig->model,
                'system_prompt' => $dbConfig->system_prompt,
                'options' => $dbConfig->options ?? [],
            ];
        }

        $registry = config('ai.agent_registry', []);
        $fallback = $registry[$featureKey] ?? $registry['global.default'] ?? [];

        return [
            'feature_key' => $featureKey,
            'agent_name' => $fallback['agent_name'] ?? 'Default Agent',
            'provider' => $fallback['provider'] ?? 'openai',
            'model' => $fallback['model'] ?? config('services.openai.model'),
            'system_prompt' => $fallback['system_prompt'] ?? null,
            'options' => $fallback['options'] ?? [],
        ];
    }
}
