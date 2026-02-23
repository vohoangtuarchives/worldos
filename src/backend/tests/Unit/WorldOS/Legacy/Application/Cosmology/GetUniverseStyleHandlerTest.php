<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Cosmology;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Cosmology\GetUniverseStyle\GetUniverseStyleHandler;
use WorldOS\Legacy\Application\Cosmology\GetUniverseStyle\GetUniverseStyleQuery;
use WorldOS\Legacy\Domain\Cosmology\Entity\UniverseStyle;
use WorldOS\Legacy\Domain\Cosmology\Exception\UniverseStyleNotFoundException;
use WorldOS\Legacy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

final class GetUniverseStyleHandlerTest extends TestCase
{
    public function test_handle_returns_style_when_found(): void
    {
        $style = UniverseStyle::create('Style A', 'world-1', 'style-id');
        $repo = new class($style) implements UniverseStyleRepositoryInterface {
            public function __construct(private ?UniverseStyle $style) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?UniverseStyle { return $this->style; }
            public function save(UniverseStyle $u): void {}
        };
        $handler = new GetUniverseStyleHandler($repo);
        $result = $handler->handle(new GetUniverseStyleQuery('style-id'));
        $this->assertSame($style, $result);
        $this->assertSame('style-id', $result->getId());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements UniverseStyleRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?UniverseStyle { return null; }
            public function save(UniverseStyle $u): void {}
        };
        $handler = new GetUniverseStyleHandler($repo);
        $this->expectException(UniverseStyleNotFoundException::class);
        $this->expectExceptionMessage('UniverseStyle not found: x');
        $handler->handle(new GetUniverseStyleQuery('x'));
    }
}
