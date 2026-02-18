"use client";

import { useWorld, useWorldAction } from "./useWriterApi";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export function WorldHubView({ worldId }: { worldId: string }) {
  const { data: world, isLoading, error } = useWorld(worldId);
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
            {world.status ?? "—"} | Tick: {world.current_tick ?? 0}
          </p>
        </CardHeader>
        <CardContent className="flex flex-wrap gap-2">
          <Button size="sm" disabled={freeze.isPending} onClick={() => freeze.mutate(worldId)}>Freeze</Button>
          <Button size="sm" variant="outline" disabled={resume.isPending} onClick={() => resume.mutate(worldId)}>Resume</Button>
          <Button size="sm" variant="outline" disabled={step.isPending} onClick={() => step.mutate(worldId)}>Step</Button>
          <Button size="sm" variant="outline" disabled={rollback.isPending} onClick={() => rollback.mutate(worldId)}>Rollback</Button>
          <Button size="sm" variant="secondary" disabled={createInstance.isPending} onClick={() => createInstance.mutate(worldId)}>Create instance</Button>
        </CardContent>
      </Card>
      {world.runtime_instances && world.runtime_instances.length > 0 && (
        <Card>
          <CardHeader><CardTitle>Instances</CardTitle></CardHeader>
          <CardContent>
            <ul className="list-disc list-inside text-sm">
              {world.runtime_instances.map((u) => (
                <li key={u.id}>{u.name} {u.age != null ? `(age: ${u.age})` : ""}</li>
              ))}
            </ul>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
