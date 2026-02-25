<?php
require __DIR__ . '/vendor/autoload.php';

$redis = new Redis();
$redis->pconnect('redis', 6379);

// Timeout: 10s
$redis->setOption(Redis::OPT_READ_TIMEOUT, 10);

echo "Subscribing to simulation_events...\n";
try {
    $redis->subscribe(['simulation_events'], function ($redis, $chan, $msg) {
        echo "RECEIVED: " . $msg . "\n";
        exit(0);
    });
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'read error') !== false) {
        echo "Timeout reached.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
