"use client";

import { useClusterSystem } from "./useClusterApi";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export function SystemPanel() {
  const { data: system, isLoading, error } = useClusterSystem();

  if (isLoading) {
    return (
      <Card>
        <CardHeader className="pb-2">
          <CardTitle className="text-base">System</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">Loading…</p>
        </CardContent>
      </Card>
    );
  }

  if (error) {
    return (
      <Card>
        <CardHeader className="pb-2">
          <CardTitle className="text-base">System</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-destructive">Failed to load system metrics.</p>
        </CardContent>
      </Card>
    );
  }

  const cpu = system?.cpuPercent;
  const memory = system?.memoryPercent;
  const queue = system?.queueLength;

  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-base">System</CardTitle>
        <p className="text-xs text-muted-foreground">
          CPU, memory, queue. Stub until metrics collector is wired.
        </p>
      </CardHeader>
      <CardContent>
        <dl className="grid gap-1 text-sm">
          <dt className="text-muted-foreground">CPU %</dt>
          <dd className="font-mono tabular-nums">{cpu != null ? `${cpu}%` : "—"}</dd>
          <dt className="text-muted-foreground">Memory %</dt>
          <dd className="font-mono tabular-nums">{memory != null ? `${memory}%` : "—"}</dd>
          <dt className="text-muted-foreground">Queue length</dt>
          <dd className="font-mono tabular-nums">{queue != null ? queue : "—"}</dd>
        </dl>
      </CardContent>
    </Card>
  );
}
