<?php

declare(strict_types=1);

namespace WorldOS\Core\ValueObject;

use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;
use WorldOS\Society\Culture\ValueObject\CulturalVector;
use WorldOS\Society\Faction\ValueObject\IdeologyVector;


/**
 * CivilizationSnapshot: The full state of a civilization at a single point in time.
 */
readonly class CivilizationSnapshot
{
    public function __construct(
        public string $id,
        public StateVector $physics,
        public IdeologyVector $ideology,
        public CulturalVector $culture,
        public LifecycleState $lifecycle,
        public int $stabilityDuration,
        public float $influenceMass,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new CivilizationSnapshot(
            $data['id'],
            StateVector::fromSnapshot($data['physics']), // Giả định StateVector có mapping này
            IdeologyVector::fromArray($data['ideology']),
            CulturalVector::fromArray($data['culture']),
            LifecycleState::from($data['lifecycle']),
            (int) ($data['stability_duration'] ?? 0),
            (float) ($data['influence_mass'] ?? 1.0),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'physics' => $this->physics->toArray(),
            'ideology' => $this->ideology->toArray(),
            'culture' => $this->culture->toArray(),
            'lifecycle' => $this->lifecycle->value,
            'stability_duration' => $this->stabilityDuration,
            'influence_mass' => $this->influenceMass,
        ];
    }
}
