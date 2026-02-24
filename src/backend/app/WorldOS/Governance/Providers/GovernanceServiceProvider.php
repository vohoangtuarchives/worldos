<?php

declare(strict_types=1);

namespace App\WorldOS\Governance\Providers;

use App\WorldOS\Governance\Contracts\UniverseEvaluatorInterface;
use App\WorldOS\Governance\Listeners\EvaluateOnTickListener;
use App\WorldOS\Governance\Services\RuleBasedEvaluator;
use App\WorldOS\Runtime\Events\UniverseTicked;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class GovernanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UniverseEvaluatorInterface::class,
            RuleBasedEvaluator::class
        );
    }

    public function boot(): void
    {
        Event::listen(
            UniverseTicked::class,
            EvaluateOnTickListener::class
        );
    }
}
