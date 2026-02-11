<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Genre\GenreRegistry;
use App\Domains\Genre\Genres\WuxiaGenre;
use App\Domains\Genre\Genres\XianxiaGenre;
use App\Domains\Genre\Genres\SystemGenre;
use App\Domains\Genre\Genres\MagicalAcademyGenre;

class GenreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(GenreRegistry::class, function ($app) {
            $registry = new \App\Domains\Genre\GenreRegistry();
            
            // Register detailed GenreDefinitions
            $registry->register(new \App\Domains\Genre\Genres\Xianxia\XianxiaDefinition());
            $registry->register(new \App\Domains\Genre\Genres\Survival\SurvivalGenre());
            
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
