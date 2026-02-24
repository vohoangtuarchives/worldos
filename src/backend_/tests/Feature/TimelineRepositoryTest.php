<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\World;
use WorldOS\Legacy\Application\Narrative\Timeline\TimelineNode;
use WorldOS\Saga\Domain\Narrative\ValueObject\StateSnapshot;
use WorldOS\Legacy\Application\Narrative\Timeline\Repositories\TimelineEloquentRepository;
use WorldOS\Legacy\Application\Narrative\Timeline\Services\CausalConsistency;
use WorldOS\Legacy\Application\Narrative\Character\Entities\Memory;
use Illuminate\Support\Str;

class TimelineRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_persist_and_retrieve_dag()
    {
        $world = World::create(['name' => 'Timeline World']);
        $repo = new TimelineEloquentRepository();

        // 1. Create Root (T1)
        $t1Id = (string) Str::uuid();
        $t1 = new TimelineNode($t1Id, [], 'MAIN', new StateSnapshot([], [], 100));
        $repo->save($t1, $world->id);

        // 2. Create Child (T2)
        $t2Id = (string) Str::uuid();
        $t2 = new TimelineNode($t2Id, [$t1Id], 'MAIN', new StateSnapshot([], [], 200));
        $repo->save($t2, $world->id);

        // 3. Verify Persistence
        $this->assertDatabaseHas('timeline_nodes', ['id' => $t1Id]);
        $this->assertDatabaseHas('timeline_nodes', ['id' => $t2Id, 'parent_ids' => json_encode([$t1Id])]); // Note check JSON handling
        
        // 4. Test Causal Consistency Logic w/ Ancestry
        $service = new CausalConsistency($repo);

        // Memory created at T1
        $memAtT1 = new Memory((string) Str::uuid(), 's', 'Fact', 'pub', 1.0, $t1Id);
        
        // T2 (child) should see T1
        $this->assertTrue($service->validateMemoryAccess($t2, $memAtT1));

        // Memory created at T2
        $memAtT2 = new Memory((string) Str::uuid(), 's', 'Future Fact', 'pub', 1.0, $t2Id);

        // T1 (parent) should NOT see T2 (future)
        $this->assertFalse($service->validateMemoryAccess($t1, $memAtT2));
    }
}
