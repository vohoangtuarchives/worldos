<?php

namespace Tests\Unit\Tuzy\Application\Narrative;

use PHPUnit\Framework\TestCase;
use Tuzy\Application\Narrative\CreateNarrativeSeries\CreateNarrativeSeriesCommand;
use Tuzy\Application\Narrative\CreateNarrativeSeries\CreateNarrativeSeriesHandler;
use Tuzy\Application\Narrative\CreateNarrativeSeries\CreateNarrativeSeriesResult;
use Tuzy\Domain\Narrative\Entity\NarrativeSeries;
use Tuzy\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

final class CreateNarrativeSeriesHandlerTest extends TestCase
{
    public function test_handle_creates_series_saves_returns_result(): void
    {
        $saved = [];
        $repo = new class($saved) implements NarrativeSeriesRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findById(string $id): ?NarrativeSeries { return null; }
            public function save(NarrativeSeries $s): void { $this->saved[] = $s; }
        };
        $handler = new CreateNarrativeSeriesHandler($repo);
        $result = $handler->handle(new CreateNarrativeSeriesCommand('My Series'));
        $this->assertInstanceOf(CreateNarrativeSeriesResult::class, $result);
        $this->assertNotEmpty($result->id);
        $this->assertSame('My Series', $result->title);
        $this->assertCount(1, $saved);
    }
}
