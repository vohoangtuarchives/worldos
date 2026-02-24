<?php

namespace Tests\Feature\Tuzy;

use Tests\TestCase;
use WorldOS\Blueprint\Domain\Legacy\Entity\World;
use WorldOS\Blueprint\Domain\Legacy\Repository\WorldRepositoryInterface;

/**
 * Integration test: Tuzy domain exceptions are rendered as 404 JSON by bootstrap/app.php.
 * Uses a stub repository so no database is required.
 */
final class DomainExceptionTo404Test extends TestCase
{
    public function test_get_world_with_non_existent_id_returns_404_json(): void
    {
        $this->app->bind(WorldRepositoryInterface::class, function (): WorldRepositoryInterface {
            return new class implements WorldRepositoryInterface {
                public function findById(string $id): ?World
                {
                    return null;
                }

                /** @return list<World> */
                public function findAll(): array
                {
                    return [];
                }

                public function save(World $world): void
                {
                }
            };
        });

        $response = $this->getJson('/api/v4/tuzy/worlds/non-existent-id');

        $response->assertStatus(404);
        $response->assertJsonFragment(['error' => 'world_not_found']);
        $response->assertJsonStructure(['message', 'error']);
    }
}
