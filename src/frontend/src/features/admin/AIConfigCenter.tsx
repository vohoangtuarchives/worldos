"use client";

import Link from "next/link";
import { useMemo, useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { useAdminEvolutionOverview, useAdminToggleAIEvolution } from "./useAdminApi";
import { useAIFeatureConfigs, useAIRequestLogs, useUpsertAIFeatureConfig } from "@/features/writer/useWriterApi";

type EditableFeatureConfig = {
  feature_key: string;
  agent_name: string;
  provider: string;
  model: string;
  temperature: number;
  enabled: boolean;
};

const SOURCE_CONFIG_FILES = [
  "src/backend/config/ai.php",
  "src/backend/config/llm.php",
  "src/backend/config/worldos.php",
  "src/backend/config/saga.php",
  "src/backend/config/evolution.php",
];

function toEditable(item: {
  feature_key: string;
  agent_name: string;
  provider: string;
  model: string | null;
  options?: { temperature?: number };
  enabled: boolean;
}): EditableFeatureConfig {
  return {
    feature_key: item.feature_key,
    agent_name: item.agent_name,
    provider: item.provider,
    model: item.model ?? "",
    temperature: item.options?.temperature ?? 0.7,
    enabled: item.enabled,
  };
}

export function AIConfigCenter() {
  const { data: evolutionOverview } = useAdminEvolutionOverview({ refetchInterval: 8000 });
  const toggleEvolution = useAdminToggleAIEvolution();
  const { data: featureConfigs, isLoading: featureLoading } = useAIFeatureConfigs({ refetchInterval: 8000 });
  const upsertFeatureConfig = useUpsertAIFeatureConfig();
  const { data: requestLogs } = useAIRequestLogs({ per_page: 10, page: 1 });

  const [drafts, setDrafts] = useState<Record<string, EditableFeatureConfig>>({});

  const liveConfigs = useMemo(() => featureConfigs?.data ?? [], [featureConfigs]);
  const configByKey = useMemo(
    () => new Map(liveConfigs.map((item) => [item.feature_key, item] as const)),
    [liveConfigs]
  );

  const providerStats = useMemo(() => {
    const stats = new Map<string, { provider: string; featureCount: number; enabledCount: number; models: Set<string> }>();
    for (const cfg of liveConfigs) {
      const current = stats.get(cfg.provider) ?? {
        provider: cfg.provider,
        featureCount: 0,
        enabledCount: 0,
        models: new Set<string>(),
      };
      current.featureCount += 1;
      if (cfg.enabled) current.enabledCount += 1;
      if (cfg.model) current.models.add(cfg.model);
      stats.set(cfg.provider, current);
    }
    return Array.from(stats.values());
  }, [liveConfigs]);

  const overviewData = evolutionOverview?.data;

  const readDraft = (featureKey: string) => {
    const cachedDraft = drafts[featureKey];
    if (cachedDraft) return cachedDraft;

    const source = configByKey.get(featureKey);
    if (!source) {
      return {
        feature_key: featureKey,
        agent_name: "",
        provider: "",
        model: "",
        temperature: 0.7,
        enabled: false,
      } satisfies EditableFeatureConfig;
    }

    return toEditable(source);
  };

  const updateDraft = (featureKey: string, patch: Partial<EditableFeatureConfig>) => {
    const base = readDraft(featureKey);
    setDrafts((current) => ({
      ...current,
      [featureKey]: { ...base, ...patch },
    }));
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
          <p className="text-sm text-muted-foreground">Đối chiếu đúng kiến trúc: AI là lớp đánh giá/ghi nhận, dữ liệu frontend lấy trực tiếp từ backend runtime + feature config.</p>
        </div>
        <div className="grid min-w-52 grid-cols-2 gap-2 text-xs">
          <div className="rounded border border-border p-2">
            <p className="text-muted-foreground">Providers thực tế</p>
            <p className="text-lg font-semibold">{providerStats.length}</p>
          </div>
          <div className="rounded border border-border p-2">
            <p className="text-muted-foreground">Feature configs</p>
            <p className="text-lg font-semibold">{liveConfigs.length}</p>
          </div>
        </div>
      </header>

      <Card>
        <CardHeader>
          <CardTitle>0) Runtime Monitoring (Live)</CardTitle>
          <p className="text-sm text-muted-foreground">Từ phản hồi vận hành: cần nhìn thấy AI/simulation có thực sự chạy hay không theo thời gian thực.</p>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid gap-3 md:grid-cols-4">
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">AI Evolution</p>
              <p className="text-lg font-semibold">{overviewData?.ai_enabled ? "Enabled" : "Disabled"}</p>
            </div>
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">Generations/h</p>
              <p className="text-lg font-semibold">{overviewData?.generations_per_hour ?? "—"}</p>
            </div>
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">Collapse rate</p>
              <p className="text-lg font-semibold">{overviewData?.collapse_rate_percent != null ? `${overviewData.collapse_rate_percent}%` : "—"}</p>
            </div>
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">Frontier size</p>
              <p className="text-lg font-semibold">{overviewData?.frontier_size ?? "—"}</p>
            </div>
          </div>
          <div className="flex flex-wrap items-center gap-3">
            <Button
              variant={overviewData?.ai_enabled ? "destructive" : "default"}
              disabled={toggleEvolution.isPending}
              onClick={() => toggleEvolution.mutate(!(overviewData?.ai_enabled ?? false))}
            >
              {toggleEvolution.isPending ? "Đang cập nhật..." : overviewData?.ai_enabled ? "Tắt AI Evolution" : "Bật AI Evolution"}
            </Button>
            <p className="text-xs text-muted-foreground">Cập nhật gần nhất: {overviewData?.updated_at ?? "chưa có dữ liệu runtime"}</p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>0) Runtime Monitoring (Live)</CardTitle>
          <p className="text-sm text-muted-foreground">Từ phản hồi vận hành: cần nhìn thấy AI/simulation có thực sự chạy hay không theo thời gian thực.</p>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid gap-3 md:grid-cols-4">
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">AI Evolution</p>
              <p className="text-lg font-semibold">{overviewData?.ai_enabled ? "Enabled" : "Disabled"}</p>
            </div>
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">Generations/h</p>
              <p className="text-lg font-semibold">{overviewData?.generations_per_hour ?? "—"}</p>
            </div>
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">Collapse rate</p>
              <p className="text-lg font-semibold">{overviewData?.collapse_rate_percent != null ? `${overviewData.collapse_rate_percent}%` : "—"}</p>
            </div>
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">Frontier size</p>
              <p className="text-lg font-semibold">{overviewData?.frontier_size ?? "—"}</p>
            </div>
          </div>
          <div className="flex flex-wrap items-center gap-3">
            <Button
              variant={overviewData?.ai_enabled ? "destructive" : "default"}
              disabled={toggleEvolution.isPending}
              onClick={() => toggleEvolution.mutate(!(overviewData?.ai_enabled ?? false))}
            >
              {toggleEvolution.isPending ? "Đang cập nhật..." : overviewData?.ai_enabled ? "Tắt AI Evolution" : "Bật AI Evolution"}
            </Button>
            <p className="text-xs text-muted-foreground">Cập nhật gần nhất: {overviewData?.updated_at ?? "chưa có dữ liệu runtime"}</p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>0) Runtime Monitoring (Live)</CardTitle>
          <p className="text-sm text-muted-foreground">Theo dõi evolution engine theo thời gian thực và bật/tắt AI enrichment.</p>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid gap-3 md:grid-cols-4">
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">AI Evolution</p>
              <p className="text-lg font-semibold">{overviewData?.ai_enabled ? "Enabled" : "Disabled"}</p>
            </div>
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">Generations/h</p>
              <p className="text-lg font-semibold">{overviewData?.generations_per_hour ?? "—"}</p>
            </div>
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">Collapse rate</p>
              <p className="text-lg font-semibold">{overviewData?.collapse_rate_percent != null ? `${overviewData.collapse_rate_percent}%` : "—"}</p>
            </div>
            <div className="rounded border border-border p-3">
              <p className="text-xs text-muted-foreground">Frontier size</p>
              <p className="text-lg font-semibold">{overviewData?.frontier_size ?? "—"}</p>
            </div>
          </div>
          <div className="flex flex-wrap items-center gap-3">
            <Button
              variant={overviewData?.ai_enabled ? "destructive" : "default"}
              disabled={toggleEvolution.isPending}
              onClick={() => toggleEvolution.mutate(!(overviewData?.ai_enabled ?? false))}
            >
              {toggleEvolution.isPending ? "Đang cập nhật..." : overviewData?.ai_enabled ? "Tắt AI Evolution" : "Bật AI Evolution"}
            </Button>
            <p className="text-xs text-muted-foreground">Cập nhật gần nhất: {overviewData?.updated_at ?? "chưa có dữ liệu runtime"}</p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>1) AI Provider Matrix (Real Data)</CardTitle>
          <p className="text-sm text-muted-foreground">Tổng hợp theo provider từ feature configs đang có trong DB.</p>
        </CardHeader>
        <CardContent className="space-y-4">
          {providerStats.length === 0 && <p className="text-sm text-muted-foreground">Không có provider nào từ backend.</p>}
          {providerStats.map((provider) => (
            <article key={provider.provider} className="space-y-2 rounded border border-border p-3">
              <div className="flex items-center justify-between">
                <p className="font-medium uppercase">{provider.provider}</p>
                <span className="text-xs text-muted-foreground">
                  Enabled {provider.enabledCount}/{provider.featureCount}
                </span>
              </div>
              <p className="text-xs text-muted-foreground">Models: {Array.from(provider.models).join(", ") || "(model quản lý ở feature config)"}</p>
            </article>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>2) AI Feature Profiles (Live + Save API)</CardTitle>
          <p className="text-sm text-muted-foreground">Chỉnh trực tiếp theo `feature_key`, lưu bằng API writer AI governance.</p>
        </CardHeader>
        <CardContent className="space-y-4">
          {featureLoading && <p className="text-sm text-muted-foreground">Đang tải feature configs...</p>}
          {!featureLoading && liveConfigs.length === 0 && <p className="text-sm text-muted-foreground">Hiện chưa có feature config nào.</p>}
          {liveConfigs.map((config) => {
            const draft = readDraft(config.feature_key);
            return (
              <article key={config.id} className="space-y-3 rounded border border-border p-3">
                <div className="flex items-center justify-between gap-2">
                  <div>
                    <p className="font-medium">{config.feature_key}</p>
                    <p className="text-xs text-muted-foreground">Updated: {config.updated_at ?? "—"}</p>
                  </div>
                  <Button
                    variant={draft.enabled ? "default" : "outline"}
                    size="sm"
                    onClick={() => updateDraft(config.feature_key, { enabled: !draft.enabled })}
                  >
                    {draft.enabled ? "Enabled" : "Disabled"}
                  </Button>
                </div>
                <div className="grid gap-3 md:grid-cols-2">
                  <Input value={draft.agent_name} onChange={(event) => updateDraft(config.feature_key, { agent_name: event.target.value })} placeholder="Agent name" />
                  <Input value={draft.provider} onChange={(event) => updateDraft(config.feature_key, { provider: event.target.value })} placeholder="Provider" />
                  <Input value={draft.model} onChange={(event) => updateDraft(config.feature_key, { model: event.target.value })} placeholder="Model" />
                  <Input
                    type="number"
                    step="0.05"
                    value={draft.temperature}
                    onChange={(event) => updateDraft(config.feature_key, { temperature: Number(event.target.value) })}
                    placeholder="Temperature"
                  />
                </div>
                <div className="flex items-center gap-3">
                  <Button
                    onClick={() =>
                      upsertFeatureConfig.mutate({
                        feature_key: draft.feature_key,
                        agent_name: draft.agent_name,
                        provider: draft.provider,
                        model: draft.model || undefined,
                        temperature: draft.temperature,
                        enabled: draft.enabled,
                      })
                    }
                    disabled={upsertFeatureConfig.isPending || !draft.feature_key || !draft.agent_name || !draft.provider}
                  >
                    {upsertFeatureConfig.isPending ? "Saving..." : "Lưu cấu hình"}
                  </Button>
                </div>
              </article>
            );
          })}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>3) Recent AI Request Logs</CardTitle>
          <p className="text-sm text-muted-foreground">Feed request gần nhất từ backend để kiểm tra AI hoạt động thật.</p>
        </CardHeader>
        <CardContent className="space-y-2">
          {(requestLogs?.data?.data ?? []).length === 0 && <p className="text-sm text-muted-foreground">Chưa có request log.</p>}
          {(requestLogs?.data?.data ?? []).map((log) => (
            <div key={log.id} className="flex items-center justify-between rounded border border-border p-2 text-xs">
              <div>
                <p className="font-medium">{log.feature_key ?? "global.default"}</p>
                <p className="text-muted-foreground">{log.agent_name ?? "default-agent"} • {log.provider} • {log.model ?? "n/a"}</p>
              </div>
              <div className="flex items-center gap-2">
                <Badge variant="outline">{log.status}</Badge>
                <span className="font-mono text-muted-foreground">{log.duration_ms ?? 0}ms</span>
              </div>
            </div>
          ))}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>4) Source Config Mapping</CardTitle>
          <p className="text-sm text-muted-foreground">Các file backend để đối chiếu khi rollout.</p>
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
    </div>
  );
}
