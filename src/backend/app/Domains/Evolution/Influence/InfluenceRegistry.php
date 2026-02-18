<?php

declare(strict_types=1);

namespace App\Domains\Evolution\Influence;

use App\Models\World;

/**
 * InfluenceRegistry - Resolves config keys to EvolutionInfluence instances.
 * World only stores config['evolution_influences']; Registry does instantiation.
 */
final class InfluenceRegistry
{
    /** @var array<string, class-string<EvolutionInfluence>> */
    private array $map = [];

    public function __construct()
    {
        $this->registerDefault();
    }

    public function register(string $key, string $class): void
    {
        $this->map[$key] = $class;
    }

    /**
     * @return list<EvolutionInfluence>
     */
    public function resolveForWorld(World $world): array
    {
        $config = $world->config ?? [];
        $keys = $config['evolution_influences'] ?? [];
        if (!is_array($keys)) {
            $keys = [];
        }

        $instances = [];
        foreach ($keys as $key) {
            $class = $this->map[$key] ?? null;
            if ($class !== null && is_a($class, EvolutionInfluence::class, true)) {
                $instances[] = app($class);
            }
        }

        usort($instances, fn (EvolutionInfluence $a, EvolutionInfluence $b): int => $b->priority() <=> $a->priority());

        return $instances;
    }

    private function registerDefault(): void
    {
        $this->map['vietnamese_hero'] = VietnameseHeroInfluence::class;
        $this->map['realm_contact'] = RealmContactInfluence::class;
    }
}
