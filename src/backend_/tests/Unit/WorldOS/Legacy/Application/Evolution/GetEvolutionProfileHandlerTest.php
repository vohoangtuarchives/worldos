<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Evolution;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Evolution\GetEvolutionProfile\GetEvolutionProfileHandler;
use WorldOS\Legacy\Application\Evolution\GetEvolutionProfile\GetEvolutionProfileQuery;
use WorldOS\Evolution\Domain\Legacy\Entity\EvolutionProfile;
use WorldOS\Evolution\Domain\Legacy\Exception\EvolutionProfileNotFoundException;
use WorldOS\Evolution\Domain\Legacy\Repository\EvolutionProfileRepositoryInterface;

final class GetEvolutionProfileHandlerTest extends TestCase
{
    public function test_handle_returns_profile_when_found(): void
    {
        $profile = EvolutionProfile::create('Profile X', 'ep-1');
        $repo = new class($profile) implements EvolutionProfileRepositoryInterface {
            public function __construct(private ?EvolutionProfile $profile) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?EvolutionProfile { return $this->profile; }
            public function save(EvolutionProfile $p): void {}
        };
        $handler = new GetEvolutionProfileHandler($repo);
        $result = $handler->handle(new GetEvolutionProfileQuery('ep-1'));
        $this->assertSame($profile, $result);
        $this->assertSame('ep-1', $result->getId());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements EvolutionProfileRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?EvolutionProfile { return null; }
            public function save(EvolutionProfile $p): void {}
        };
        $handler = new GetEvolutionProfileHandler($repo);
        $this->expectException(EvolutionProfileNotFoundException::class);
        $this->expectExceptionMessage('EvolutionProfile not found: y');
        $handler->handle(new GetEvolutionProfileQuery('y'));
    }
}
