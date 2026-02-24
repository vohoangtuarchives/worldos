<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

/**
 * EliteDecision - Represents the human layer that decides whether to follow the AI Advisor.
 * Introduces political friction and biases based on power dynamics.
 */
final class EliteDecision
{
    /**
     * Recommends the final chosen policy, blending AI suggestions with Elite bias.
     *
     * @param string[] $suggestedPolicies
     * @param float $eliteCohesion
     * @param float $legitimacy
     * @param float $structuralEntropy
     * @return string The chosen policy
     */
    public function makeDecision(
        array $suggestedPolicies,
        float $eliteCohesion,
        float $legitimacy,
        float $structuralEntropy
    ): string {
        if (empty($suggestedPolicies)) {
            return PolicyAdvisor::POLICY_MAINTAIN_STATUS_QUO;
        }

        $topSuggestion = $suggestedPolicies[0];

        // DecisionQuality = EliteCohesion * 0.4 + Legitimacy * 0.3 - StructuralEntropy * 0.3
        $decisionQuality = ($eliteCohesion * 0.4) + ($legitimacy * 0.3) - ($structuralEntropy * 0.3);

        // If decision quality is high, elites rationally follow the best advice
        if ($decisionQuality > 0.5) {
            return $topSuggestion;
        }

        // Under high structural entropy / low cohesion, elites ignore advice and preserve power
        if ($structuralEntropy > 0.6 || $eliteCohesion < 0.4) {
            // High friction block
            // They avoid risky reforms even if CollapseProbability is high
            if ($topSuggestion === PolicyAdvisor::POLICY_TRIGGER_REFORM) {
                // Delay action or push burden
                return mt_rand(0, 1) === 0 ? PolicyAdvisor::POLICY_MAINTAIN_STATUS_QUO : PolicyAdvisor::POLICY_INCREASE_DATA_QUALITY;
            }

            // They might choose to reduce centralization but struggle to enact it
            if ($topSuggestion === PolicyAdvisor::POLICY_REDUCE_CENTRALIZATION) {
                return PolicyAdvisor::POLICY_MAINTAIN_STATUS_QUO; // Institutional paralysis
            }
        }

        // Some minor chance of erratic choice when quality is low
        if (mt_rand(0, 100) < 30) {
            $randomChoice = array_rand($suggestedPolicies);
            return $suggestedPolicies[$randomChoice];
        }

        return $topSuggestion;
    }
}
