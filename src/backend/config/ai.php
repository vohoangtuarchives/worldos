<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho các tính năng AI trong WorldOS
    |
    */

    'enabled' => env('AI_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình API OpenAI cho các tính năng AI
    |
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'timeout' => env('OPENAI_TIMEOUT', 30),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 2000),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Story Generation
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho tính năng tạo cốt truyện AI
    |
    */
    'story_generation' => [
        'enabled' => env('AI_STORY_GENERATION_ENABLED', true),
        'model' => env('AI_STORY_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => env('AI_STORY_MAX_TOKENS', 2000),
        'temperature' => env('AI_STORY_TEMPERATURE', 0.8),
        'cache_ttl' => env('AI_STORY_CACHE_TTL', 3600), // 1 giờ
        'max_events_per_story' => env('AI_MAX_EVENTS_PER_STORY', 5),
        'min_events_per_story' => env('AI_MIN_EVENTS_PER_STORY', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Intelligent NPCs
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho NPC thông minh
    |
    */
    'intelligent_npcs' => [
        'enabled' => env('AI_INTELLIGENT_NPCS_ENABLED', true),
        'model' => env('AI_NPC_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => env('AI_NPC_MAX_TOKENS', 1000),
        'temperature' => env('AI_NPC_TEMPERATURE', 0.6),
        'memory_limit' => env('AI_NPC_MEMORY_LIMIT', 100),
        'decision_timeout' => env('AI_NPC_DECISION_TIMEOUT', 10),
        'max_npcs_per_world' => env('AI_MAX_NPCS_PER_WORLD', 10),
        'personality_traits' => [
            'brave' => ['courage' => 0.8, 'risk_taking' => 0.7],
            'cautious' => ['courage' => 0.3, 'risk_taking' => 0.2],
            'aggressive' => ['aggression' => 0.8, 'dominance' => 0.7],
            'diplomatic' => ['charisma' => 0.8, 'negotiation' => 0.7],
            'scholarly' => ['intelligence' => 0.8, 'curiosity' => 0.7],
            'mysterious' => ['secrecy' => 0.8, 'intrigue' => 0.7],
        ],
        'roles' => [
            'leader' => ['brave', 'diplomatic'],
            'advisor' => ['scholarly', 'cautious'],
            'merchant' => ['diplomatic', 'opportunistic'],
            'scholar' => ['scholarly', 'mysterious'],
            'spy' => ['mysterious', 'cautious'],
            'diplomat' => ['diplomatic', 'cautious'],
            'general' => ['brave', 'aggressive'],
            'influencer' => ['diplomatic', 'charismatic'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dynamic World Events
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho sự kiện thế giới động
    |
    */
    'dynamic_events' => [
        'enabled' => env('AI_DYNAMIC_EVENTS_ENABLED', true),
        'model' => env('AI_EVENT_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => env('AI_EVENT_MAX_TOKENS', 1500),
        'temperature' => env('AI_EVENT_TEMPERATURE', 0.7),
        'cache_ttl' => env('AI_EVENT_CACHE_TTL', 1800), // 30 phút
        'max_events_per_world' => env('AI_MAX_EVENTS_PER_WORLD', 20),
        'event_types' => [
            'conflict' => ['probability' => 0.2, 'severity_range' => [6, 9]],
            'opportunity' => ['probability' => 0.2, 'severity_range' => [3, 6]],
            'crisis' => ['probability' => 0.1, 'severity_range' => [8, 10]],
            'discovery' => ['probability' => 0.15, 'severity_range' => [4, 7]],
            'alliance' => ['probability' => 0.15, 'severity_range' => [2, 5]],
            'betrayal' => ['probability' => 0.1, 'severity_range' => [7, 9]],
            'celebration' => ['probability' => 0.1, 'severity_range' => [1, 3]],
        ],
        'event_locations' => [
            'conflict' => ['Biên giới', 'Thủ đô', 'Căng cứ quân sự', 'Vùng chiến sự'],
            'opportunity' => ['Thị trường', 'Cảng biển', 'Mỏ mỏ', 'Trung tâm thương mại'],
            'crisis' => ['Thành phố', 'Làng mạc', 'Cơ sở hạ tầng', 'Khu dân cư'],
            'discovery' => ['Di tích cổ', 'Thư viện', 'Phòng thí nghiệm', 'Vùng đất xa xôi'],
            'alliance' => ['Hội trường', 'Cung điện', 'Ngoại giao quán', 'Trung tâm hội nghị'],
            'betrayal' => ['Hội nghị bí mật', 'Nơi hẹn ước', 'Trụ sở phe', 'Vùng trung lập'],
            'celebration' => ['Quảng trường', 'Đền thờ', 'Nhà hát', 'Công viên'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Predictive Analytics
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho phân tích dự đoán
    |
    */
    'predictive_analytics' => [
        'enabled' => env('AI_PREDICTIVE_ANALYTICS_ENABLED', true),
        'model' => env('AI_ANALYTICS_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => env('AI_ANALYTICS_MAX_TOKENS', 2500),
        'temperature' => env('AI_ANALYTICS_TEMPERATURE', 0.5),
        'cache_ttl' => env('AI_ANALYTICS_CACHE_TTL', 7200), // 2 giờ
        'time_horizons' => [
            'short_term' => '1_week',
            'medium_term' => '1_month',
            'long_term' => '6_months',
        ],
        'prediction_models' => [
            'political' => ['accuracy' => 0.75, 'type' => 'trend_analysis'],
            'economic' => ['accuracy' => 0.70, 'type' => 'time_series'],
            'social' => ['accuracy' => 0.65, 'type' => 'network_analysis'],
            'technological' => ['accuracy' => 0.60, 'type' => 'innovation_curve'],
        ],
        'risk_thresholds' => [
            'low' => 0.3,
            'medium' => 0.6,
            'high' => 0.8,
            'critical' => 0.9,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Cài đặt hiệu suất cho AI
    |
    */
    'performance' => [
        'max_concurrent_requests' => env('AI_MAX_CONCURRENT_REQUESTS', 5),
        'request_timeout' => env('AI_REQUEST_TIMEOUT', 30),
        'retry_attempts' => env('AI_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('AI_RETRY_DELAY', 1000), // milliseconds
        'rate_limit' => [
            'requests_per_minute' => env('AI_RATE_LIMIT_RPM', 60),
            'requests_per_hour' => env('AI_RATE_LIMIT_RPH', 1000),
        ],
        'memory_limit' => env('AI_MEMORY_LIMIT', '512M'),
        'cache_size_limit' => env('AI_CACHE_SIZE_LIMIT', '100M'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging and Monitoring
    |--------------------------------------------------------------------------
    |
    | Cài đặt logging và monitoring cho AI
    |
    */
    'logging' => [
        'enabled' => env('AI_LOGGING_ENABLED', true),
        'level' => env('AI_LOG_LEVEL', 'info'),
        'log_requests' => env('AI_LOG_REQUESTS', true),
        'log_responses' => env('AI_LOG_RESPONSES', false),
        'log_errors' => env('AI_LOG_ERRORS', true),
        'log_performance' => env('AI_LOG_PERFORMANCE', true),
        'retention_days' => env('AI_LOG_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Cài đặt bảo mật cho AI
    |
    */
    'security' => [
        'validate_api_keys' => env('AI_VALIDATE_API_KEYS', true),
        'encrypt_sensitive_data' => env('AI_ENCRYPT_SENSITIVE_DATA', true),
        'sanitize_inputs' => env('AI_SANITIZE_INPUTS', true),
        'rate_limit_by_ip' => env('AI_RATE_LIMIT_BY_IP', true),
        'audit_log' => env('AI_AUDIT_LOG', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Cờ tính năng để bật/tắt các tính năng AI cụ thể
    |
    */
    'features' => [
        'story_generation' => env('AI_FEATURE_STORY_GENERATION', true),
        'intelligent_npcs' => env('AI_FEATURE_INTELLIGENT_NPCS', true),
        'dynamic_events' => env('AI_FEATURE_DYNAMIC_EVENTS', true),
        'predictive_analytics' => env('AI_FEATURE_PREDICTIVE_ANALYTICS', true),
        'multiplayer_support' => env('AI_FEATURE_MULTIPLAYER', false),
        'real_time_generation' => env('AI_FEATURE_REAL_TIME', false),
        'voice_integration' => env('AI_FEATURE_VOICE', false),
        'image_generation' => env('AI_FEATURE_IMAGE_GENERATION', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    |
    | Cài đặt cho môi trường phát triển
    |
    */
    'development' => [
        'debug_mode' => env('AI_DEBUG_MODE', env('APP_DEBUG', false)),
        'mock_responses' => env('AI_MOCK_RESPONSES', false),
        'save_prompts' => env('AI_SAVE_PROMPTS', false),
        'detailed_logging' => env('AI_DETAILED_LOGGING', false),
        'test_mode' => env('AI_TEST_MODE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent Registry (Per Feature)
    |--------------------------------------------------------------------------
    */
    'agent_registry' => [
        'global.default' => [
            'agent_name' => 'Core Agent',
            'provider' => 'openai',
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
            'options' => ['temperature' => 0.7],
        ],
        'narrative.chronicler' => [
            'agent_name' => 'Narrative Agent',
            'provider' => 'openai',
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
            'options' => ['temperature' => 0.7],
        ],
        'narrative.dialogue' => [
            'agent_name' => 'Dialogue Agent',
            'provider' => 'openai',
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
            'options' => ['temperature' => 0.8],
        ],
        'runtime.universe_evaluator' => [
            'agent_name' => 'Universe Evaluator Agent',
            'provider' => 'openai',
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
            'options' => ['temperature' => 0.3],
        ],
    ],

];
