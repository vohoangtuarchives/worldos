<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\WorldOS\Runtime\Entities\UniverseEntity;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Shared\ValueObjects\LawVector;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;
use App\WorldOS\World\Contracts\WorldRepositoryInterface;
use App\WorldOS\World\Entities\WorldEntity;
use Illuminate\Database\Seeder;

/**
 * World Seeder — seeds preset Worlds for development.
 *
 * From docs §13.3: php artisan db:seed --class=WorldSeeder
 */
class WorldSeeder extends Seeder
{
    public function run(WorldRepositoryInterface $worldRepository): void
    {
        $presets = config('worldos.presets', []);

        foreach ($presets as $presetKey => $presetData) {
            $this->command->info("Seeding World: {$presetData['name']} ({$presetKey})");

            $world = WorldEntity::createFromPreset(
                presetKey: $presetKey,
                presetData: $presetData,
            );

            $worldRepository->save($world);

            $this->command->line("  → World ID: {$world->getId()->value}");
        }

        $this->command->info('WorldSeeder complete: ' . count($presets) . ' worlds created.');
    }
}
