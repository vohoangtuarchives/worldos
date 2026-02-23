<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Cosmology;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Cosmology\CreateUniverseStyle\CreateUniverseStyleCommand;
use WorldOS\Legacy\Application\Cosmology\CreateUniverseStyle\CreateUniverseStyleHandler;
use WorldOS\Legacy\Application\Cosmology\CreateUniverseStyle\CreateUniverseStyleResult;
use WorldOS\Legacy\Domain\Cosmology\Entity\UniverseStyle;
use WorldOS\Legacy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

final class CreateUniverseStyleHandlerTest extends TestCase
{
    public function test_handle_creates_universe_style_saves_returns_result(): void
    {
        $saved = [];
        $repo = new class($saved) implements UniverseStyleRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?UniverseStyle { return null; }
            public function save(UniverseStyle $s): void { $this->saved[] = $s; }
        };

        $handler = new CreateUniverseStyleHandler($repo);
        $command = new CreateUniverseStyleCommand('My Style', 'world-123');
        $result = $handler->handle($command);

        $this->assertInstanceOf(CreateUniverseStyleResult::class, $result);
        $this->assertNotEmpty($result->id);
        $this->assertSame('My Style', $result->name);
        $this->assertSame('world-123', $result->worldId);
        $this->assertCount(1, $saved);
    }
}
