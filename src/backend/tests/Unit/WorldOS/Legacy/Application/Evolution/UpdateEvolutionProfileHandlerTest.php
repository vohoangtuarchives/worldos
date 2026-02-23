<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Evolution;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Evolution\UpdateEvolutionProfile\UpdateEvolutionProfileCommand;
use WorldOS\Legacy\Application\Evolution\UpdateEvolutionProfile\UpdateEvolutionProfileHandler;
use WorldOS\Evolution\Domain\Legacy\Entity\EvolutionProfile;
use WorldOS\Evolution\Domain\Legacy\Exception\EvolutionProfileNotFoundException;
use WorldOS\Evolution\Domain\Legacy\Repository\EvolutionProfileRepositoryInterface;

final class UpdateEvolutionProfileHandlerTest extends TestCase
{
    public function test_handle_updates_name_and_saves(): void
    {
        $saved = [];
        $repo = new class($saved) implements EvolutionProfileRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?EvolutionProfile { return EvolutionProfile::create('Old', $id); }
            public function save(EvolutionProfile $p): void { $this->saved[] = $p; }
        };
        $handler = new UpdateEvolutionProfileHandler($repo);
        $handler->handle(new UpdateEvolutionProfileCommand('ep-1', 'New Profile'));
        $this->assertCount(1, $saved);
        $this->assertSame('ep-1', $saved[0]->getId());
        $this->assertSame('New Profile', $saved[0]->getName());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements EvolutionProfileRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?EvolutionProfile { return null; }
            public function save(EvolutionProfile $p): void {}
        };
        $handler = new UpdateEvolutionProfileHandler($repo);
        $this->expectException(EvolutionProfileNotFoundException::class);
        $handler->handle(new UpdateEvolutionProfileCommand('y', 'Any'));
    }
}
