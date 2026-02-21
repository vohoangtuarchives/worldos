<?php

namespace Tests\Unit\Tuzy\Application\Cosmology;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Cosmology\ListUniverseStyles\ListUniverseStylesHandler;
use Tuzy\Application\Cosmology\ListUniverseStyles\ListUniverseStylesQuery;
use Tuzy\Application\Cosmology\ListUniverseStyles\ListUniverseStylesResult;
use Tuzy\Domain\Cosmology\Entity\UniverseStyle;
use Tuzy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

final class ListUniverseStylesHandlerTest extends TestCase
{
    public function test_handle_returns_all_styles_as_result(): void
    {
        $s1 = UniverseStyle::create('Style A', 'world-1', 'style-1');
        $s2 = UniverseStyle::create('Style B', 'world-2', 'style-2');
        $repo = new class($s1, $s2) implements UniverseStyleRepositoryInterface {
            public function __construct(private UniverseStyle $s1, private UniverseStyle $s2) {}
            public function findAll(): array { return [$this->s1, $this->s2]; }
            public function findById(string $id): ?UniverseStyle { return null; }
            public function save(UniverseStyle $u): void {}
        };
        $handler = new ListUniverseStylesHandler($repo);
        $result = $handler->handle(new ListUniverseStylesQuery());
        $this->assertInstanceOf(ListUniverseStylesResult::class, $result);
        $this->assertCount(2, $result->universeStyles);
        $this->assertSame('style-1', $result->universeStyles[0]['id']);
        $this->assertSame('Style A', $result->universeStyles[0]['name']);
        $this->assertSame('world-1', $result->universeStyles[0]['world_id']);
        $this->assertSame('style-2', $result->universeStyles[1]['id']);
        $this->assertSame('Style B', $result->universeStyles[1]['name']);
        $this->assertSame('world-2', $result->universeStyles[1]['world_id']);
    }

    public function test_handle_returns_empty_when_no_styles(): void
    {
        $repo = new class() implements UniverseStyleRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?UniverseStyle { return null; }
            public function save(UniverseStyle $u): void {}
        };
        $handler = new ListUniverseStylesHandler($repo);
        $result = $handler->handle(new ListUniverseStylesQuery());
        $this->assertSame([], $result->universeStyles);
    }
}
