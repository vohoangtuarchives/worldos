<?php

return [
    App\Providers\AppServiceProvider::class,
    WorldOS\Legacy\Infrastructure\Material\Providers\MaterialServiceProvider::class,
    WorldOS\Legacy\Infrastructure\World\Providers\WorldServiceProvider::class,
    WorldOS\Legacy\Infrastructure\Cosmic\Providers\CosmicServiceProvider::class,
    WorldOS\Legacy\Infrastructure\Vietnamese\Providers\VietnameseServiceProvider::class,
    WorldOS\Shared\Infrastructure\Providers\WorldOSV5ServiceProvider::class,
];
