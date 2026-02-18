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
        <li key={s.id}>
          <Link href={"/writer/sagas/" + s.id} className="font-medium text-primary hover:underline">
            {s.name}
          </Link>
          <span className="ml-2 text-xs text-muted-foreground">{s.status}</span>
        </li>
      ))}
    </ul>
  );
}
