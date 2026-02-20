<?php

namespace Tests\Unit\Tuzy\Application\Cosmology;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Cosmology\CreateUniverseStyle\CreateUniverseStyleCommand;
use Tuzy\Application\Cosmology\CreateUniverseStyle\CreateUniverseStyleHandler;
use Tuzy\Application\Cosmology\CreateUniverseStyle\CreateUniverseStyleResult;
use Tuzy\Domain\Cosmology\Entity\UniverseStyle;
use Tuzy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

final class CreateUniverseStyleHandlerTest extends TestCase
{
    public function test_handle_creates_universe_style_saves_returns_result(): void
    {
        $saved = [];
        $repo = new class($saved) implements UniverseStyleRepositoryInterface {
            public function __construct(private array &$saved) {}
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
