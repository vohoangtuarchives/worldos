<?php

namespace Tests\Integration\Tuzy\Infrastructure\Persistence\World;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tuzy\Domain\World\Entity\World;
use Tuzy\Domain\World\Repository\WorldRepositoryInterface;

final class EloquentWorldRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_and_find_by_id_round_trips_entity(): void
    {
        $repo = $this->app->make(WorldRepositoryInterface::class);
        $world = World::create('Integration World');

        $repo->save($world);

        $found = $repo->findById($world->getId());
        $this->assertInstanceOf(World::class, $found);
        $this->assertSame($world->getId(), $found->getId());
        $this->assertSame('Integration World', $found->getName());
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $repo = $this->app->make(WorldRepositoryInterface::class);
        $this->assertNull($repo->findById('non-existent-uuid'));
    }
}
