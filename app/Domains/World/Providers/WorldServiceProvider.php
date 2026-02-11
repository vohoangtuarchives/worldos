<?php

namespace App\Domains\World\Providers;

use App\Domains\World\Policy\InfiniteResourcePolicy;
use App\Domains\World\Policy\LinearPowerPolicy;
use App\Domains\World\Policy\NoConflictPolicy;
use App\Domains\World\Policy\PassiveEscalationPolicy;
use App\Domains\World\Services\PolicyRegistry;
use App\Domains\World\Services\WorldTickService;
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
