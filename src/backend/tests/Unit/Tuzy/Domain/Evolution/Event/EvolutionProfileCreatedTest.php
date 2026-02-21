<?php

namespace Tests\Unit\Tuzy\Domain\Evolution\Event;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Evolution\Event\EvolutionProfileCreated;

final class EvolutionProfileCreatedTest extends TestCase
{
    public function test_event_holds_profile_id_and_name(): void
    {
        $event = new EvolutionProfileCreated('prof-1', 'Profile');
        $this->assertSame('prof-1', $event->profileId);
        $this->assertSame('Profile', $event->profileName);
    }
}
