<?php

namespace Tests\Unit\Tuzy\Application\Narrative;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Narrative\GetNarrativeSeries\GetNarrativeSeriesHandler;
use Tuzy\Application\Narrative\GetNarrativeSeries\GetNarrativeSeriesQuery;
use Tuzy\Domain\Narrative\Entity\NarrativeSeries;
use Tuzy\Domain\Narrative\Exception\NarrativeSeriesNotFoundException;
use Tuzy\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

final class GetNarrativeSeriesHandlerTest extends TestCase
{
    public function test_handle_returns_series_when_found(): void
    {
        $series = NarrativeSeries::create('My Series', 'ns-1');
        $repo = new class($series) implements NarrativeSeriesRepositoryInterface {
            public function __construct(private ?NarrativeSeries $series) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?NarrativeSeries { return $this->series; }
            public function save(NarrativeSeries $s): void {}
        };
        $handler = new GetNarrativeSeriesHandler($repo);
        $result = $handler->handle(new GetNarrativeSeriesQuery('ns-1'));
        $this->assertSame($series, $result);
        $this->assertSame('ns-1', $result->getId());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements NarrativeSeriesRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?NarrativeSeries { return null; }
            public function save(NarrativeSeries $s): void {}
        };
        $handler = new GetNarrativeSeriesHandler($repo);
        $this->expectException(NarrativeSeriesNotFoundException::class);
        $this->expectExceptionMessage('NarrativeSeries not found: z');
        $handler->handle(new GetNarrativeSeriesQuery('z'));
    }
}
