<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\World;
use App\Domains\Saga\Services\StoryAssembler;
use App\Domains\Saga\Services\TitleGenerator;
use App\Domains\Saga\Dto\StoryMetadataDTO;
use Illuminate\Http\Request;

class StoryPublicationController extends Controller
{
    public function __construct(
        private readonly StoryAssembler $assembler,
        private readonly TitleGenerator $titleGenerator
    ) {}

    /**
     * Publish a World into a Story.
     * This creates the Story record, generates Chapters, and tags the story.
     */
    public function publish(Request $request, World $world)
    {
        // 1. Validate
        // (In future: Allow user to input custom title/genre in a modal before this)
        
        // 2. Prepare Metadata
        // Auto-detect genre from World or default to 'Mundane'
        $primaryGenre = $world->genre ?? 'mundane';
        $generatedTitle = $this->titleGenerator->generateStoryTitle($world->name, $primaryGenre);
        
        $metadata = new StoryMetadataDTO(
            title: $generatedTitle,
            description: $world->description ?? "Một biên niên sử về sự trỗi dậy và sụp đổ của các nền văn minh tại $world->name.",
            tags: [], // Let NarrativeTagger handle this
            primaryGenre: $primaryGenre
        );

        // 3. Assemble Story
        try {
            $story = $this->assembler->assemble($world->id, $metadata);
            return redirect()->route('writer.story.show', $story)
                ->with('success', 'Saga đã được xuất bản thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi xuất bản: ' . $e->getMessage());
        }
    }

    /**
     * Display the Official Book Viewer.
     */
    public function show(Story $story)
    {
        // Eager load chapters efficiently
        $story->load(['chapters' => function ($query) {
            $query->orderBy('order', 'asc');
        }]);

        // Decode World State (Tags, Genre)
        $worldState = is_string($story->world_state) 
            ? json_decode($story->world_state, true) 
            : $story->world_state;

        return view('writer.saga.book', compact('story', 'worldState'));
    }
}
