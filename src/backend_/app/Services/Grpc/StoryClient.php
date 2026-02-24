??php

namespace App\Services\Grpc;

use Worldos\Common\StoryId;
use Worldos\Story\GenerateChapterRequest;
use Worldos\Story\GetStoryRequest;
use Worldos\Story\Seed;
use Worldos\Story\StoryServiceClient;

class StoryClient extends BaseGrpcClient
{
    private StoryServiceClient $client;

    public function __construct(string $target, array $options = [])
    {
        parent::__construct($target, $options);
        $this-client = new StoryServiceClient($target, $this-options);
    }

    public function generateChapter(string $storyId, array $seedData)
    {
        $request = new GenerateChapterRequest();
        $request-setStoryId((new StoryId())-setValue($storyId));

        $seed = new Seed();
        $seed-setType($seedData['type'] ?? '');
        $seed-setDimension($seedData['dimension'] ?? '');
        $seed-setSeverity($seedData['severity'] ?? 0);
        $request-setSeed($seed);

        return $this-unwrap($this-client-GenerateChapter($request));
    }

    public function getStory(string $storyId)
    {
        $request = new GetStoryRequest();
        $request-setStoryId((new StoryId())-setValue($storyId));

        return $this-unwrap($this-client-GetStory($request));
    }
}