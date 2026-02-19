"use client";

import Link from "next/link";
import { useAdminStats, useAdminUniverses, useAdminToggleLock } from "./useAdminApi";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

type UniverseItem = {
  id: number;
  name?: string;
  parameters?: {
    is_locked?: boolean;
  };
};

function StatItem({ label, value }: { label: string; value: number }) {
  return (
    <div className="rounded border border-border p-3">
      <p className="text-xs uppercase tracking-wider text-muted-foreground">{label}</p>
      <p className="text-xl font-semibold tabular-nums">{value}</p>
    </div>
  );
}

export function AdminDashboard() {
  const { data: stats, isLoading: statsLoading } = useAdminStats();
  const { data: universes, isLoading: universesLoading } = useAdminUniverses();
  const toggleLock = useAdminToggleLock();

  const list = (universes ?? []) as UniverseItem[];

  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Admin Control Center</CardTitle>
          <p className="text-sm text-muted-foreground">Điều hướng nhanh các module quản trị hệ thống.</p>
        </CardHeader>
        <CardContent className="flex flex-wrap gap-2">
          <Button variant="outline" asChild>
            <Link href="/admin/evolution">Evolution Lab</Link>
          </Button>
          <Button variant="outline" asChild>
            <Link href="/admin/ai">AI Config Center</Link>
          </Button>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Universe Stats</CardTitle>
        </CardHeader>
        <CardContent>
          {statsLoading && <p className="text-sm text-muted-foreground">Loading…</p>}
          {stats && (
            <div className="grid gap-3 md:grid-cols-3">
              <StatItem label="Total Universes" value={Number(stats.total_universes) || 0} />
              <StatItem label="Active" value={Number(stats.active_universes) || 0} />
              <StatItem label="Archived" value={Number(stats.archived_universes) || 0} />
            </div>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Universe Lock Manager</CardTitle>
        </CardHeader>
        <CardContent>
          {universesLoading && <p className="text-sm text-muted-foreground">Loading…</p>}
          {!universesLoading && list.length === 0 && <p className="text-sm text-muted-foreground">No universes.</p>}

          {list.length > 0 && (
            <ul className="space-y-2">
              {list.map((universe) => (
                <li key={universe.id} className="flex items-center justify-between rounded border border-border px-3 py-2">
                  <div>
                    <p className="font-medium">{universe.name ?? `Universe #${universe.id}`}</p>
                    <p className="text-xs text-muted-foreground">ID: {universe.id}</p>
                  </div>
                  <Button
                    size="sm"
                    variant={universe.parameters?.is_locked ? "default" : "outline"}
                    disabled={toggleLock.isPending}
                    onClick={() => toggleLock.mutate(universe.id)}
                  >
                    {universe.parameters?.is_locked ? "Unlock" : "Lock"}
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
