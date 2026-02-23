<?php

namespace App\Domains\World\Contracts;

/**
 * World (aggregate root) owns evolution logic.
 * Runtime (Universe instance) delegates tick to this engine.
 * Implementations may delegate to Cosmology kernel or World-specific Evolution kernel.
 * Phase 4.2: Optional $shockParams (e.g. WorldOS\Saga\Domain\Legacy\ValueObject\ShockParams) for Saga mode.
 */
interface EvolutionEngineInterface
{
    /**
     * Apply one tick of evolution to the given runtime instance.
     * The instance state is updated according to World's laws.
     *
     * @param object $runtimeInstance Universe (Cosmology) instance
     * @param object|null $shockParams Optional shock (e.g. ShockParams) to apply after physics
     */
    public function applyTick(object $runtimeInstance, ?object $shockParams = null): void;
}
