<?php

namespace Tests\Unit\WorldOS\Saga\Domain\Narrative;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Narrative\Entity\NarrativeSeries;

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
