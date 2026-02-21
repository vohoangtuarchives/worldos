<?php

namespace Tests\Unit\Tuzy\Application\Evolution;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Evolution\ListEvolutionProfiles\ListEvolutionProfilesHandler;
use Tuzy\Application\Evolution\ListEvolutionProfiles\ListEvolutionProfilesQuery;
use Tuzy\Application\Evolution\ListEvolutionProfiles\ListEvolutionProfilesResult;
use Tuzy\Domain\Evolution\Entity\EvolutionProfile;
use Tuzy\Domain\Evolution\Repository\EvolutionProfileRepositoryInterface;

final class ListEvolutionProfilesHandlerTest extends TestCase
{
    public function test_handle_returns_all_profiles_as_result(): void
    {
        $p1 = EvolutionProfile::create('Profile A', 'ep-1');
        $p2 = EvolutionProfile::create('Profile B', 'ep-2');
        $repo = new class($p1, $p2) implements EvolutionProfileRepositoryInterface {
            public function __construct(private EvolutionProfile $p1, private EvolutionProfile $p2) {}
            public function findAll(): array { return [$this->p1, $this->p2]; }
            public function findById(string $id): ?EvolutionProfile { return null; }
            public function save(EvolutionProfile $p): void {}
        };
        $handler = new ListEvolutionProfilesHandler($repo);
        $result = $handler->handle(new ListEvolutionProfilesQuery());
        $this->assertInstanceOf(ListEvolutionProfilesResult::class, $result);
        $this->assertCount(2, $result->evolutionProfiles);
        $this->assertSame('ep-1', $result->evolutionProfiles[0]['id']);
        $this->assertSame('Profile A', $result->evolutionProfiles[0]['name']);
        $this->assertSame('ep-2', $result->evolutionProfiles[1]['id']);
        $this->assertSame('Profile B', $result->evolutionProfiles[1]['name']);
    }

    public function test_handle_returns_empty_when_no_profiles(): void
    {
        $repo = new class() implements EvolutionProfileRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?EvolutionProfile { return null; }
            public function save(EvolutionProfile $p): void {}
        };
        $handler = new ListEvolutionProfilesHandler($repo);
        $result = $handler->handle(new ListEvolutionProfilesQuery());
        $this->assertSame([], $result->evolutionProfiles);
    }
}
