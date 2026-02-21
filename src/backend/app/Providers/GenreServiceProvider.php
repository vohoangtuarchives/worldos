<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Tuzy\Domain\Genre\GenreRegistry;
use Tuzy\Application\Genre\Genres\WuxiaGenre;
use Tuzy\Application\Genre\Genres\XianxiaGenre;
use Tuzy\Application\Genre\Genres\SystemGenre;
use Tuzy\Application\Genre\Genres\MagicalAcademyGenre;

class GenreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(GenreRegistry::class, function ($app) {
            $registry = new \Tuzy\Domain\Genre\GenreRegistry();
            
            // Register detailed GenreDefinitions
            $registry->register(new \Tuzy\Application\Genre\Genres\Xianxia\XianxiaDefinition());
            $registry->register(new \Tuzy\Application\Genre\Genres\Survival\SurvivalGenre());
            
            // Re-enable others when ported to new Definition system
            // $registry->register(new WuxiaDefinition());
            // $registry->register(new SystemDefinition());
            
            return $registry;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
