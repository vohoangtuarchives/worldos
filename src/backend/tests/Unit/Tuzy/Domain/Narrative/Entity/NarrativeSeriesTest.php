<?php

namespace Tests\Unit\Tuzy\Domain\Narrative\Entity;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Narrative\Entity\NarrativeSeries;

final class NarrativeSeriesTest extends TestCase
{
    public function test_create_returns_entity_with_getters(): void
    {
        $series = NarrativeSeries::create('My Series');
        $this->assertInstanceOf(NarrativeSeries::class, $series);
        $this->assertNotEmpty($series->getId());
        $this->assertSame('My Series', $series->getTitle());
    }
}
