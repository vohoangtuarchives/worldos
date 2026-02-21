<?php

namespace Tuzy\Infrastructure\World\Providers;

use Tuzy\Domain\World\Policy\InfiniteResourcePolicy;
use Tuzy\Domain\World\Policy\LinearPowerPolicy;
use Tuzy\Domain\World\Policy\NoConflictPolicy;
use Tuzy\Domain\World\Policy\PassiveEscalationPolicy;
use Tuzy\Application\World\Services\PolicyRegistry;
use Tuzy\Application\World\Services\WorldTickService;
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
