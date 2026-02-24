<?php

namespace Tests\Unit\WorldOS\Chronicle;

use Tests\TestCase;
use WorldOS\Legacy\Application\Narrative\Timeline\TimelineNode;
use WorldOS\Saga\Domain\Narrative\ValueObject\StateSnapshot;
use WorldOS\Legacy\Application\Narrative\Character\Entities\Memory;
use Illuminate\Support\Str;

class TimelineDAGTest extends TestCase
{
    public function test_forking_creates_child_node_with_inheritance()
    {
        // 1. Create Root Node
        $rootId = (string) Str::uuid();
        $snapshot = new StateSnapshot(['char-1'], ['weather' => 'sunny'], 100);
        $root = new TimelineNode($rootId, [], 'MAIN', $snapshot);

        // 2. Fork
        $child = $root->fork('ALTERNATE');

        // 3. Assert
        $this->assertContains($rootId, $child->parentIds);
        $this->assertEquals('ALTERNATE', $child->canonicalLevel);
        $this->assertEquals(100, $child->snapshot->worldTick);
        $this->assertEquals(['weather' => 'sunny'], $child->snapshot->globalFlags);
    }

    public function test_memory_can_be_linked_to_timeline_node()
    {
        $nodeId = (string) Str::uuid();
        $memory = new Memory(
            (string) Str::uuid(),
            'episodic',
            'I saw the fork happen',
            'private',
            1.0,
            $nodeId
        );

        $this->assertEquals($nodeId, $memory->timelineNodeId);
    }
}
