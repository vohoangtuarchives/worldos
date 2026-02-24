<?php

namespace Tests\Unit\WorldOS\Evolution\Domain\Legacy\Event;

use PHPUnit\Framework\TestCase;
use WorldOS\Evolution\Domain\Legacy\Event\EvolutionProfileCreated;

final class EvolutionProfileCreatedTest extends TestCase
{
    public function test_event_holds_profile_id_and_name(): void
    {
        $event = new EvolutionProfileCreated('prof-1', 'Profile');
        $this->assertSame('prof-1', $event->profileId);
        $this->assertSame('Profile', $event->profileName);
    }
}
