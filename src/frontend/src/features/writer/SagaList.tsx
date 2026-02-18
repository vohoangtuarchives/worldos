"use client";

import Link from "next/link";
import { useSagas } from "./useWriterApi";

export function SagaList() {
  const { data: sagas, isLoading, error } = useSagas();
  if (isLoading) return <p className="text-sm text-muted-foreground">Loading…</p>;
  if (error) return <p className="text-sm text-destructive">Failed to load sagas.</p>;
  if (!sagas?.length) return <p className="text-sm text-muted-foreground">No sagas yet.</p>;
  return (
    <ul className="space-y-2">
      {sagas.map((s) => (
        <li key={s.id} className="flex flex-wrap items-center gap-2">
          <Link href={"/writer/sagas/" + s.id} className="font-medium text-primary hover:underline">
            {s.name}
          </Link>
          <span className="text-xs text-muted-foreground">{s.status}</span>
          {s.saga_worlds_count != null && (
            <span className="text-xs text-muted-foreground">({s.saga_worlds_count} world{s.saga_worlds_count !== 1 ? "s" : ""})</span>
          )}
          {s.current_universe_id && (
            <span className="rounded bg-muted px-1.5 py-0.5 font-mono text-xs" title={s.current_universe_id}>
              {s.current_universe_id.slice(0, 8)}…
            </span>
          )}
        </li>
      ))}
    </ul>
  );
}
