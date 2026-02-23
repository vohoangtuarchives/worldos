<?php

namespace Tests\Unit\WorldOS\Evolution\Domain\Legacy\Entity;

use PHPUnit\Framework\TestCase;
use WorldOS\Evolution\Domain\Legacy\Entity\EvolutionProfile;

final class EvolutionProfileTest extends TestCase
{
    public function test_create_returns_entity_with_getters(): void
    {
        $profile = EvolutionProfile::create('Test Profile');
        $this->assertInstanceOf(EvolutionProfile::class, $profile);
        $this->assertNotEmpty($profile->getId());
        $this->assertSame('Test Profile', $profile->getName());
    }
}
