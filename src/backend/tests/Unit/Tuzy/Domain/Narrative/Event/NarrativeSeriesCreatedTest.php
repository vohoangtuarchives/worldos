<?php

namespace Tests\Unit\Tuzy\Domain\Narrative\Event;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Narrative\Event\NarrativeSeriesCreated;

final class NarrativeSeriesCreatedTest extends TestCase
{
    public function test_event_holds_series_id_and_title(): void
    {
        $event = new NarrativeSeriesCreated('series-1', 'My Series');
        $this->assertSame('series-1', $event->seriesId);
        $this->assertSame('My Series', $event->title);
    }
}
