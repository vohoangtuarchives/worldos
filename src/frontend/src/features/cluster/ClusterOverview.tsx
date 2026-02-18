"use client";

import Link from "next/link";
import { useClusterSnapshot } from "./useClusterApi";
import { GovernorPanel } from "./GovernorPanel";
import { SystemPanel } from "./SystemPanel";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { ClusterWorldSnapshot } from "@/shared/api/cluster";

function WorldMatrix({ worlds }: { worlds: ClusterWorldSnapshot[] }) {
  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="border-b border-border">
            <th className="px-3 py-2 text-left font-medium">World</th>
            <th className="px-3 py-2 text-left font-medium">Status</th>
            <th className="px-3 py-2 text-right font-medium">Tick</th>
            <th className="px-3 py-2 text-right font-medium">Entropy</th>
            <th className="px-3 py-2 text-right font-medium">Stability</th>
          </tr>
        </thead>
        <tbody>
          {worlds.map((w) => (
            <tr key={w.id} className="border-b border-border/50 hover:bg-muted/50">
              <td className="px-3 py-2">
                <Link
                  href={`/world/${w.id}`}
                  className="font-medium text-primary hover:underline"
                >
                  {w.name || `World ${w.id}`}
                </Link>
              </td>
              <td className="px-3 py-2 text-muted-foreground">{w.status ?? "—"}</td>
              <td className="px-3 py-2 text-right tabular-nums">{w.current_tick}</td>
              <td className="px-3 py-2 text-right tabular-nums">
                {w.entropy != null ? w.entropy.toFixed(2) : "—"}
              </td>
              <td className="px-3 py-2 text-right tabular-nums">
                {w.stability != null ? w.stability.toFixed(2) : "—"}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export function ClusterOverview() {
  const { data, isLoading, error } = useClusterSnapshot();

  if (isLoading) {
    return (
      <div className="flex items-center justify-center gap-2 p-8 text-muted-foreground">
        <div className="h-5 w-5 animate-spin rounded-full border-2 border-muted border-t-primary" />
        <span>Loading cluster snapshot…</span>
      </div>
    );
  }

  if (error) {
    return (
      <Card>
        <CardContent className="p-6">
          <p className="text-destructive">
            Failed to load cluster: {(error as Error).message}
          </p>
        </CardContent>
      </Card>
    );
  }

  const { worlds = [], clusterStats = { total: 0, running: 0 } } = data ?? {};

  return (
    <div className="space-y-6">
      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-base">Worlds</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-semibold tabular-nums">{clusterStats.total}</p>
            <p className="text-xs text-muted-foreground">total</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-base">Running</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-semibold tabular-nums">{clusterStats.running}</p>
            <p className="text-xs text-muted-foreground">active</p>
          </CardContent>
        </Card>
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <GovernorPanel />
        <SystemPanel />
      </div>

      <Card>
        <CardHeader>
          <CardTitle>World Matrix</CardTitle>
          <p className="text-sm text-muted-foreground">
            Snapshot polled every 15s. Click world to open detail.
          </p>
        </CardHeader>
        <CardContent>
          {worlds.length === 0 ? (
            <p className="text-muted-foreground">No worlds yet.</p>
          ) : (
            <WorldMatrix worlds={worlds} />
          )}
        </CardContent>
      </Card>
    </div>
  );
}
