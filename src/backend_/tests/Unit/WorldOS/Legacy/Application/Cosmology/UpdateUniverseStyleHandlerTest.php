<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Cosmology;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Cosmology\UpdateUniverseStyle\UpdateUniverseStyleCommand;
use WorldOS\Legacy\Application\Cosmology\UpdateUniverseStyle\UpdateUniverseStyleHandler;
use WorldOS\Legacy\Domain\Cosmology\Entity\UniverseStyle;
use WorldOS\Legacy\Domain\Cosmology\Exception\UniverseStyleNotFoundException;
use WorldOS\Legacy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

final class UpdateUniverseStyleHandlerTest extends TestCase
{
    public function test_handle_updates_name_and_saves(): void
    {
        $saved = [];
        $repo = new class($saved) implements UniverseStyleRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?UniverseStyle {
                return UniverseStyle::create('Old', 'w1', $id);
            }
            public function save(UniverseStyle $u): void { $this->saved[] = $u; }
        };
        $handler = new UpdateUniverseStyleHandler($repo);
        $handler->handle(new UpdateUniverseStyleCommand('style-1', 'New Style'));
        $this->assertCount(1, $saved);
        $this->assertSame('style-1', $saved[0]->getId());
        $this->assertSame('New Style', $saved[0]->getName());
        $this->assertSame('w1', $saved[0]->getWorldId());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements UniverseStyleRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?UniverseStyle { return null; }
            public function save(UniverseStyle $u): void {}
        };
        $handler = new UpdateUniverseStyleHandler($repo);
        $this->expectException(UniverseStyleNotFoundException::class);
        $handler->handle(new UpdateUniverseStyleCommand('x', 'Any'));
    }
}
