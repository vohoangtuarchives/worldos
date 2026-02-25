<?php

return [
    App\Providers\AppServiceProvider::class,

    // Modules
    App\Modules\Universe\Providers\WorldServiceProvider::class,
    App\Modules\Simulation\Providers\SimulationEngineServiceProvider::class,
    App\Modules\WorldTemplate\Providers\CosmologyServiceProvider::class,
    App\Modules\Narrative\Providers\NarrativeServiceProvider::class,
    // Note: Other modules might not have providers yet, or they were deleted.
    // If they exist they can be added later, these four are the main ones currently active.
];
