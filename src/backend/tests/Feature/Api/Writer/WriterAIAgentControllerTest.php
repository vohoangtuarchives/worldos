<?php

namespace Tests\Feature\Api\Writer;

use App\Models\AIFeatureAgentConfig;
use App\Models\AIProviderRequestHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterAIAgentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/writer/ai/feature-configs')->assertStatus(401);
    }

    public function test_can_upsert_and_list_feature_configs(): void
    {
        $payload = [
            'feature_key' => 'narrative.dialogue',
            'agent_name' => 'Dialogue Agent B',
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'temperature' => 0.4,
            'enabled' => true,
        ];

        $this->actingAs($this->user)
            ->postJson('/api/writer/ai/feature-configs', $payload)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.feature_key', 'narrative.dialogue')
            ->assertJsonPath('data.agent_name', 'Dialogue Agent B');

        $this->assertDatabaseHas('ai_feature_agent_configs', [
            'feature_key' => 'narrative.dialogue',
            'agent_name' => 'Dialogue Agent B',
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/writer/ai/feature-configs')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment([
                'feature_key' => 'narrative.dialogue',
                'agent_name' => 'Dialogue Agent B',
            ]);
    }

    public function test_can_delete_feature_config(): void
    {
        AIFeatureAgentConfig::query()->create([
            'feature_key' => 'runtime.universe_evaluator',
            'agent_name' => 'Evaluator Agent',
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'enabled' => true,
            'options' => ['temperature' => 0.3],
        ]);

        $this->actingAs($this->user)
            ->deleteJson('/api/writer/ai/feature-configs/runtime.universe_evaluator')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('deleted', true);

        $this->assertDatabaseMissing('ai_feature_agent_configs', [
            'feature_key' => 'runtime.universe_evaluator',
        ]);
    }

    public function test_can_list_logs_filters_and_log_detail(): void
    {
        $log = AIProviderRequestHistory::query()->create([
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'feature_key' => 'narrative.chronicler',
            'agent_name' => 'Narrative Agent',
            'system_prompt' => 'system',
            'user_prompt' => 'user',
            'request_payload' => '{"a":1}',
            'response_payload' => '{"b":2}',
            'http_status' => 200,
            'status' => 'SUCCESS',
            'duration_ms' => 120,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/writer/ai/request-logs?feature_key=narrative.chronicler')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.data.0.feature_key', 'narrative.chronicler');

        $this->actingAs($this->user)
            ->getJson('/api/writer/ai/request-logs/filters')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['narrative.chronicler'])
            ->assertJsonFragment(['Narrative Agent'])
            ->assertJsonFragment(['SUCCESS']);

        $this->actingAs($this->user)
            ->getJson('/api/writer/ai/request-logs/' . $log->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $log->id)
            ->assertJsonPath('data.response_payload', '{"b":2}');
    }
}
