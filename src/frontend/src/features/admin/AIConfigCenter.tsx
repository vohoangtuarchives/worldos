"use client";

import Link from "next/link";
import { useMemo, useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";

type ProviderConfig = {
  id: string;
  name: string;
  model: string;
  endpoint: string;
  enabled: boolean;
};

type AgentConfig = {
  key: string;
  title: string;
  description: string;
  temperature: number;
  maxTokens: number;
  topP: number;
  promptPreset: string;
};

const SOURCE_CONFIG_FILES = [
  "src/backend/config/ai.php",
  "src/backend/config/llm.php",
  "src/backend/config/worldos.php",
  "src/backend/config/saga.php",
  "src/backend/config/evolution.php",
];

const INITIAL_PROVIDERS: ProviderConfig[] = [
  {
    id: "openai",
    name: "OpenAI",
    model: "gpt-4o-mini",
    endpoint: "https://api.openai.com/v1",
    enabled: true,
  },
  {
    id: "anthropic",
    name: "Anthropic",
    model: "claude-3-5-sonnet-latest",
    endpoint: "https://api.anthropic.com",
    enabled: false,
  },
  {
    id: "local-vllm",
    name: "Local vLLM",
    model: "qwen2.5-14b-instruct",
    endpoint: "http://localhost:8000/v1",
    enabled: false,
  },
];

const INITIAL_AGENTS: AgentConfig[] = [
  {
    key: "character",
    title: "AI Character",
    description: "Quản lý persona, memory style, voice của nhân vật AI.",
    temperature: 0.8,
    maxTokens: 3000,
    topP: 0.95,
    promptPreset: "character_depth_v2",
  },
  {
    key: "writer",
    title: "AI Writer",
    description: "Quản lý flow viết chương, tóm tắt, mở rộng mạch truyện.",
    temperature: 0.7,
    maxTokens: 4000,
    topP: 0.9,
    promptPreset: "saga_writer_v3",
  },
  {
    key: "narrative-orchestrator",
    title: "Narrative Orchestrator",
    description: "Điều phối AI Character + AI Writer + logic consistency.",
    temperature: 0.4,
    maxTokens: 2000,
    topP: 0.85,
    promptPreset: "orchestrator_control_v1",
  },
];

function countConfiguredProviders(providers: ProviderConfig[]) {
  return providers.filter((provider) => provider.enabled).length;
}

export function AIConfigCenter() {
  const [providers, setProviders] = useState(INITIAL_PROVIDERS);
  const [agents, setAgents] = useState(INITIAL_AGENTS);
  const [savedAt, setSavedAt] = useState<string | null>(null);

  const configuredProviderCount = useMemo(() => countConfiguredProviders(providers), [providers]);

  const updateProvider = (id: string, patch: Partial<ProviderConfig>) => {
    setProviders((current) => current.map((provider) => (provider.id === id ? { ...provider, ...patch } : provider)));
  };

  const updateAgent = (key: string, patch: Partial<AgentConfig>) => {
    setAgents((current) => current.map((agent) => (agent.key === key ? { ...agent, ...patch } : agent)));
  };

  const handleSave = () => {
    setSavedAt(new Date().toLocaleString("vi-VN"));
  };

  return (
    <div className="space-y-6 p-6">
      <header className="flex flex-wrap items-start justify-between gap-4 rounded-lg border border-border bg-card p-4">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <Button variant="outline" size="sm" asChild>
              <Link href="/admin">← Admin</Link>
            </Button>
            <p className="text-xs uppercase tracking-wider text-muted-foreground">AI Governance</p>
          </div>
          <h1 className="text-2xl font-semibold">AI Agent Configuration Center</h1>
          <p className="text-sm text-muted-foreground">
            Refactor giao diện admin để quản lý gọn theo từng cụm: Provider, Agent Profile, và mapping file cấu hình.
          </p>
        </div>
        <div className="grid min-w-52 grid-cols-2 gap-2 text-xs">
          <div className="rounded border border-border p-2">
            <p className="text-muted-foreground">Provider bật</p>
            <p className="text-lg font-semibold">{configuredProviderCount}</p>
          </div>
          <div className="rounded border border-border p-2">
            <p className="text-muted-foreground">AI Profiles</p>
            <p className="text-lg font-semibold">{agents.length}</p>
          </div>
        </div>
      </header>

      <Card>
        <CardHeader>
          <CardTitle>1) AI Provider Configuration</CardTitle>
          <p className="text-sm text-muted-foreground">Bật/tắt provider, chỉnh model mặc định và endpoint theo môi trường.</p>
        </CardHeader>
        <CardContent className="space-y-4">
          {providers.map((provider) => (
            <article key={provider.id} className="space-y-3 rounded border border-border p-3">
              <div className="flex items-center justify-between gap-2">
                <div>
                  <p className="font-medium">{provider.name}</p>
                  <p className="text-xs text-muted-foreground">ID: {provider.id}</p>
                </div>
                <Button
                  variant={provider.enabled ? "default" : "outline"}
                  size="sm"
                  onClick={() => updateProvider(provider.id, { enabled: !provider.enabled })}
                >
                  {provider.enabled ? "Enabled" : "Disabled"}
                </Button>
              </div>
              <div className="grid gap-3 md:grid-cols-2">
                <Input
                  value={provider.model}
                  onChange={(event) => updateProvider(provider.id, { model: event.target.value })}
                  placeholder="Model"
                />
                <Input
                  value={provider.endpoint}
                  onChange={(event) => updateProvider(provider.id, { endpoint: event.target.value })}
                  placeholder="Endpoint"
                />
              </div>
            </article>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>2) AI Agent Profiles</CardTitle>
          <p className="text-sm text-muted-foreground">
            Quản lý thông số cho AI Character, AI Writer và các agent điều phối.
          </p>
        </CardHeader>
        <CardContent className="space-y-4">
          {agents.map((agent) => (
            <article key={agent.key} className="space-y-3 rounded border border-border p-3">
              <div>
                <p className="font-medium">{agent.title}</p>
                <p className="text-xs text-muted-foreground">{agent.description}</p>
              </div>
              <div className="grid gap-3 md:grid-cols-2">
                <Input
                  type="number"
                  step="0.05"
                  value={agent.temperature}
                  onChange={(event) => updateAgent(agent.key, { temperature: Number(event.target.value) })}
                  placeholder="Temperature"
                />
                <Input
                  type="number"
                  value={agent.maxTokens}
                  onChange={(event) => updateAgent(agent.key, { maxTokens: Number(event.target.value) })}
                  placeholder="Max tokens"
                />
                <Input
                  type="number"
                  step="0.05"
                  value={agent.topP}
                  onChange={(event) => updateAgent(agent.key, { topP: Number(event.target.value) })}
                  placeholder="Top P"
                />
                <Input
                  value={agent.promptPreset}
                  onChange={(event) => updateAgent(agent.key, { promptPreset: event.target.value })}
                  placeholder="Prompt preset"
                />
              </div>
            </article>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>3) Source Config Mapping</CardTitle>
          <p className="text-sm text-muted-foreground">
            Các file cấu hình backend liên quan để đội vận hành đối chiếu nhanh khi rollout.
          </p>
        </CardHeader>
        <CardContent>
          <ul className="grid gap-2 text-sm md:grid-cols-2">
            {SOURCE_CONFIG_FILES.map((file) => (
              <li key={file} className="rounded border border-border bg-muted/30 px-3 py-2 font-mono text-xs">
                {file}
              </li>
            ))}
          </ul>
        </CardContent>
      </Card>

      <footer className="flex flex-wrap items-center gap-3">
        <Button onClick={handleSave}>Lưu cấu hình AI</Button>
        <p className="text-xs text-muted-foreground">
          {savedAt ? `Đã lưu cấu hình cục bộ lúc ${savedAt}.` : "Chưa có thay đổi nào được lưu."}
        </p>
      </footer>
    </div>
  );
}
