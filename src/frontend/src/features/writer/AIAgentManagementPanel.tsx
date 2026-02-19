"use client";

import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  useAIFeatureConfigs,
  useAIRequestLogDetail,
  useAIRequestLogFilters,
  useAIRequestLogs,
  useDeleteAIFeatureConfig,
  useUpsertAIFeatureConfig,
} from "./useWriterApi";

export function AIAgentManagementPanel() {
  const [featureKey, setFeatureKey] = useState("");
  const [agentName, setAgentName] = useState("");
  const [provider, setProvider] = useState("openai");
  const [model, setModel] = useState("gpt-4o-mini");
  const [temperature, setTemperature] = useState("0.7");

  const [filterFeature, setFilterFeature] = useState("");
  const [filterAgent, setFilterAgent] = useState("");
  const [filterStatus, setFilterStatus] = useState("");
  const [selectedLogId, setSelectedLogId] = useState<string | undefined>(undefined);

  const { data: configs } = useAIFeatureConfigs();
  const { data: filters } = useAIRequestLogFilters();
  const { data: logs } = useAIRequestLogs({
    feature_key: filterFeature || undefined,
    agent_name: filterAgent || undefined,
    status: filterStatus || undefined,
    per_page: 20,
  });
  const { data: logDetail } = useAIRequestLogDetail(selectedLogId);

  const upsert = useUpsertAIFeatureConfig();
  const removeConfig = useDeleteAIFeatureConfig();

  return (
    <div className="grid gap-4 lg:grid-cols-2">
      <Card>
        <CardHeader>
          <CardTitle>Feature Agent Config</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          <Input placeholder="feature_key (vd: narrative.dialogue)" value={featureKey} onChange={(e) => setFeatureKey(e.target.value)} />
          <Input placeholder="agent_name" value={agentName} onChange={(e) => setAgentName(e.target.value)} />
          <Input placeholder="provider" value={provider} onChange={(e) => setProvider(e.target.value)} />
          <Input placeholder="model" value={model} onChange={(e) => setModel(e.target.value)} />
          <Input placeholder="temperature" value={temperature} onChange={(e) => setTemperature(e.target.value)} />
          <Button
            onClick={() => upsert.mutate({
              feature_key: featureKey,
              agent_name: agentName,
              provider,
              model,
              temperature: Number(temperature),
              enabled: true,
            })}
            disabled={upsert.isPending || !featureKey || !agentName}
          >
            Save config
          </Button>

          <div className="space-y-2 pt-3">
            {(configs?.data ?? []).map((item) => (
              <div key={item.id} className="flex items-center justify-between rounded border p-2 text-sm">
                <div>
                  <div className="font-medium">{item.feature_key}</div>
                  <div className="text-muted-foreground">{item.agent_name} · {item.model ?? "default"}</div>
                </div>
                <Button variant="outline" size="sm" onClick={() => removeConfig.mutate(item.feature_key)}>
                  Delete
                </Button>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>AI Request / Response Logs</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          <div className="grid grid-cols-3 gap-2">
            <select className="rounded border p-2 text-sm" value={filterFeature} onChange={(e) => setFilterFeature(e.target.value)}>
              <option value="">All features</option>
              {(filters?.data?.feature_keys ?? []).map((f) => <option key={f} value={f}>{f}</option>)}
            </select>
            <select className="rounded border p-2 text-sm" value={filterAgent} onChange={(e) => setFilterAgent(e.target.value)}>
              <option value="">All agents</option>
              {(filters?.data?.agent_names ?? []).map((a) => <option key={a} value={a}>{a}</option>)}
            </select>
            <select className="rounded border p-2 text-sm" value={filterStatus} onChange={(e) => setFilterStatus(e.target.value)}>
              <option value="">All statuses</option>
              {(filters?.data?.statuses ?? []).map((s) => <option key={s} value={s}>{s}</option>)}
            </select>
          </div>

          <div className="max-h-64 space-y-2 overflow-auto rounded border p-2">
            {(logs?.data?.data ?? []).map((log) => (
              <button key={log.id} className="w-full rounded border p-2 text-left text-sm hover:bg-muted" onClick={() => setSelectedLogId(log.id)}>
                <div className="font-medium">{log.feature_key ?? "global.default"} · {log.agent_name ?? "Default Agent"}</div>
                <div className="text-xs text-muted-foreground">{log.status} · {log.model ?? "n/a"} · {log.created_at}</div>
              </button>
            ))}
          </div>

          {selectedLogId && (
            <div className="space-y-2 rounded border p-3 text-xs">
              <div><strong>System:</strong><pre className="overflow-auto whitespace-pre-wrap">{logDetail?.data?.system_prompt ?? ""}</pre></div>
              <div><strong>User Input:</strong><pre className="overflow-auto whitespace-pre-wrap">{logDetail?.data?.user_prompt ?? ""}</pre></div>
              <div><strong>Request:</strong><pre className="overflow-auto whitespace-pre-wrap">{logDetail?.data?.request_payload ?? ""}</pre></div>
              <div><strong>Response:</strong><pre className="overflow-auto whitespace-pre-wrap">{logDetail?.data?.response_payload ?? ""}</pre></div>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
