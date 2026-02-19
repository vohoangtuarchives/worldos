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

const sourceConfigFiles = [
  "src/backend/config/ai.php",
  "src/backend/config/llm.php",
  "src/backend/config/worldos.php",
  "src/backend/config/saga.php",
  "src/backend/config/evolution.php",
];

const initialProviders: ProviderConfig[] = [
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

const initialAgents: AgentConfig[] = [
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

export function AIConfigCenter() {
  const [providers, setProviders] = useState(initialProviders);
  const [agents, setAgents] = useState(initialAgents);
  const [savedAt, setSavedAt] = useState<string | null>(null);

  const activeProviderCount = useMemo(() => providers.filter((p) => p.enabled).length, [providers]);

  const updateProvider = (id: string, patch: Partial<ProviderConfig>) => {
    setProviders((prev) => prev.map((p) => (p.id === id ? { ...p, ...patch } : p)));
  };

  const updateAgent = (key: string, patch: Partial<AgentConfig>) => {
    setAgents((prev) => prev.map((a) => (a.key === key ? { ...a, ...patch } : a)));
  };

  const saveConfig = () => {
    // Placeholder: backend API quản lý config chưa được expose.
    // Sau này có thể map sang /api/admin/ai/config.
    setSavedAt(new Date().toLocaleString("vi-VN"));
  };

  return (
    <div className="space-y-6 p-6">
      <div className="flex items-center gap-4">
        <Button variant="outline" size="sm" asChild>
          <Link href="/admin">← Admin</Link>
        </Button>
        <div>
          <h1 className="text-2xl font-semibold">AI Agent Configuration Center</h1>
          <p className="text-sm text-muted-foreground">
            Quản lý cấu hình AI Character, AI Writer, AI Provider và các cấu hình AI liên quan trong source code.
          </p>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Nguồn cấu hình trong source code</CardTitle>
          <p className="text-sm text-muted-foreground">
            Danh sách file backend cần đồng bộ khi nối API lưu cấu hình chính thức.
          </p>
        </CardHeader>
        <CardContent>
          <ul className="grid gap-2 text-sm">
            {sourceConfigFiles.map((file) => (
              <li key={file} className="rounded border border-border bg-muted/30 px-3 py-2 font-mono text-xs">
                {file}
              </li>
            ))}
          </ul>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>AI Provider</CardTitle>
          <p className="text-sm text-muted-foreground">Bật/tắt provider và model mặc định cho từng provider.</p>
        </CardHeader>
        <CardContent className="space-y-4">
          <p className="text-xs text-muted-foreground">Provider đang bật: {activeProviderCount}</p>
          {providers.map((provider) => (
            <div key={provider.id} className="space-y-3 rounded border border-border p-3">
              <div className="flex items-center justify-between">
                <p className="font-medium">{provider.name}</p>
                <label className="flex items-center gap-2 text-xs text-muted-foreground">
                  <input
                    type="checkbox"
                    checked={provider.enabled}
                    onChange={(event) => updateProvider(provider.id, { enabled: event.target.checked })}
                  />
                  Enabled
                </label>
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
            </div>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>AI Agent Profiles</CardTitle>
          <p className="text-sm text-muted-foreground">
            Cấu hình cho AI Character, AI Writer và agent điều phối narrative.
          </p>
        </CardHeader>
        <CardContent className="space-y-4">
          {agents.map((agent) => (
            <div key={agent.key} className="space-y-3 rounded border border-border p-3">
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
            </div>
          ))}
        </CardContent>
      </Card>

      <div className="flex items-center gap-3">
        <Button onClick={saveConfig}>Lưu cấu hình AI</Button>
        {savedAt && <p className="text-xs text-muted-foreground">Đã lưu cục bộ lúc: {savedAt}</p>}
      </div>
    </div>
  );
}
