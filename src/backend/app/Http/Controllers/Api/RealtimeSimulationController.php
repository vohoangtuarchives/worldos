<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tuzy\Infrastructure\Realtime\RealtimeStreamServer;
use Illuminate\Support\Facades\Redis;

class RealtimeSimulationController
{
    public function __construct(
        private RealtimeStreamServer $streamServer
    ) {}

    /**
     * Streams simulation data for a specific world using SSE.
     * Payload is binary encoded using MessagePack for maximum efficiency.
     */
    public function stream(string $worldId): StreamedResponse
    {
        return new StreamedResponse(function () use ($worldId) {
            // SSE headers
            echo "Content-Type: text/event-stream\n";
            echo "Cache-Control: no-cache\n";
            echo "Connection: keep-alive\n";
            echo "X-Accel-Buffering: no\n\n";

            $streamKey = 'world_stream:' . $worldId;
            $lastId = '$'; // Listen for new entries only

            // Check if there's any initial state to send
            $initial = $this->streamServer->getLatestBinary($worldId);
            if ($initial) {
                $base64 = base64_encode($initial);
                echo "data: {$base64}\n\n";
                ob_flush();
                flush();
            }

            // Loop and block until new data arrives in Redis Stream
            while (true) {
                if (connection_aborted()) {
                    break;
                }

                // XREAD BLOCK 5000 STREAMS key lastId
                $results = Redis::xread([$streamKey => $lastId], 0, 5000);

                if ($results && isset($results[$streamKey])) {
                    foreach ($results[$streamKey] as $id => $fields) {
                        $lastId = $id;
                        $binary = $fields['p'] ?? '';
                        $eventType = $fields['type'] ?? 'metric';
                        if ($binary) {
                            // SSE data must be string, so we base64 encode the MessagePack binary
                            $base64 = base64_encode($binary);
                            echo "event: {$eventType}\n";
                            echo "data: {$base64}\n\n";
                        }
                    }
                    ob_flush();
                    flush();
                }

                // Small sleep to prevent tight loop if XREAD returns instantly (though BLOCK should prevent this)
                usleep(10000); 
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
