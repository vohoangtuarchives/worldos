"use client";

import { useState, useEffect } from "react";
import { useSearchParams } from "next/navigation";
import {
  useWorld,
  useWorldGodConsoleMetrics,
  useUniverseMetrics,
} from "@/features/writer/useWriterApi";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

const METRICS_POLL_MS = 4000;

export function EvolutionView({ worldId }: { worldId: string }) {
  const searchParams = useSearchParams();
  const universeFromUrl = searchParams.get("universe") ?? "";
  const [selectedUniverseId, setSelectedUniverseId] = useState<string>("");

  const { data: world } = useWorld(worldId);
  const runtimeInstances = world?.runtime_instances ?? [];

  useEffect(() => {
    if (universeFromUrl && runtimeInstances.some((u) => u.id === universeFromUrl)) {
      setSelectedUniverseId(universeFromUrl);
    } else if (runtimeInstances.length > 0 && !selectedUniverseId) {
      setSelectedUniverseId(runtimeInstances[0].id);
    }
  }, [universeFromUrl, runtimeInstances, selectedUniverseId]);

  const useUniverse = selectedUniverseId.length > 0;
  const { data: universeData, isLoading: universeLoading, error: universeError } = useUniverseMetrics(
    useUniverse ? selectedUniverseId : null,
    { refetchInterval: METRICS_POLL_MS }
  );
  const { data: worldData, isLoading: worldLoading, error: worldError } = useWorldGodConsoleMetrics(
    useUniverse ? null : worldId,
    { refetchInterval: METRICS_POLL_MS }
  );

  const data = useUniverse ? universeData : worldData;
  const isLoading = useUniverse ? universeLoading : worldLoading;
  const error = useUniverse ? universeError : worldError;

  if (isLoading && !data) {
    return (
      <div className="flex items-center gap-2 p-6 text-muted-foreground">
        <div className="h-5 w-5 animate-spin rounded-full border-2 border-muted border-t-primary" />
        <span>Loading evolution metrics…</span>
      </div>
    );
  }

  if (error && !data) {
    return (
      <div className="p-6">
        <p className="text-destructive">
          Failed to load metrics: {(error as Error).message}
        </p>
      </div>
    );
  }

  const tick = data?.tick ?? 0;
  const phase = data?.phase ?? "unknown";
  const vector = data?.state_vector ?? {};
  const entropy = "entropy" in (data ?? {}) && data?.entropy != null ? data.entropy : (vector as Record<string, number>)?.entropy;
  const stability = "stability_index" in (data ?? {}) && data?.stability_index != null ? data.stability_index : (vector as Record<string, number>)?.stability_index;

  return (
    <div className="space-y-6 p-6">
      {runtimeInstances.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="text-base">Chọn Universe (v3)</CardTitle>
            <p className="text-sm text-muted-foreground">
              Metrics theo Universe. Tick = age của Universe.
            </p>
          </CardHeader>
          <CardContent>
            <select
              className="rounded border border-input bg-background px-2 py-1.5 text-sm"
              value={selectedUniverseId}
              onChange={(e) => setSelectedUniverseId(e.target.value)}
            >
              <option value="">— World (legacy god-console) —</option>
              {runtimeInstances.map((u) => (
                <option key={u.id} value={u.id}>
                  {u.name} (age {u.age})
                </option>
              ))}
            </select>
          </CardContent>
        </Card>
      )}

      <Card>
        <CardHeader>
          <CardTitle>Evolution Live</CardTitle>
          <p className="text-sm text-muted-foreground">
            Metrics polled every {METRICS_POLL_MS / 1000}s. {useUniverse ? "Universe (v3)." : "World kernel (legacy)."}
          </p>
        </CardHeader>
        <CardContent className="space-y-4">
          <dl className="grid gap-2 text-sm md:grid-cols-2">
            <dt className="text-muted-foreground">Cycle / Tick (age)</dt>
            <dd className="font-mono tabular-nums">{tick}</dd>
            <dt className="text-muted-foreground">Phase</dt>
            <dd className="font-mono">{phase}</dd>
            {entropy != null && (
              <>
                <dt className="text-muted-foreground">Entropy</dt>
                <dd className="font-mono tabular-nums">{Number(entropy).toFixed(3)}</dd>
              </>
            )}
            {stability != null && (
              <>
                <dt className="text-muted-foreground">Stability</dt>
                <dd className="font-mono tabular-nums">{Number(stability).toFixed(3)}</dd>
              </>
            )}
          </dl>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>State vector</CardTitle>
          <p className="text-sm text-muted-foreground">
            Current state (entropy, stability, etc.)
          </p>
        </CardHeader>
        <CardContent>
          {Object.keys(vector).length === 0 ? (
            <p className="text-muted-foreground">No state vector yet.</p>
          ) : (
            <ul className="space-y-1 font-mono text-sm">
              {Object.entries(vector).map(([k, v]) => (
                <li key={k} className="flex justify-between gap-4">
                  <span className="text-muted-foreground">{k}</span>
                  <span className="tabular-nums">
                    {typeof v === "number" ? v.toFixed(3) : String(v)}
                  </span>
                </li>
              ))}
            </ul>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
