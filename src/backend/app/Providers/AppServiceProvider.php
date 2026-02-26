<?php

namespace App\Providers;

use App\Domain\Simulation\Events\TickCompleted;
use App\Domain\Simulation\Events\TickRejected;
use App\Domain\Simulation\Listeners\NarrativeEventListener;
use App\Domain\Simulation\Services\GrpcSimulationEngineClient;
use App\Domain\Simulation\Services\SimulationEngineClientInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind gRPC Simulation Engine client (Rust backend)
        $this->app->singleton(
            SimulationEngineClientInterface::class,
            function () {
                $host = config('services.simulation_engine.host', '127.0.0.1:50051');
                return new GrpcSimulationEngineClient($host);
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Đăng ký Event → Listener mapping cho Simulation Domain
        Event::listen(TickCompleted::class, NarrativeEventListener::class);
    }
}
