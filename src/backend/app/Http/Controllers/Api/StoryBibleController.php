<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domains\Narrative\Services\WorldbuildingService;
use App\Http\Controllers\Controller;
use App\Models\NarrativeSeries;
use App\Models\StoryBible;
use App\Models\StoryBibleCharacter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Story Bible CRUD: GET/PUT /series/{id}/story-bible, GET/POST /series/{id}/story-bible/characters.
 */
class StoryBibleController extends Controller
{
    /**
     * GET /serial/series/{id}/story-bible
     */
    public function show(string $id): JsonResponse
    {
        $series = NarrativeSeries::find($id);
        if ($series === null) {
            return response()->json(['success' => false, 'message' => 'Series not found.'], 404);
        }
        $bible = $series->storyBible;
        if ($bible === null) {
            return response()->json([
                'success' => true,
                'data' => [
                    'story_bible' => null,
                    'message' => 'No Story Bible yet. Use PUT to create.',
                ],
            ]);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'story_bible' => [
                    'id' => $bible->id,
                    'narrative_series_id' => $bible->narrative_series_id,
                    'braindump' => $bible->braindump,
                    'synopsis' => $bible->synopsis,
                    'style_notes' => $bible->style_notes,
                    'worldbuilding_rules' => $bible->worldbuilding_rules,
                    'updated_at' => $bible->updated_at?->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * POST /serial/series/{id}/story-bible/generate-from-premise
     * Body: premise (required), genre_key (optional). Calls LLM to fill synopsis, style_notes, worldbuilding_rules.
     */
    public function generateFromPremise(Request $request, string $id): JsonResponse
    {
        $series = NarrativeSeries::find($id);
        if ($series === null) {
            return response()->json(['success' => false, 'message' => 'Series not found.'], 404);
        }
        $validated = $request->validate([
            'premise' => 'required|string|max:10000',
            'genre_key' => 'nullable|string|max:64',
        ]);
        try {
            $bible = app(WorldbuildingService::class)->generateFromPremise(
                $id,
                $validated['premise'],
                $validated['genre_key'] ?? $series->genre_key ?? 'wuxia'
            );
            return response()->json([
                'success' => true,
                'data' => [
                    'story_bible' => [
                        'id' => $bible->id,
                        'narrative_series_id' => $bible->narrative_series_id,
                        'braindump' => $bible->braindump,
                        'synopsis' => $bible->synopsis,
                        'style_notes' => $bible->style_notes,
                        'worldbuilding_rules' => $bible->worldbuilding_rules,
                        'updated_at' => $bible->updated_at?->toIso8601String(),
                    ],
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Generate from premise failed: ' . $e->getMessage(),
            ], 502);
        }
    }

    /**
     * PUT /serial/series/{id}/story-bible
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $series = NarrativeSeries::find($id);
        if ($series === null) {
            return response()->json(['success' => false, 'message' => 'Series not found.'], 404);
        }
        $validated = $request->validate([
            'braindump' => 'nullable|string|max:65535',
            'synopsis' => 'nullable|string|max:65535',
            'style_notes' => 'nullable|string|max:65535',
            'worldbuilding_rules' => 'nullable|array',
        ]);
        $bible = StoryBible::firstOrCreate(
            ['narrative_series_id' => $series->id],
            ['braindump' => null, 'synopsis' => null, 'style_notes' => null, 'worldbuilding_rules' => null]
        );
        $bible->update($validated);
        return response()->json([
            'success' => true,
            'data' => [
                'story_bible' => [
                    'id' => $bible->id,
                    'narrative_series_id' => $bible->narrative_series_id,
                    'braindump' => $bible->braindump,
                    'synopsis' => $bible->synopsis,
                    'style_notes' => $bible->style_notes,
                    'worldbuilding_rules' => $bible->worldbuilding_rules,
                    'updated_at' => $bible->updated_at?->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * GET /serial/series/{id}/story-bible/characters
     */
    public function indexCharacters(string $id): JsonResponse
    {
        $series = NarrativeSeries::find($id);
        if ($series === null) {
            return response()->json(['success' => false, 'message' => 'Series not found.'], 404);
        }
        $bible = $series->storyBible;
        if ($bible === null) {
            return response()->json(['success' => true, 'data' => ['characters' => []]]);
        }
        $characters = $bible->characters()->orderBy('name')->get()->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'role' => $c->role,
            'traits' => $c->traits,
            'first_seen_chapter' => $c->first_seen_chapter,
            'is_active' => $c->is_active,
        ]);
        return response()->json(['success' => true, 'data' => ['characters' => $characters]]);
    }

    /**
     * POST /serial/series/{id}/story-bible/characters
     */
    public function storeCharacter(Request $request, string $id): JsonResponse
    {
        $series = NarrativeSeries::find($id);
        if ($series === null) {
            return response()->json(['success' => false, 'message' => 'Series not found.'], 404);
        }
        $bible = $series->storyBible;
        if ($bible === null) {
            $bible = StoryBible::create(['narrative_series_id' => $series->id]);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'traits' => 'nullable|array',
            'traits.*' => 'string|max:500',
            'first_seen_chapter' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['story_bible_id'] = $bible->id;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $character = StoryBibleCharacter::create($validated);
        return response()->json([
            'success' => true,
            'data' => [
                'character' => [
                    'id' => $character->id,
                    'name' => $character->name,
                    'role' => $character->role,
                    'traits' => $character->traits,
                    'first_seen_chapter' => $character->first_seen_chapter,
                    'is_active' => $character->is_active,
                ],
            ],
        ], 201);
    }
}
