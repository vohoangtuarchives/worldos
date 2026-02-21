<?php

namespace Tuzy\Infrastructure\Persistence\Narrative;

use Tuzy\Domain\Narrative\Repository\ArchiveRepository;
use Tuzy\Domain\Narrative\Entity\Archive;
use Tuzy\Domain\Narrative\Entity\Episode;
use App\Models\StoryArc as StoryArcModel;
use App\Models\Chapter as ChapterModel;

class EloquentArchiveRepository implements ArchiveRepository
{
    public function save(Archive $archive): void
    {
        $reflection = new \ReflectionClass(Archive::class);
        $worldIdProp = $reflection->getProperty('worldId'); $worldIdProp->setAccessible(true);
        $instabilityProp = $reflection->getProperty('epistemicInstability'); $instabilityProp->setAccessible(true);
        $episodesProp = $reflection->getProperty('episodes'); $episodesProp->setAccessible(true);
        
        $worldId = $worldIdProp->getValue($archive);
        $epistemicInstability = $instabilityProp->getValue($archive);
        $episodes = $episodesProp->getValue($archive);

        // Lưu Archive dưới dạng StoryArc gốc của World
        $arcModel = StoryArcModel::updateOrCreate(
            ['world_id' => $worldId, 'name' => 'Main Archive'],
            [
                'status' => 'active',
                'description' => json_encode(['epistemic_instability' => $epistemicInstability])
            ]
        );

        // Lưu các Episodes thành Chapters
        foreach ($episodes as $episode) {
            ChapterModel::updateOrCreate(
                ['id' => $episode->id],
                [
                    'story_arc_id' => $arcModel->id,
                    'title' => $episode->title,
                    'content' => $episode->content,
                    'status' => 'published',
                    'order' => $episode->year
                ]
            );
        }
    }

    public function findByWorld(string $worldId): ?Archive
    {
        $arcModel = StoryArcModel::where('world_id', $worldId)->where('name', 'Main Archive')->first();
        if (!$arcModel) return null;

        $desc = json_decode($arcModel->description ?? '{}', true);
        $instability = $desc['epistemic_instability'] ?? 0.0;

        $archive = new Archive($worldId);
        $reflection = new \ReflectionClass(Archive::class);
        $instabilityProp = $reflection->getProperty('epistemicInstability');
        $instabilityProp->setAccessible(true);
        $instabilityProp->setValue($archive, $instability);
        
        $episodesProp = $reflection->getProperty('episodes');
        $episodesProp->setAccessible(true);

        $chapters = ChapterModel::where('story_arc_id', $arcModel->id)->orderBy('order')->get();
        $episodes = [];
        foreach ($chapters as $chap) {
            $episodes[] = new Episode($chap->id, $chap->title, $chap->content, $chap->order);
        }
        $episodesProp->setValue($archive, $episodes);

        return $archive;
    }
}
