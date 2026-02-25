<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ListenSimulationEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulation:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen to simulation_events from Rust Engine via Redis PubSub';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Starting Redis PubSub listener on channel 'simulation_events'...");

        try {
            // Subscribe to the Redis channel
            Redis::subscribe(['simulation_events'], function (string $message) {
                $payload = json_decode($message, true);
                
                if (!$payload) {
                    $this->error("Received malformed JSON message");
                    return;
                }

                $type = $payload['type'] ?? 'UNKNOWN';
                $universeId = $payload['universe_id'] ?? 'unknown';

                if ($type === 'TICK_COMPLETED') {
                    // For Frontend Observers (Real-time Feed)
                    // We can broadcast this out via Laravel Reverb/Pusher later.
                    // The actual DB update and recursive queueing is handled safely 
                    // within TickUniverseJob to prevent Race Conditions.
                    $this->info("✅ [TICK_COMPLETED] Universe {$universeId}");
                } elseif ($type === 'GOVERNANCE_VIOLATION') {
                    $reason = $payload['reason'] ?? 'Unknown Reason';
                    $this->error("🚨 [GOVERNANCE_VIOLATION] Universe {$universeId} | {$reason}");
                } else {
                    $this->line("ℹ️ [{$type}] Universe {$universeId}");
                }
            });
        } catch (\Exception $e) {
            $this->error("Redis listener crashed: " . $e->getMessage());
            Log::error("Redis listener crashed", ['exception' => $e]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
