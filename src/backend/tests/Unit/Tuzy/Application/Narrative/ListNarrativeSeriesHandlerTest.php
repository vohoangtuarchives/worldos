<?php

namespace Tests\Unit\Tuzy\Application\Narrative;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Narrative\ListNarrativeSeries\ListNarrativeSeriesHandler;
use Tuzy\Application\Narrative\ListNarrativeSeries\ListNarrativeSeriesQuery;
use Tuzy\Application\Narrative\ListNarrativeSeries\ListNarrativeSeriesResult;
use Tuzy\Domain\Narrative\Entity\NarrativeSeries;
use Tuzy\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

final class ListNarrativeSeriesHandlerTest extends TestCase
{
    public function test_handle_returns_all_series_as_result(): void
    {
        $n1 = NarrativeSeries::create('Series A', 'ns-1');
        $n2 = NarrativeSeries::create('Series B', 'ns-2');
        $repo = new class($n1, $n2) implements NarrativeSeriesRepositoryInterface {
            public function __construct(private NarrativeSeries $n1, private NarrativeSeries $n2) {}
            public function findAll(): array { return [$this->n1, $this->n2]; }
            public function findById(string $id): ?NarrativeSeries { return null; }
            public function save(NarrativeSeries $s): void {}
        };
        $handler = new ListNarrativeSeriesHandler($repo);
        $result = $handler->handle(new ListNarrativeSeriesQuery());
        $this->assertInstanceOf(ListNarrativeSeriesResult::class, $result);
        $this->assertCount(2, $result->narrativeSeries);
        $this->assertSame('ns-1', $result->narrativeSeries[0]['id']);
        $this->assertSame('Series A', $result->narrativeSeries[0]['title']);
        $this->assertSame('ns-2', $result->narrativeSeries[1]['id']);
        $this->assertSame('Series B', $result->narrativeSeries[1]['title']);
    }

    public function test_handle_returns_empty_when_no_series(): void
    {
        $repo = new class() implements NarrativeSeriesRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?NarrativeSeries { return null; }
            public function save(NarrativeSeries $s): void {}
        };
        $handler = new ListNarrativeSeriesHandler($repo);
        $result = $handler->handle(new ListNarrativeSeriesQuery());
        $this->assertSame([], $result->narrativeSeries);
    }
}
