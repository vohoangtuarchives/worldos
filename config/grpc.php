<?php

return [
    'world_service' => env('WORLD_SERVICE_TARGET', 'world-simulation:50051'),
    'story_service' => env('STORY_SERVICE_TARGET', 'story-service:50051'),
    'material_service' => env('MATERIAL_SERVICE_TARGET', 'material-service:50051'),
    'default_timeout' => env('GRPC_DEFAULT_TIMEOUT_MS', 5000),
];