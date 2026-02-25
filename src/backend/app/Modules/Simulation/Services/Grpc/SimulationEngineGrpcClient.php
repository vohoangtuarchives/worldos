<?php

namespace App\Modules\Simulation\Services\Grpc;

use Grpc\ChannelCredentials;
use Simulation\SimulationEngineClient;
use Simulation\TickRequest;

class SimulationEngineGrpcClient
{
    private SimulationEngineClient $client;

    public function __construct()
    {
        $host = config('simulation.engine_grpc_host', 'localhost:50051');
        $this->client = new SimulationEngineClient($host, [
            'credentials' => ChannelCredentials::createInsecure(),
        ]);
    }

    /**
     * @param string $universeId
     * @param int $dimension
     * @param array $currentState
     * @param array $controlVector
     * @param array $aMatrix Flattened
     * @param array $lMatrix Flattened
     * @param float $alpha
     * @param float $lambda
     * @param float $eta
     * @param float $beta
     * @param float $deltaTarget
     * @param float $gammaCap
     * @param float $rMax
     * @param float $energyRateLimit
     * @param array $currentCascade Flattened (length 5)
     * @param array $cascadeThresholds Flattened (length 4)
     * @param array $lawVector Flattened (length 17)
     * @return array [bool $success, array $nextState, array $nextCascadeState, string $errorMessage]
     */
    public function runTick(
        string $universeId,
        int $dimension,
        array $currentState,
        array $controlVector,
        array $aMatrix,
        array $lMatrix,
        float $alpha,
        float $lambda,
        float $eta,
        float $beta,
        float $deltaTarget,
        float $gammaCap,
        float $rMax,
        float $energyRateLimit,
        array $currentCascade,
        array $cascadeThresholds,
        array $lawVector
    ): array {
        $request = new TickRequest();
        $request->setUniverseId($universeId);
        $request->setDimension($dimension);
        $request->setCurrentState($currentState);
        $request->setControlVector($controlVector);
        $request->setAMatrix($aMatrix);
        $request->setLMatrix($lMatrix);

        $request->setAlpha($alpha);
        $request->setLambda($lambda);
        $request->setEta($eta);
        $request->setBeta($beta);

        $request->setDeltaTarget($deltaTarget);
        $request->setGammaCap($gammaCap);
        $request->setRMax($rMax);
        $request->setEnergyRateLimit($energyRateLimit);

        $request->setCurrentCascade($currentCascade);
        $request->setCascadeThresholds($cascadeThresholds);
        $request->setLawVector($lawVector);

        /** @var \Simulation\TickResponse $response */
        list($response, $status) = $this->client->RunTick($request)->wait();

        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \Exception("gRPC Error: " . $status->details);
        }

        return [
            'success'            => $response->getSuccess(),
            'next_state'         => iterator_to_array($response->getNextState()),
            'next_cascade_state' => iterator_to_array($response->getNextCascadeState()),
            'error_message'      => $response->getErrorMessage(),
        ];
    }
}
