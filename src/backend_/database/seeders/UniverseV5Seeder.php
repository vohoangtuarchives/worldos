<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\World;
use App\Models\Universe;
use WorldOS\Chronicle\Domain\Entity\ChronicleEvent;
use WorldOS\Chronicle\Domain\Repository\ChronicleRepositoryInterface;
use WorldOS\Chronicle\Domain\ValueObject\EventType;
use WorldOS\Chronicle\Domain\ValueObject\Severity;

class UniverseV5Seeder extends Seeder
{
    public function __construct(
        private readonly ChronicleRepositoryInterface $chronicleRepository
    ) {
    }

    public function run(): void
    {
        // 1. Create a Sealed World Blueprint
        $world = World::firstOrCreate(
            ['name' => 'V5 Baseline Blueprint'],
            [
                'description' => 'A master blueprint for V5 simulation testing',
                'status' => 'sealed', // Required for ignition
                'gene_vector' => ['entropy' => 0.2, 'stability' => 0.8],
                'preset' => 'myth',
            ]
        );

        $this->command->info("World Blueprint resolved: {$world->id}");

        // 2. Ignite a Universe
        $universe = Universe::firstOrCreate(
            ['name' => 'The First Eon'],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'world_blueprint_id' => (string) $world->id,
                'multiverse_id' => 'mv-baseline-001',
                'current_tick' => 0,
                'status' => 'running',
                'entropy' => 0.1,
                'stability_index' => 0.9,
                'state_vector' => [
                    'entropy' => 0.1,
                    'stability' => 0.9,
                    'richness' => 0.5,
                    'w' => 1.0
                ],
            ]
        );

        $this->command->info("Universe ignited: {$universe->id}");

        // 3. Record some "Genesis" events for the Historian to pick up
        $this->command->info("Recording Genesis events...");

        $events = [
            ChronicleEvent::record(
                universeId: (string) $universe->id,
                tick:       0,
                seed:       1234,
                type:       EventType::ERA_SHIFT,
                title:      'Bình minh của Kỷ nguyên Đầu tiên',
                severity:   Severity::MEDIUM,
                payload:    ['desc' => 'Hạt nhân thế giới bắt đầu rung động.']
            ),
            ChronicleEvent::record(
                universeId: (string) $universe->id,
                tick:       50,
                seed:       5678,
                type:       EventType::ANOMALY_SPIKE,
                title:      'Cơn địa chấn Entropy',
                severity:   Severity::HIGH,
                payload:    ['intensity' => 0.75]
            ),
            ChronicleEvent::record(
                universeId: (string) $universe->id,
                tick:       150,
                seed:       9999,
                type:       EventType::TRANSCENDENCE,
                title:      'Sự thăng hoa của Ý thức Hạt nhân',
                severity:   Severity::CRITICAL,
                payload:    ['desc' => 'Vũ trụ đạt đến mức độ ổn định tuyệt đối.']
            )
        ];

        foreach ($events as $event) {
            $this->chronicleRepository->save($event);
        }

        $this->command->info("Phase 13 Seeding Complete. Universe is ready for simulation.");
    }
}
