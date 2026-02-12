??php

namespace App\Services\Grpc;

use Worldos\Common\WorldId;
use Worldos\World\GetWorldStateRequest;
use Worldos\World\TickWorldRequest;
use Worldos\World\WorldSimulationServiceClient;

class WorldServiceClient extends BaseGrpcClient
{
    private WorldSimulationServiceClient $client;

    public function __construct(string $target, array $options = [])
    {
        parent::__construct($target, $options);
        $this->client = new WorldSimulationServiceClient($target, $this->options);
    }

    public function tick(string $worldId, int $ticks = 1)
    {
        $request = new TickWorldRequest();
        $request->setWorldId((new WorldId())->setValue($worldId));
        $request->setTicks($ticks);

        return $this->unwrap($this->client->TickWorld($request));
    }

    public function getState(string $worldId)
    {
        $request = new GetWorldStateRequest();
        $request->setWorldId((new WorldId())->setValue($worldId));

        return $this->unwrap($this->client->GetWorldState($request));
    }
}