"use client";

import { useState } from "react";
import {
  useWorld,
  useWorldSnapshots,
  useWorldSnapshotsCompare,
  useWorldSnapshotsCreate,
  useUniverseSnapshots,
} from "@/features/writer/useWriterApi";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import type { WorldSnapshotItem } from "@/shared/api/writer";
import type { UniverseSnapshotItem } from "@/shared/api/writer";

export function SnapshotView({ worldId }: { worldId: string }) {
  const { data: world } = useWorld(worldId);
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

  const runtimeInstances = world?.runtime_instances ?? [];
  const [selectedUniverseId, setSelectedUniverseId] = useState<string | "">("");
  const { data: universeSnapshots = [], isLoading: universeSnapLoading } = useUniverseSnapshots(
    selectedUniverseId || null
  );
  const [tickA, setTickA] = useState<number | "">("");
  const [tickB, setTickB] = useState<number | "">("");
  const snapA = universeSnapshots.find((s: UniverseSnapshotItem) => s.tick === tickA);
  const snapB = universeSnapshots.find((s: UniverseSnapshotItem) => s.tick === tickB);

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
      {runtimeInstances.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Snapshots theo Universe (v3)</CardTitle>
            <p className="text-sm text-muted-foreground">
              Chọn Universe, xem danh sách universe_snapshots theo tick. So sánh hai tick.
            </p>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex flex-wrap items-center gap-2">
              <label className="text-sm font-medium">Universe</label>
              <select
                className="rounded border border-input bg-background px-2 py-1.5 text-sm"
                value={selectedUniverseId}
                onChange={(e) => setSelectedUniverseId(e.target.value)}
              >
                <option value="">— Chọn —</option>
                {runtimeInstances.map((u) => (
                  <option key={u.id} value={u.id}>
                    {u.name} (age {u.age})
                  </option>
                ))}
              </select>
            </div>
            {selectedUniverseId && (
              <>
                {universeSnapLoading ? (
                  <p className="text-sm text-muted-foreground">Loading snapshots…</p>
                ) : universeSnapshots.length === 0 ? (
                  <p className="text-sm text-muted-foreground">Chưa có snapshot cho universe này.</p>
                ) : (
                  <>
                    <div className="overflow-x-auto">
                      <table className="w-full text-sm">
                        <thead>
                          <tr className="border-b border-border">
                            <th className="px-2 py-1 text-left font-medium">Tick</th>
                            <th className="px-2 py-1 text-right font-medium">Entropy</th>
                            <th className="px-2 py-1 text-right font-medium">Stability</th>
                          </tr>
                        </thead>
                        <tbody>
                          {(universeSnapshots as UniverseSnapshotItem[]).map((s) => (
                            <tr key={s.id} className="border-b border-border/50">
                              <td className="px-2 py-1 tabular-nums">{s.tick}</td>
                              <td className="px-2 py-1 text-right tabular-nums">
                                {s.entropy != null ? s.entropy.toFixed(3) : "—"}
                              </td>
                              <td className="px-2 py-1 text-right tabular-nums">
                                {s.stability_index != null ? s.stability_index.toFixed(3) : "—"}
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                    <div className="flex flex-wrap items-center gap-2 border-t pt-4">
                      <label className="text-sm text-muted-foreground">So sánh tick A</label>
                      <input
                        type="number"
                        className="w-20 rounded border border-input bg-background px-2 py-1 text-sm"
                        value={tickA === "" ? "" : tickA}
                        onChange={(e) => setTickA(e.target.value === "" ? "" : Number(e.target.value))}
                      />
                      <label className="text-sm text-muted-foreground">tick B</label>
                      <input
                        type="number"
                        className="w-20 rounded border border-input bg-background px-2 py-1 text-sm"
                        value={tickB === "" ? "" : tickB}
                        onChange={(e) => setTickB(e.target.value === "" ? "" : Number(e.target.value))}
                      />
                    </div>
                    {snapA && snapB && (
                      <dl className="grid gap-2 text-sm md:grid-cols-2">
                        <div>
                          <dt className="font-medium text-muted-foreground">Tick {snapA.tick}</dt>
                          <dd className="font-mono text-xs">
                            entropy={snapA.entropy?.toFixed(3)}, stability={snapA.stability_index?.toFixed(3)}
                          </dd>
                        </div>
                        <div>
                          <dt className="font-medium text-muted-foreground">Tick {snapB.tick}</dt>
                          <dd className="font-mono text-xs">
                            entropy={snapB.entropy?.toFixed(3)}, stability={snapB.stability_index?.toFixed(3)}
                          </dd>
                        </div>
                      </dl>
                    )}
                  </>
                )}
              </>
            )}
          </CardContent>
        </Card>
      )}

      <Card>
        <CardHeader>
          <CardTitle>Cosmic snapshots (legacy)</CardTitle>
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
