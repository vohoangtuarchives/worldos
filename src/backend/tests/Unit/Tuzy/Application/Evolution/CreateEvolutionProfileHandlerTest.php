<?php

namespace Tests\Unit\Tuzy\Application\Evolution;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Evolution\CreateEvolutionProfile\CreateEvolutionProfileCommand;
use Tuzy\Application\Evolution\CreateEvolutionProfile\CreateEvolutionProfileHandler;
use Tuzy\Application\Evolution\CreateEvolutionProfile\CreateEvolutionProfileResult;
use Tuzy\Domain\Evolution\Entity\EvolutionProfile;
use Tuzy\Domain\Evolution\Repository\EvolutionProfileRepositoryInterface;

final class CreateEvolutionProfileHandlerTest extends TestCase
{
    public function test_handle_creates_profile_saves_returns_result(): void
    {
        $saved = [];
        $repo = new class($saved) implements EvolutionProfileRepositoryInterface {
            public function __construct(private array &$saved) {}
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
