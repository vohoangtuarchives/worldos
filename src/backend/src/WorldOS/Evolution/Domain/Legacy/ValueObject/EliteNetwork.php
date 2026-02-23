<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\ValueObject;

/**
 * EliteNetwork - Represents the matrix of influence, alliances, and rivalries among Factions.
 * Also tracks NetworkRigidity, which contributes to StructuralEntropy.
 */
final class EliteNetwork
{
    public function __construct(
        public readonly float $networkRigidity = 0.0, // 0.0 to 1.0
        // Maps Faction ID -> Faction ID -> Influence/Alliance value (-1.0 to 1.0)
        public readonly array $allianceMatrix = []
    ) {
    }

    public function getAlliance(string $factionA, string $factionB): float
    {
        return $this->allianceMatrix[$factionA][$factionB] ?? 0.0;
    }

    public function withRigidity(float $newRigidity): self
    {
        return new self(
            networkRigidity: max(0.0, min(1.0, $newRigidity)),
            allianceMatrix: $this->allianceMatrix
        );
    }

    public function withAlliance(string $factionA, string $factionB, float $value): self
    {
        $matrix = $this->allianceMatrix;
        $matrix[$factionA][$factionB] = max(-1.0, min(1.0, $value));
        $matrix[$factionB][$factionA] = max(-1.0, min(1.0, $value)); // Symmetric for now
        
        return new self(
            networkRigidity: $this->networkRigidity,
            allianceMatrix: $matrix
        );
    }

    public function toArray(): array
    {
        return [
            'network_rigidity' => $this->networkRigidity,
            'alliance_matrix' => $this->allianceMatrix,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            networkRigidity: (float)($data['network_rigidity'] ?? 0.0),
            allianceMatrix: (array)($data['alliance_matrix'] ?? [])
        );
    }
}
