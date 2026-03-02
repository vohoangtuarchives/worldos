<?php

namespace App\Services\Simulation;

use App\Models\Actor;
use App\Models\Universe;
use App\Models\AgentDecision;
use App\Models\SocialContract;
use App\Models\Chronicle;

class AutonomyEngine
{
    protected array $actionWeights = [
        'form_contract' => [
            'Solidarity' => 0.8,
            'Loyalty' => 0.6,
            'Empathy' => 0.5,
            'Fear' => 0.4, // Fear can drive cooperation
            'Ambition' => -0.3, // High ambition might prefer solo power
        ],
        'revolt' => [
            'Ambition' => 0.7,
            'Coercion' => 0.6,
            'Vengeance' => 0.8,
            'Fear' => -0.5, // Fearful actors are less likely to revolt
            'Dominance' => 0.6,
        ],
        'migrate' => [
            'Curiosity' => 0.7,
            'RiskTolerance' => 0.8,
            'Hope' => 0.5,
            'Fear' => 0.6, // Fleeing from danger
        ],
        'trade' => [
            'Pragmatism' => 0.9,
            'Solidarity' => 0.4,
            'Ambition' => 0.5,
        ]
    ];

    /**
     * Process decisions for all active actors in a universe.
     */
    public function process(Universe $universe, int $tick): void
    {
        $actors = Actor::where('universe_id', $universe->id)->where('is_alive', true)->get();

        foreach ($actors as $actor) {
            $decision = $this->makeDecision($actor, $universe, $tick);
            if ($decision) {
                $this->applyDecision($actor, $decision, $universe, $tick);
            }
        }
    }

    protected function makeDecision(Actor $actor, Universe $universe, int $tick): ?array
    {
        $traits = $actor->traits; // 17D array
        $dimensions = HeroicActorService::TRAIT_DIMENSIONS;
        
        $utilities = [];
        foreach ($this->actionWeights as $action => $weights) {
            $score = 0.5; // Base score
            foreach ($weights as $traitName => $weight) {
                $index = array_search($traitName, $dimensions);
                if ($index !== false && isset($traits[$index])) {
                    $score += ($traits[$index] * $weight);
                }
            }
            // Add some noise as per V6 spec
            $score += (rand(-10, 10) / 100);
            $utilities[$action] = $score;
        }

        arsort($utilities);
        $bestAction = key($utilities);
        $bestScore = current($utilities);

        // Only act if utility is high enough
        if ($bestScore > 1.2) {
            return [
                'type' => $bestAction,
                'score' => $bestScore,
                'traits' => $traits
            ];
        }

        return null;
    }

    protected function applyDecision(Actor $actor, array $decision, Universe $universe, int $tick): void
    {
        // Record the decision
        AgentDecision::create([
            'actor_id' => $actor->id,
            'universe_id' => $universe->id,
            'tick' => $tick,
            'action_type' => $decision['type'],
            'utility_score' => $decision['score'],
            'traits_snapshot' => $decision['traits'],
        ]);

        // Specific logic for each action
        switch ($decision['type']) {
            case 'form_contract':
                $this->handleSocialContract($actor, $universe, $tick);
                break;
            case 'revolt':
                $this->handleRevolt($actor, $universe, $tick);
                break;
            case 'migrate':
                $actor->biography .= "\n- T{$tick}: Quyết định dời bước khỏi chốn cũ, tìm kiếm chân trời mới.";
                $actor->save();
                break;
        }
    }

    protected function handleSocialContract(Actor $actor, Universe $universe, int $tick): void
    {
        // Find nearby actors to form a contract
        $others = Actor::where('universe_id', $universe->id)
            ->where('id', '!=', $actor->id)
            ->where('is_alive', true)
            ->limit(3)
            ->get();

        if ($others->isEmpty()) return;

        $participants = $others->pluck('id')->push($actor->id)->toArray();

        SocialContract::create([
            'universe_id' => $universe->id,
            'type' => 'mutual_defense',
            'participants' => $participants,
            'strictness' => (rand(30, 80) / 100),
            'duration' => 100,
            'created_at_tick' => $tick,
            'expires_at_tick' => $tick + 100,
        ]);

        $names = $others->pluck('name')->implode(', ');
        $actor->biography .= "\n- T{$tick}: Ký kết giao ước liên thủ với {$names}.";
        $actor->save();

        Chronicle::create([
            'universe_id' => $universe->id,
            'from_tick' => $tick,
            'to_tick' => $tick,
            'type' => 'social_contract',
            'content' => "GIAO ƯỚC MỚI: {$actor->name} và các đồng minh thiết lập một khế ước tương trợ, đặt nền móng cho trật tự mới.",
        ]);
    }

    protected function handleRevolt(Actor $actor, Universe $universe, int $tick): void
    {
        $actor->biography .= "\n- T{$tick}: Bùng nổ nộ khí, công khai phản kháng lại trật tự hiện hành.";
        $actor->save();

        Chronicle::create([
            'universe_id' => $universe->id,
            'from_tick' => $tick,
            'to_tick' => $tick,
            'type' => 'revolt',
            'content' => "BIẾN LOẠN: {$actor->name} công khai phản kháng, tạo ra một cơn sóng bất ổn lan rộng.",
        ]);
        
        // Impact on universe entropy
        $latest = $universe->snapshots()->orderByDesc('tick')->first();
        if ($latest) {
             $metrics = $latest->metrics;
             $metrics['local_instability'] = ($metrics['local_instability'] ?? 0) + 0.05;
             // We can't update snapshot directly, but we can signal pressure
        }
    }
}
