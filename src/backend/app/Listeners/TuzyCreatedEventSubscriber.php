<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use WorldOS\Legacy\Domain\Cosmology\Event\UniverseStyleCreated;
use WorldOS\Evolution\Domain\Legacy\Event\EvolutionProfileCreated;
use WorldOS\Saga\Domain\Narrative\Event\NarrativeSeriesCreated;
use WorldOS\Legacy\Domain\Runtime\Event\UniverseCreated;
use WorldOS\Saga\Domain\Legacy\Event\SagaCreated;
use WorldOS\Saga\Domain\Hero\Event\HeroCreated;
use WorldOS\Blueprint\Domain\Legacy\Event\WorldCreated;

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
        $events->listen(HeroCreated::class, [$this, 'onHeroCreated']);
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

    public function onHeroCreated(HeroCreated $event): void
    {
        Log::debug('Tuzy.HeroCreated', [
            'hero_id' => $event->heroId,
            'name' => $event->name,
            'world_id' => $event->worldId,
        ]);
    }
}
