<?php

declare(strict_types=1);

namespace App\WorldOS\Influence\Providers;

use App\WorldOS\Influence\Contracts\EvolutionInfluenceInterface;
use App\WorldOS\Influence\Contracts\NarrativePressureBridgeInterface;
use App\WorldOS\Influence\Influences\AttractorInfluence;
use App\WorldOS\Influence\Influences\MythInfluence;
use App\WorldOS\Influence\Influences\ScarInfluence;
use App\WorldOS\Influence\Services\InfluencePipeline;
use App\WorldOS\Influence\Services\NullNarrativePressureBridge;
use Illuminate\Support\ServiceProvider;

/**
 * Influence Module Service Provider.
 *
 * Registers the InfluencePipeline with default influences
 * and binds the NarrativePressureBridge to null stub.
 */
class InfluenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind narrative bridge to null stub (until Narrative module is built)
        $this->app->bind(
            NarrativePressureBridgeInterface::class,
            NullNarrativePressureBridge::class
        );

        // Register InfluencePipeline as singleton with default influences
        $this->app->singleton(InfluencePipeline::class, function () {
            $pipeline = new InfluencePipeline();

            // Register default influences in order:
            // Structural (Scar) → Cultural (Myth) → Gravitational (Attractor)
            $pipeline->register(new ScarInfluence());
            $pipeline->register(new MythInfluence());
            $pipeline->register(new AttractorInfluence());

            return $pipeline;
        });
    }

    public function boot(): void
    {
        //
    }
}
