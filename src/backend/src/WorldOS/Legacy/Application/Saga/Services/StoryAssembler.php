<?php

namespace WorldOS\Legacy\Application\Saga\Services;

use App\Models\Story;
use App\Models\Chapter;
use WorldOS\Saga\Domain\Legacy\ValueObject\StoryMetadataDTO;
use WorldOS\Legacy\Domain\Genre\ValueObject\GenreProfile;
use Illuminate\Support\Facades\DB;

class StoryAssembler
{
    public function __construct(
        private readonly NarrativeTagger $tagger,
        private readonly TitleGenerator $titleGenerator
    ) {}

    public function assemble(string $worldId, StoryMetadataDTO $metadata): Story
    {
        return DB::transaction(function () use ($worldId, $metadata) {
            
            // 0. Auto-tagging if tags are empty
            $tags = $metadata->tags;
            if (empty($tags)) {
                $profile = new GenreProfile([
                    $metadata->primaryGenre => 1.0
                ]);
                $tags = $this->tagger->generateTags($profile);
            }

            // 1. Create/Update Story record
            $story = Story::updateOrCreate(
                ['world_id' => $worldId],
                [
                    'title' => $metadata->title,
                    'status' => 'completed',
                    'world_state' => json_encode([
                        'tags' => $tags, 
                        'genre' => $metadata->primaryGenre
                    ], JSON_UNESCAPED_UNICODE)
                ]
            );

            // 2. Fetch Chronicles for this world
            $chronicles = DB::table('chronicles')
                ->where('world_id', $worldId)
                ->orderBy('epoch', 'asc')
                ->get();

            // 3. Word-Count Based Grouping Logic
            $targetWordCount = 15000; // Target: 1.5 vạn chữ
            $currentChapterContent = "";
            $chapterOrder = 1;

            foreach ($chronicles as $chronicle) {
                $currentChapterContent .= $chronicle->content . "\n\n";
                
                // Word count check: Vietnamese words are roughly 4.5 chars including spaces
                if (strlen($currentChapterContent) > ($targetWordCount * 4.5)) {
                    $this->createChapter($story->id, $chapterOrder, $currentChapterContent);
                    $currentChapterContent = "";
                    $chapterOrder++;
                }
            }

            // Flush remaining content into a final chapter
            if (!empty($currentChapterContent)) {
                $this->createChapter($story->id, $chapterOrder, $currentChapterContent);
            }

            return $story;
        });
    }

    private function createChapter(int $storyId, int $order, string $content): void
    {
        $title = "Chương $order: " . $this->generateInitialTitleFromContent($content);

        Chapter::updateOrCreate(
            ['story_id' => $storyId, 'order' => $order],
            ['title' => $title, 'content' => $content]
        );
    }

    private function generateInitialTitleFromContent(string $content): string
    {
        $evocativeTitles = [
            "Hỗn Loạn Khởi Đầu", "Vận Mệnh Xoay Vần", "Huyết Sắc Chân Trời", 
            "Bóng Tối Phủ Giăng", "Bình Minh Đẫm Máu", "Thiên Luân Đạo Nghịch"
        ];
        return $evocativeTitles[array_rand($evocativeTitles)];
    }
}
