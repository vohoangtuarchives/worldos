"use client";

import Link from "next/link";
import { useWorlds } from "./useWriterApi";

export function WorldList() {
  const { data: worlds, isLoading, error } = useWorlds();
  if (isLoading) return <p className="text-sm text-muted-foreground">Loading…</p>;
  if (error) return <p className="text-sm text-destructive">Failed to load worlds.</p>;
  if (!worlds?.length) return <p className="text-sm text-muted-foreground">No worlds yet. Use Genesis to create.</p>;
  return (
    <ul className="space-y-2">
      {worlds.map((w) => (
        <li key={w.id} className="flex items-center justify-between rounded-md border border-border px-3 py-2">
          <Link href={`/writer/worlds/${w.id}`} className="font-medium text-primary hover:underline">
            {w.name}
          </Link>
          <span className="text-xs text-muted-foreground">{w.status ?? w.health_status ?? "—"}</span>
        </li>
      ))}
    </ul>
  );
}
