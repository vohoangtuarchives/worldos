<?php

namespace WorldOS\Legacy\Application\Narrative\LLM\Support;

use App\Models\AIProviderRequestHistory;
use Illuminate\Support\Facades\Log;

class AIProviderRequestLogger
{
    /**
     * Ghi lại đầy đủ lịch sử request/response khi gọi AI provider.
     */
    public function log(array $payload): void
    {
        try {
            AIProviderRequestHistory::query()->create($payload);
        } catch (\Throwable $exception) {
            // Không làm fail luồng chính nếu logging DB lỗi.
            Log::error('Failed to persist AI provider request history', [
                'error' => $exception->getMessage(),
                'provider' => $payload['provider'] ?? 'unknown',
            ]);
        }
    }
}
