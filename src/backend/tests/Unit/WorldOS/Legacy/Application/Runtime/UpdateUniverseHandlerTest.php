<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Runtime;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Runtime\UpdateUniverse\UpdateUniverseCommand;
use WorldOS\Legacy\Application\Runtime\UpdateUniverse\UpdateUniverseHandler;
use WorldOS\Legacy\Domain\Runtime\Entity\Universe;
use WorldOS\Legacy\Domain\Runtime\Exception\UniverseNotFoundException;
use WorldOS\Legacy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class UpdateUniverseHandlerTest extends TestCase
{
    public function test_handle_updates_name_and_saves(): void
    {
        $saved = [];
        $repo = new class($saved) implements UniverseRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?Universe {
                return Universe::create('Old Universe', $id);
            }
            public function save(Universe $universe): void {
                $this->saved[] = $universe;
            }
        };

        $handler = new UpdateUniverseHandler($repo);
        $handler->handle(new UpdateUniverseCommand('uid-1', 'New Universe Name'));

        $this->assertCount(1, $saved);
        $this->assertSame('uid-1', $saved[0]->getId());
        $this->assertSame('New Universe Name', $saved[0]->getName());
    }

    public function test_handle_throws_when_universe_not_found(): void
    {
        $repo = new class() implements UniverseRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?Universe { return null; }
            public function save(Universe $universe): void {}
        };

        $handler = new UpdateUniverseHandler($repo);

        $this->expectException(UniverseNotFoundException::class);
        $this->expectExceptionMessage('Universe not found: uid-missing');
        $handler->handle(new UpdateUniverseCommand('uid-missing', 'Any Name'));
    }
}
