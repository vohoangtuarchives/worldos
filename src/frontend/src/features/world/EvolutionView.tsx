"use client";

import { useWorldGodConsoleMetrics } from "@/features/writer/useWriterApi";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

const METRICS_POLL_MS = 4000;

export function EvolutionView({ worldId }: { worldId: string }) {
  const { data, isLoading, error } = useWorldGodConsoleMetrics(worldId, {
    refetchInterval: METRICS_POLL_MS,
  });

  if (isLoading) {
    return (
      <div className="flex items-center gap-2 p-6 text-muted-foreground">
        <div className="h-5 w-5 animate-spin rounded-full border-2 border-muted border-t-primary" />
        <span>Loading evolution metrics…</span>
      </div>
    );
  }

  if (error) {
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

  return (
    <div className="space-y-6 p-6">
      <Card>
        <CardHeader>
          <CardTitle>Evolution Live</CardTitle>
          <p className="text-sm text-muted-foreground">
            Metrics polled every {METRICS_POLL_MS / 1000}s. Tick and phase from world state kernel.
          </p>
        </CardHeader>
        <CardContent className="space-y-4">
          <dl className="grid gap-2 text-sm md:grid-cols-2">
            <dt className="text-muted-foreground">Cycle / Tick</dt>
            <dd className="font-mono tabular-nums">{tick}</dd>
            <dt className="text-muted-foreground">Phase</dt>
            <dd className="font-mono">{phase}</dd>
          </dl>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>State vector</CardTitle>
          <p className="text-sm text-muted-foreground">
            Current kernel state (entropy, stability, etc.)
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
