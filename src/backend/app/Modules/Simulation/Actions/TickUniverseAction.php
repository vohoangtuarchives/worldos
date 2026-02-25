<?php

namespace App\Modules\Simulation\Actions;

use App\Modules\Simulation\Services\Grpc\SimulationEngineGrpcClient;
use Illuminate\Support\Facades\Log;

class TickUniverseAction
{
    private SimulationEngineGrpcClient $grpcClient;

    public function __construct(SimulationEngineGrpcClient $grpcClient)
    {
        $this->grpcClient = $grpcClient;
    }

    /**
     * @param string $universeId
     * @param array $config Must contain: dimension, alpha, lambda, eta, beta, deltaTarget, gammaCap, rMax, energyRateLimit
     * @param array $currentState Flat array length N
     * @param array $controlVector Flat array length N
     * @param array $aMatrix Flat array length NxN
     * @param array $lMatrix Flat array length NxN
     * @return array
     * @throws \Exception
     */
    public function handle(
        string $universeId,
        array $config,
        array $currentState,
        array $controlVector,
        array $aMatrix,
        array $lMatrix
    ): array {
        Log::info("Executing Tick for Universe: {$universeId}");

        $response = $this->grpcClient->runTick(
            $universeId,
            $config['dimension'],
            $currentState,
            $controlVector,
            $aMatrix,
            $lMatrix,
            $config['alpha'],
            $config['lambda'],
            $config['eta'],
            $config['beta'],
            $config['deltaTarget'],
            $config['gammaCap'],
            $config['rMax'],
            $config['energyRateLimit']
        );

        if (!$response['success']) {
            Log::warning("Universe {$universeId} tick rejected by GovernanceGuard: " . $response['error_message']);
            // Publish Event or throw exception depending on Business Logic
            return [
                'status' => 'rejected',
                'reason' => $response['error_message'],
            ];
        }

        Log::info("Universe {$universeId} tick successful.");
        return [
            'status'     => 'success',
            'next_state' => $response['next_state'],
        ];
    }
}
