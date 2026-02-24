<?php

namespace WorldOS\Legacy\Infrastructure\World\Providers;

use WorldOS\Blueprint\Domain\Legacy\Policy\InfiniteResourcePolicy;
use WorldOS\Blueprint\Domain\Legacy\Policy\LinearPowerPolicy;
use WorldOS\Blueprint\Domain\Legacy\Policy\NoConflictPolicy;
use WorldOS\Blueprint\Domain\Legacy\Policy\PassiveEscalationPolicy;
use WorldOS\Legacy\Application\World\Services\PolicyRegistry;
use WorldOS\Legacy\Application\World\Services\WorldTickService;
use Illuminate\Support\ServiceProvider;

class WorldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PolicyRegistry::class);
        $this->app->singleton(WorldTickService::class);
    }

    public function boot(): void
    {
        $registry = $this->app->make(PolicyRegistry::class);

        // Register default policies
        $registry->register('linear_power', LinearPowerPolicy::class);
        $registry->register('infinite_resource', InfiniteResourcePolicy::class);
        $registry->register('no_conflict', NoConflictPolicy::class);
        $registry->register('passive_escalation', PassiveEscalationPolicy::class);
    }
}
