"use client";

import Link from "next/link";
import { useWorld, useWorldAction } from "./useWriterApi";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export function WorldHubView({ worldId, refetchInterval }: { worldId: string; refetchInterval?: number }) {
  const { data: world, isLoading, error } = useWorld(worldId, { refetchInterval });
  const freeze = useWorldAction("freeze");
  const resume = useWorldAction("resume");
  const step = useWorldAction("step");
  const rollback = useWorldAction("rollback");
  const createInstance = useWorldAction("createInstance");

  if (isLoading) return <p className="text-muted-foreground">Loading…</p>;
  if (error) return <p className="text-destructive">Failed to load world.</p>;
  if (!world) return null;

  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>{world.name}</CardTitle>
          <p className="text-sm text-muted-foreground">
            World (rule set) — {world.status ?? "—"} | Legacy tick: {world.current_tick ?? 0}. Runtime tick = mỗi Universe (age).
          </p>
        </CardHeader>
        <CardContent className="flex flex-wrap gap-2">
          <Button size="sm" disabled={freeze.isPending} onClick={() => freeze.mutate(worldId)}>Freeze</Button>
          <Button size="sm" variant="outline" disabled={resume.isPending} onClick={() => resume.mutate(worldId)}>Resume</Button>
          <Button size="sm" variant="outline" disabled={step.isPending} onClick={() => step.mutate(worldId)} title="Legacy (World-level)">Step</Button>
          <Button size="sm" variant="outline" disabled={rollback.isPending} onClick={() => rollback.mutate(worldId)} title="Legacy (World-level)">Rollback</Button>
          <Button size="sm" variant="secondary" disabled={createInstance.isPending} onClick={() => createInstance.mutate(worldId)}>Create instance</Button>
        </CardContent>
      </Card>
      {world.runtime_instances && world.runtime_instances.length > 0 && (
        <Card>
          <CardHeader><CardTitle>Runtime instances (Universes)</CardTitle></CardHeader>
          <CardContent>
            <ul className="space-y-2">
              {world.runtime_instances.map((u) => (
                <li key={u.id} className="flex flex-wrap items-center gap-2 rounded border border-border bg-muted/30 p-2 text-sm">
                  <span className="font-medium">{u.name}</span>
                  <span className="text-muted-foreground">age: {u.age ?? 0}</span>
                  {u.entropy != null && <span>entropy: {u.entropy.toFixed(3)}</span>}
                  {u.stability_index != null && <span>stability: {u.stability_index.toFixed(3)}</span>}
                  {u.status && <span className="rounded bg-muted px-1.5 py-0.5">{u.status}</span>}
                  <Link
                    href={`/world/${worldId}?universe=${u.id}`}
                    className="text-primary hover:underline"
                  >
                    View
                  </Link>
                </li>
              ))}
            </ul>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
