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
          <li key={n.id}>
            {n.name} {n.current_era && `(${n.current_era})`} — {n.status ?? "—"}
          </li>
        ))}
      </ul>
    </div>
  );
}
