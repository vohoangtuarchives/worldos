<?php

namespace Tests\Unit\WorldOS\Legacy\Application\Narrative;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Application\Narrative\UpdateNarrativeSeries\UpdateNarrativeSeriesCommand;
use WorldOS\Legacy\Application\Narrative\UpdateNarrativeSeries\UpdateNarrativeSeriesHandler;
use WorldOS\Saga\Domain\Narrative\Entity\NarrativeSeries;
use WorldOS\Saga\Domain\Narrative\Exception\NarrativeSeriesNotFoundException;
use WorldOS\Saga\Domain\Narrative\Repository\NarrativeSeriesRepositoryInterface;

final class UpdateNarrativeSeriesHandlerTest extends TestCase
{
    public function test_handle_updates_title_and_saves(): void
    {
        $saved = [];
        $repo = new class($saved) implements NarrativeSeriesRepositoryInterface {
            public function __construct(private array &$saved) {}
            public function findAll(): array { return []; }
            public function findById(string $id): ?NarrativeSeries { return NarrativeSeries::create('Old Title', $id); }
            public function save(NarrativeSeries $s): void { $this->saved[] = $s; }
        };
        $handler = new UpdateNarrativeSeriesHandler($repo);
        $handler->handle(new UpdateNarrativeSeriesCommand('ns-1', 'New Title'));
        $this->assertCount(1, $saved);
        $this->assertSame('ns-1', $saved[0]->getId());
        $this->assertSame('New Title', $saved[0]->getTitle());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = new class() implements NarrativeSeriesRepositoryInterface {
            public function findAll(): array { return []; }
            public function findById(string $id): ?NarrativeSeries { return null; }
            public function save(NarrativeSeries $s): void {}
        };
        $handler = new UpdateNarrativeSeriesHandler($repo);
        $this->expectException(NarrativeSeriesNotFoundException::class);
        $handler->handle(new UpdateNarrativeSeriesCommand('z', 'Any'));
    }
}
