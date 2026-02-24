<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default LLM driver
    |--------------------------------------------------------------------------
    | openai | alibaba | (future: anthropic, azure, etc.)
    */
    'default' => env('LLM_DRIVER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Log full request (prompts + payload) để debug / cải thiện prompt
    |--------------------------------------------------------------------------
    | Bật LLM_LOG_REQUESTS=1 trong .env để ghi toàn bộ system + user prompt và payload vào log.
    */
    'log_requests' => filter_var(env('LLM_LOG_REQUESTS', '0'), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Driver configurations (OpenAI-compatible chat/completions)
    |--------------------------------------------------------------------------
    */
    /*
    | Tất cả driver dùng chung một bộ biến môi trường (LLM_*).
    | Chỉ đổi LLM_DRIVER và gán giá trị tương ứng cho LLM_API_KEY, LLM_BASE_URL, LLM_MODEL...
    | Fallback: nếu chưa set LLM_* thì đọc OPENAI_* (tương thích .env cũ).
    */
    'drivers' => [

        'openai' => [
            'label' => 'OpenAI',
            'api_key' => env('LLM_API_KEY', env('OPENAI_API_KEY')),
            'base_url' => env('LLM_BASE_URL', env('OPENAI_BASE_URL', 'https://api.openai.com/v1')),
            'model' => env('LLM_MODEL', env('OPENAI_MODEL', 'gpt-4-turbo-preview')),
            'timeout' => max(60, (int) (env('LLM_TIMEOUT') ?? env('OPENAI_TIMEOUT', 120))),
            'response_format' => filter_var(env('LLM_RESPONSE_FORMAT', env('OPENAI_RESPONSE_FORMAT', '1')), FILTER_VALIDATE_BOOLEAN),
            'extra_body' => [],
        ],

        'alibaba' => [
            'label' => 'Alibaba DashScope (Tongyi / Qwen)',
            'api_key' => env('LLM_API_KEY', env('OPENAI_API_KEY')),
            'base_url' => env('LLM_BASE_URL', env('OPENAI_BASE_URL', 'https://dashscope.aliyuncs.com/compatible-mode/v1')),
            'model' => env('LLM_MODEL', env('OPENAI_MODEL', 'qwen-turbo')),
            'timeout' => max(60, (int) (env('LLM_TIMEOUT') ?? env('OPENAI_TIMEOUT', 120))),
            'response_format' => filter_var(env('LLM_RESPONSE_FORMAT', env('OPENAI_RESPONSE_FORMAT', '0')), FILTER_VALIDATE_BOOLEAN),
            'extra_body' => [
                'enable_thinking' => filter_var(env('LLM_ENABLE_THINKING', env('OPENAI_ENABLE_THINKING', '0')), FILTER_VALIDATE_BOOLEAN),
            ],
        ],

        // Thêm driver mới: cùng bộ LLM_API_KEY, LLM_BASE_URL, LLM_MODEL...
        // 'anthropic' => [ 'api_key' => env('LLM_API_KEY'), 'base_url' => env('LLM_BASE_URL'), ... ],
    ],

];
