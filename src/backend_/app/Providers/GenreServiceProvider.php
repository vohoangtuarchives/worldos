<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use WorldOS\Legacy\Domain\Genre\GenreRegistry;
use WorldOS\Legacy\Application\Genre\Genres\WuxiaGenre;
use WorldOS\Legacy\Application\Genre\Genres\XianxiaGenre;
use WorldOS\Legacy\Application\Genre\Genres\SystemGenre;
use WorldOS\Legacy\Application\Genre\Genres\MagicalAcademyGenre;

class GenreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(GenreRegistry::class, function ($app) {
            $registry = new \WorldOS\Legacy\Domain\Genre\GenreRegistry();
            
            // Register detailed GenreDefinitions
            $registry->register(new \WorldOS\Legacy\Application\Genre\Genres\Xianxia\XianxiaDefinition());
            $registry->register(new \WorldOS\Legacy\Application\Genre\Genres\Survival\SurvivalGenre());
            
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
