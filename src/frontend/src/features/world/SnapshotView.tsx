"use client";

import { useState } from "react";
import {
  useWorldSnapshots,
  useWorldSnapshotsCompare,
  useWorldSnapshotsCreate,
} from "@/features/writer/useWriterApi";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import type { WorldSnapshotItem } from "@/shared/api/writer";

export function SnapshotView({ worldId }: { worldId: string }) {
  const { data: snapshots = [], isLoading, error } = useWorldSnapshots(worldId);
  const createSnapshot = useWorldSnapshotsCreate(worldId);
  const [yearA, setYearA] = useState<number | "">("");
  const [yearB, setYearB] = useState<number | "">("");
  const compareEnabled = yearA !== "" && yearB !== "" && yearA !== yearB;
  const { data: compareData, isLoading: compareLoading } = useWorldSnapshotsCompare(
    worldId,
    typeof yearA === "number" ? yearA : 0,
    typeof yearB === "number" ? yearB : 0
  );

  if (isLoading) {
    return (
      <div className="flex items-center gap-2 p-6 text-muted-foreground">
        <div className="h-5 w-5 animate-spin rounded-full border-2 border-muted border-t-primary" />
        <span>Loading snapshots…</span>
      </div>
    );
  }

  if (error) {
    return (
      <div className="p-6">
        <p className="text-destructive">Failed to load snapshots.</p>
      </div>
    );
  }

  const a = compareData?.snapshot_a;
  const b = compareData?.snapshot_b;

  return (
    <div className="space-y-6 p-6">
      <Card>
        <CardHeader>
          <CardTitle>Snapshots</CardTitle>
          <p className="text-sm text-muted-foreground">
            Cosmic snapshots by year. Create (stub) or compare two.
          </p>
        </CardHeader>
        <CardContent className="space-y-4">
          <Button
            size="sm"
            disabled={createSnapshot.isPending}
            onClick={() => createSnapshot.mutate()}
          >
            {createSnapshot.isPending ? "…" : "Create snapshot (stub)"}
          </Button>
          {snapshots.length === 0 ? (
            <p className="text-sm text-muted-foreground">No snapshots yet.</p>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-border">
                    <th className="px-2 py-1 text-left font-medium">Year</th>
                    <th className="px-2 py-1 text-right font-medium">Entropy</th>
                    <th className="px-2 py-1 text-right font-medium">Stability</th>
                    <th className="px-2 py-1 text-right font-medium">Energy</th>
                  </tr>
                </thead>
                <tbody>
                  {(snapshots as WorldSnapshotItem[]).map((s) => (
                    <tr key={s.id} className="border-b border-border/50">
                      <td className="px-2 py-1 tabular-nums">{s.year}</td>
                      <td className="px-2 py-1 text-right tabular-nums">
                        {s.entropy != null ? s.entropy.toFixed(3) : "—"}
                      </td>
                      <td className="px-2 py-1 text-right tabular-nums">
                        {s.stability != null ? s.stability.toFixed(3) : "—"}
                      </td>
                      <td className="px-2 py-1 text-right tabular-nums">
                        {s.energy != null ? s.energy.toFixed(3) : "—"}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Compare</CardTitle>
          <p className="text-sm text-muted-foreground">
            Select two years to diff snapshot values.
          </p>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex flex-wrap items-center gap-2">
            <label className="text-sm text-muted-foreground">Year A</label>
            <input
              type="number"
              className="w-24 rounded border border-input bg-background px-2 py-1 text-sm"
              value={yearA === "" ? "" : yearA}
              onChange={(e) => setYearA(e.target.value === "" ? "" : Number(e.target.value))}
            />
            <label className="text-sm text-muted-foreground">Year B</label>
            <input
              type="number"
              className="w-24 rounded border border-input bg-background px-2 py-1 text-sm"
              value={yearB === "" ? "" : yearB}
              onChange={(e) => setYearB(e.target.value === "" ? "" : Number(e.target.value))}
            />
          </div>
          {compareEnabled && compareLoading && <p className="text-sm text-muted-foreground">Loading compare…</p>}
          {compareEnabled && !compareLoading && (a || b) && (
            <dl className="grid gap-2 text-sm md:grid-cols-2">
              <div>
                <dt className="font-medium text-muted-foreground">Snapshot A (year {a?.year ?? "—"})</dt>
                <dd className="font-mono text-xs">
                  {a ? `entropy=${a.entropy?.toFixed(3)}, stability=${a.stability?.toFixed(3)}, energy=${a.energy?.toFixed(3)}` : "—"}
                </dd>
              </div>
              <div>
                <dt className="font-medium text-muted-foreground">Snapshot B (year {b?.year ?? "—"})</dt>
                <dd className="font-mono text-xs">
                  {b ? `entropy=${b.entropy?.toFixed(3)}, stability=${b.stability?.toFixed(3)}, energy=${b.energy?.toFixed(3)}` : "—"}
                </dd>
              </div>
            </dl>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
