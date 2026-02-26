<?php

namespace App\Domain\Simulation\Services;

use Simulation\SimulationEngineClient;
use Simulation\TickRequest;
use Grpc\ChannelCredentials;
use Illuminate\Support\Facades\Log;

class GrpcSimulationEngineClient implements SimulationEngineClientInterface
{
    protected SimulationEngineClient $client;

    public function __construct(string $host = '127.0.0.1:50051')
    {
        // Khởi tạo gRPC Client (Insecure mode cho local/internal network)
        $this->client = new SimulationEngineClient($host, [
            'credentials' => ChannelCredentials::createInsecure(),
        ]);
    }

    public function runTick(
        string $universeId,
        int $dimension,
        array $currentState,
        array $controlVector,
        array $aMatrix,
        array $lMatrix,
        array $params
    ): ?array {
        $request = new TickRequest();
        $request->setUniverseId($universeId);
        $request->setDimension($dimension);
        $request->setCurrentState($currentState);
        $request->setControlVector($controlVector);
        $request->setAMatrix($aMatrix);
        $request->setLMatrix($lMatrix);

        // Nạp Core Parameters
        $request->setAlpha($params['alpha'] ?? 0.25);
        $request->setLambda($params['lambda'] ?? 0.0);
        $request->setEta($params['eta'] ?? 0.3);
        $request->setBeta($params['beta'] ?? 0.01);

        // Nạp Guard Parameters
        $request->setDeltaTarget($params['delta_target'] ?? 0.08);
        $request->setGammaCap($params['gamma_cap'] ?? 1.5);
        $request->setRMax($params['r_max'] ?? 1000.0);
        $request->setEnergyRateLimit($params['energy_rate_limit'] ?? 1.0);

        // Nạp Cascade Parameters (Mặc định mảng rỗng nếu chưa có)
        $request->setCurrentCascade($params['current_cascade'] ?? [0.5, 0.5, 0.5, 0.5, 0.5]);
        $request->setCascadeThresholds($params['cascade_thresholds'] ?? [0.7, 0.7, 0.7, 0.7]);
        $request->setLawVector($params['law_vector'] ?? array_fill(0, 17, 0.1));

        // Gọi gRPC Call
        /** @var \Simulation\TickResponse $reply */
        /** @var \Grpc\Call $status */
        list($reply, $status) = $this->client->RunTick($request)->wait();

        if ($status->code !== \Grpc\STATUS_OK) {
            Log::error("gRPC Connection Error to Rust Engine: " . $status->details);
            return null;
        }

        if (!$reply->getSuccess()) {
            Log::warning("Simulation Engine Rejected Tick. Universe ID: {$universeId}. Reason: " . $reply->getErrorMessage());
            return null;
        }

        $nextState = [];
        foreach ($reply->getNextState() as $val) {
            $nextState[] = $val;
        }

        $nextCascadeState = [];
        foreach ($reply->getNextCascadeState() as $val) {
            $nextCascadeState[] = $val;
        }

        return [
            'state' => $nextState,
            'cascade' => $nextCascadeState,
        ];
    }
}
