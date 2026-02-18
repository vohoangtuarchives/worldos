"use client";

import Link from "next/link";
import { useSaga, useSagaAdvance } from "./useWriterApi";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { SagaTreeView } from "./SagaTreeView";
import type { SagaDetail, SagaWorld } from "@/shared/api/writer";

function SagaWorldRow({ sw }: { sw: SagaWorld }) {
  return (
    <li className="flex flex-wrap items-center gap-2 rounded border border-border bg-card p-3 text-sm">
      <span className="font-medium">{sw.world_name}</span>
      <span className="text-muted-foreground">seq {sw.sequence}</span>
      {sw.universe_id && (
        <span className="font-mono text-xs text-muted-foreground" title={sw.universe_id}>
          {sw.universe_id.slice(0, 8)}…
        </span>
      )}
      {sw.universe_age != null && <span>age: {sw.universe_age}</span>}
      {sw.universe_status && (
        <span className="rounded bg-muted px-1.5 py-0.5">{sw.universe_status}</span>
      )}
      {sw.world_id && (
        <Button variant="link" size="sm" className="h-auto p-0 text-primary" asChild>
          <Link href={`/world/${sw.world_id}`}>World</Link>
        </Button>
      )}
    </li>
  );
}

export function SagaDetailView({
  sagaId,
  showCreatedMessage,
}: {
  sagaId: string;
  showCreatedMessage?: boolean;
}) {
  const { data: saga, isLoading, error } = useSaga(sagaId, {
    refetchInterval: 5000,
  });
  const advance = useSagaAdvance(sagaId);

  if (isLoading) return <p className="text-muted-foreground">Loading saga…</p>;
  if (error) return <p className="text-destructive">Failed to load saga.</p>;
  if (!saga) return null;

  const sagaWorlds = (saga as SagaDetail).saga_worlds ?? [];

  return (
    <div className="space-y-4">
      {showCreatedMessage && (
        <p className="rounded-md border border-green-500/30 bg-green-500/10 px-3 py-2 text-sm text-green-800 dark:text-green-200">
          Saga đã được tạo và đang chạy (10 ticks).
        </p>
      )}
      <Card>
        <CardHeader>
          <CardTitle>{saga.name}</CardTitle>
          <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
            <span>Status: {saga.status}</span>
            {saga.current_universe_id && (
              <span className="font-mono" title={saga.current_universe_id}>
                Universe: {saga.current_universe_id.slice(0, 8)}…
              </span>
            )}
          </div>
        </CardHeader>
      </Card>
      {sagaWorlds.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="text-base">Saga worlds</CardTitle>
          </CardHeader>
          <CardContent>
            <ul className="space-y-2">
              {sagaWorlds.map((sw) => (
                <SagaWorldRow key={sw.id} sw={sw} />
              ))}
            </ul>
          </CardContent>
        </Card>
      )}
      <SagaTreeView sagaId={sagaId} />
      <div className="flex flex-wrap gap-2">
        <Button
          size="sm"
          disabled={advance.isPending || saga.status === "completed"}
          onClick={() => advance.mutate(10)}
        >
          {advance.isPending ? "Advancing…" : "Advance (10 ticks)"}
        </Button>
      </div>
    </div>
  );
}
