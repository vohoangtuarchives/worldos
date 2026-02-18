"use client";

import { useSagaTree } from "./useWriterApi";

export function SagaTreeView({ sagaId }: { sagaId: string }) {
  const { data, isLoading, error } = useSagaTree(sagaId);
  if (isLoading) return <p className="text-muted-foreground">Loading tree…</p>;
  if (error) return <p className="text-destructive">Failed to load tree.</p>;
  const nodes = data?.nodes ?? [];
  if (!nodes.length) return <p className="text-muted-foreground">No nodes in this saga.</p>;
  return (
    <div className="rounded-lg border border-border bg-card p-4">
      <p className="mb-2 text-sm font-medium">Tree ({nodes.length} nodes)</p>
      <ul className="list-inside list-disc space-y-1 text-sm">
        {nodes.map((n) => (
          <li key={n.id} className="flex flex-wrap items-center gap-2">
            <span>{n.name}</span>
            {n.current_era != null && <span className="text-muted-foreground">({n.current_era})</span>}
            {n.age != null && <span className="text-muted-foreground">age {n.age}</span>}
            <span>{n.status ?? "—"}</span>
            {n.universe_status && <span className="rounded bg-muted px-1 text-xs">{n.universe_status}</span>}
            {n.universe_id && (
              <span className="font-mono text-xs text-muted-foreground" title={n.universe_id}>
                {n.universe_id.slice(0, 8)}…
              </span>
            )}
          </li>
        ))}
      </ul>
    </div>
  );
}
