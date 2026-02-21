<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Tuzy\Domain\Cosmology\Event\UniverseStyleCreated;
use Tuzy\Domain\Evolution\Event\EvolutionProfileCreated;
use Tuzy\Domain\Narrative\Event\NarrativeSeriesCreated;
use Tuzy\Domain\Runtime\Event\UniverseCreated;
use Tuzy\Domain\Saga\Event\SagaCreated;
use Tuzy\Domain\Heroes\Event\WorldHeroCreated;
use Tuzy\Domain\World\Event\WorldCreated;

/**
 * Subscriber for Tuzy domain *Created events. Logs for audit; extend with notifications or persistence if needed.
 */
final class TuzyCreatedEventSubscriber
{
    public function subscribe(object $events): void
    {
        $events->listen(WorldCreated::class, [$this, 'onWorldCreated']);
        $events->listen(UniverseCreated::class, [$this, 'onUniverseCreated']);
        $events->listen(SagaCreated::class, [$this, 'onSagaCreated']);
        $events->listen(UniverseStyleCreated::class, [$this, 'onUniverseStyleCreated']);
        $events->listen(EvolutionProfileCreated::class, [$this, 'onEvolutionProfileCreated']);
        $events->listen(NarrativeSeriesCreated::class, [$this, 'onNarrativeSeriesCreated']);
        $events->listen(WorldHeroCreated::class, [$this, 'onWorldHeroCreated']);
    }

    public function onWorldCreated(WorldCreated $event): void
    {
        Log::debug('Tuzy.WorldCreated', ['world_id' => $event->worldId, 'world_name' => $event->worldName]);
    }

    public function onUniverseCreated(UniverseCreated $event): void
    {
        Log::debug('Tuzy.UniverseCreated', ['universe_id' => $event->universeId, 'universe_name' => $event->universeName]);
    }

    public function onSagaCreated(SagaCreated $event): void
    {
        Log::debug('Tuzy.SagaCreated', ['saga_id' => $event->sagaId, 'saga_name' => $event->sagaName]);
    }

    public function onUniverseStyleCreated(UniverseStyleCreated $event): void
    {
        Log::debug('Tuzy.UniverseStyleCreated', [
            'universe_style_id' => $event->universeStyleId,
            'name' => $event->name,
            'world_id' => $event->worldId,
        ]);
    }

    public function onEvolutionProfileCreated(EvolutionProfileCreated $event): void
    {
        Log::debug('Tuzy.EvolutionProfileCreated', [
            'profile_id' => $event->profileId,
            'profile_name' => $event->profileName,
        ]);
    }

    public function onNarrativeSeriesCreated(NarrativeSeriesCreated $event): void
    {
        Log::debug('Tuzy.NarrativeSeriesCreated', [
            'series_id' => $event->seriesId,
            'title' => $event->title,
        ]);
    }

    public function onWorldHeroCreated(WorldHeroCreated $event): void
    {
        Log::debug('Tuzy.WorldHeroCreated', [
            'hero_id' => $event->heroId,
            'name' => $event->name,
            'world_id' => $event->worldId,
        ]);
    }
}
