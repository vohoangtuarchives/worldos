<?php

return [
    App\Providers\AppServiceProvider::class,

    // WorldOS Modules
    App\WorldOS\World\Providers\WorldServiceProvider::class,
    App\WorldOS\Runtime\Providers\RuntimeServiceProvider::class,
    App\WorldOS\SimulationEngine\Providers\SimulationEngineServiceProvider::class,
    App\WorldOS\Cosmology\Providers\CosmologyServiceProvider::class,
    App\WorldOS\Saga\Providers\SagaServiceProvider::class,
    App\WorldOS\Resonance\Providers\ResonanceServiceProvider::class,
    App\WorldOS\Attractor\Providers\AttractorServiceProvider::class,
    App\WorldOS\CivilizationMemory\Providers\CivilizationMemoryServiceProvider::class,
    App\WorldOS\Influence\Providers\InfluenceServiceProvider::class,
    App\WorldOS\Style\Providers\StyleServiceProvider::class,
    App\WorldOS\Governance\Providers\GovernanceServiceProvider::class,
    App\WorldOS\Narrative\Providers\NarrativeServiceProvider::class,
];
