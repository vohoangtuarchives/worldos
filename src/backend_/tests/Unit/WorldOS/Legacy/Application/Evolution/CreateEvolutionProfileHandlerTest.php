<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Evolution;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Evolution\CreateEvolutionProfile\CreateEvolutionProfileCommand;
use WorldOS\Legacy\Application\Evolution\CreateEvolutionProfile\CreateEvolutionProfileHandler;
use WorldOS\Legacy\Application\Evolution\CreateEvolutionProfile\CreateEvolutionProfileResult;
use WorldOS\Evolution\Domain\Legacy\Entity\EvolutionProfile;
use WorldOS\Evolution\Domain\Legacy\Repository\EvolutionProfileRepositoryInterface;

final class CreateEvolutionProfileHandlerTest extends TestCase
{
    public function test_handle_creates_profile_saves_returns_result(): void
    {
        $saved = [];
        $repo = new class($saved) implements EvolutionProfileRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?EvolutionProfile { return null; }
            public function save(EvolutionProfile $p): void { $this->saved[] = $p; }
        };
        $handler = new CreateEvolutionProfileHandler($repo);
        $result = $handler->handle(new CreateEvolutionProfileCommand('My Profile'));
        $this->assertInstanceOf(CreateEvolutionProfileResult::class, $result);
        $this->assertNotEmpty($result->id);
        $this->assertSame('My Profile', $result->name);
        $this->assertCount(1, $saved);
    }
}
