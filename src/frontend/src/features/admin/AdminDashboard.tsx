"use client";

import Link from "next/link";
import { useAdminStats, useAdminUniverses, useAdminToggleLock } from "./useAdminApi";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

export function AdminDashboard() {
  const { data: stats, isLoading: statsLoading } = useAdminStats();
  const { data: universes, isLoading: universesLoading } = useAdminUniverses();
  const toggleLock = useAdminToggleLock();
  return (
    <div className="space-y-6">
      <p className="text-sm text-muted-foreground">
        <Link href="/admin/evolution" className="underline hover:text-foreground">Evolution Lab</Link> (placeholder)
      </p>
      <Card>
        <CardHeader><CardTitle>Stats</CardTitle></CardHeader>
        <CardContent>
          {statsLoading && <p className="text-muted-foreground">Loading…</p>}
          {stats && (
            <dl className="grid gap-2 text-sm md:grid-cols-2">
              <dt className="text-muted-foreground">Total universes</dt>
              <dd>{Number(stats.total_universes) ?? 0}</dd>
              <dt className="text-muted-foreground">Active</dt>
              <dd>{Number(stats.active_universes) ?? 0}</dd>
              <dt className="text-muted-foreground">Archived</dt>
              <dd>{Number(stats.archived_universes) ?? 0}</dd>
            </dl>
          )}
        </CardContent>
      </Card>
      <Card>
        <CardHeader><CardTitle>Universes</CardTitle></CardHeader>
        <CardContent>
          {universesLoading && <p className="text-muted-foreground">Loading…</p>}
          {universes && universes.length === 0 && <p className="text-muted-foreground">No universes.</p>}
          {universes && universes.length > 0 && (
            <ul className="space-y-2">
              {(universes as { id: number; name?: string; parameters?: { is_locked?: boolean } }[]).map((u) => (
                <li key={u.id} className="flex items-center justify-between rounded border border-border px-3 py-2">
                  <span>{u.name ?? "Universe #" + u.id}</span>
                  <Button
                    size="sm"
                    variant="outline"
                    disabled={toggleLock.isPending}
                    onClick={() => toggleLock.mutate(u.id)}
                  >
                    {u.parameters?.is_locked ? "Unlock" : "Lock"}
                  </Button>
                </li>
              ))}
            </ul>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
