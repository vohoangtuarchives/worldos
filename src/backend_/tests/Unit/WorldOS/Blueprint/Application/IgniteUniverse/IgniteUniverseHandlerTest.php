<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Blueprint\Application\IgniteUniverse;

use DomainException;
use PHPUnit\Framework\TestCase;
use WorldOS\Blueprint\Application\IgniteUniverse\IgniteUniverseCommand;
use WorldOS\Blueprint\Application\IgniteUniverse\IgniteUniverseHandler;
use WorldOS\Blueprint\Domain\World\Entity\World;
use WorldOS\Blueprint\Domain\World\Repository\WorldRepositoryInterface;
use WorldOS\Blueprint\Domain\World\ValueObject\GeneVector;
use WorldOS\Blueprint\Domain\World\ValueObject\NarrativeTopology;
use WorldOS\Blueprint\Domain\World\ValueObject\PhysicsCore;
use WorldOS\Blueprint\Domain\World\ValueObject\WorldId;
use WorldOS\Blueprint\Domain\World\ValueObject\WorldStatus;
use WorldOS\Simulation\Domain\Universe\Entity\Universe;
use WorldOS\Simulation\Domain\Universe\Repository\UniverseRepositoryInterface;

final class IgniteUniverseHandlerTest extends TestCase
{
    private WorldRepositoryInterface $worldRepository;
    private UniverseRepositoryInterface $universeRepository;
    private IgniteUniverseHandler $handler;

    protected function setUp(): void
    {
        $this->worldRepository    = $this->createMock(WorldRepositoryInterface::class);
        $this->universeRepository = $this->createMock(UniverseRepositoryInterface::class);
        $this->handler            = new IgniteUniverseHandler($this->worldRepository, $this->universeRepository);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function makeWorld(bool $sealed = true): World
    {
        $physicsCore = PhysicsCore::create(
            dimensions: 17,
            geneBounds: GeneVector::create([], []),
        );
        $narrativeTopology = NarrativeTopology::create();

        $world = World::create(
            name:            'Test World',
            physicsCore:     $physicsCore,
            narrativeTopology: $narrativeTopology,
            multiverseId:    'multiverse-test',
        );

        if ($sealed) {
            $world->seal();
        }

        return $world;
    }

    // ------------------------------------------------------------------
    // Happy path
    // ------------------------------------------------------------------

    public function test_ignite_returns_universe_with_frozen_world_signature(): void
    {
        $world     = $this->makeWorld(sealed: true);
        $worldId   = $world->getId();
        $expectedHash = $world->getSignature()->getHash();

        $this->worldRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($worldId))
            ->willReturn($world);

        $savedUniverse = null;
        $this->universeRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Universe $u) use (&$savedUniverse) {
                $savedUniverse = $u;
            });

        $command = new IgniteUniverseCommand(
            worldId: $worldId->toString(),
            name:    'Alpha Universe',
        );

        $universe = $this->handler->handle($command);

        $this->assertSame($expectedHash, $universe->getWorldSignatureHash());
        $this->assertSame($expectedHash, $savedUniverse->getWorldSignatureHash());
        $this->assertSame($worldId->toString(), $universe->getWorldBlueprintId());
    }

    // ------------------------------------------------------------------
    // Guard: World not found
    // ------------------------------------------------------------------

    public function test_throws_when_world_not_found(): void
    {
        $this->worldRepository
            ->method('findById')
            ->willReturn(null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/not found/');

        $this->handler->handle(new IgniteUniverseCommand(
            worldId: WorldId::generate()->toString(),
            name:    'Ghost Universe',
        ));
    }

    // ------------------------------------------------------------------
    // Guard: World is not SEALED
    // ------------------------------------------------------------------

    public function test_throws_when_world_is_not_sealed(): void
    {
        $world = $this->makeWorld(sealed: false); // DRAFT status

        $this->worldRepository
            ->method('findById')
            ->willReturn($world);

        $this->universeRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/SEALED/');

        $this->handler->handle(new IgniteUniverseCommand(
            worldId: $world->getId()->toString(),
            name:    'Draft Universe',
        ));
    }
}
