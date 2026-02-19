"use client";

import Link from "next/link";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useAdminEvolutionOverview, useAdminToggleAIEvolution } from "@/features/admin/useAdminApi";

function Metric({ label, value }: { label: string; value: string | number }) {
  return (
    <div className="rounded border border-border p-3">
      <p className="text-xs uppercase tracking-wider text-muted-foreground">{label}</p>
      <p className="text-2xl font-semibold tabular-nums">{value}</p>
    </div>
  );
}

export default function EvolutionLabPage() {
  const { data, isLoading } = useAdminEvolutionOverview({ refetchInterval: 10000 });
  const toggle = useAdminToggleAIEvolution();

  const overview = data?.data;

  return (
    <div className="space-y-6 p-6">
      <div className="mb-4 flex items-center gap-4">
        <Button variant="outline" size="sm" asChild>
          <Link href="/admin">← Admin</Link>
        </Button>
        <h1 className="text-2xl font-semibold">Evolution Lab</h1>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Evolution Overview</CardTitle>
          <p className="text-sm text-muted-foreground">Realtime dashboard cho generation throughput, collapse risk và frontier trạng thái universes.</p>
        </CardHeader>
        <CardContent>
          {isLoading && <p className="text-sm text-muted-foreground">Loading…</p>}
          {overview && (
            <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
              <Metric label="Generations / hour" value={overview.generations_per_hour} />
              <Metric label="Collapse rate" value={`${overview.collapse_rate_percent}%`} />
              <Metric label="Frontier size" value={overview.frontier_size} />
              <Metric label="AI state" value={overview.ai_enabled ? "ENABLED" : "DISABLED"} />
            </div>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Evolution Controls</CardTitle>
        </CardHeader>
        <CardContent className="flex items-center gap-3">
          <Button
            onClick={() => toggle.mutate(!(overview?.ai_enabled ?? false))}
            disabled={toggle.isPending || !overview}
            variant={(overview?.ai_enabled ?? false) ? "destructive" : "default"}
          >
            {(overview?.ai_enabled ?? false) ? "Disable AI Evolution" : "Enable AI Evolution"}
          </Button>
          <p className="text-xs text-muted-foreground">
            Last update: {overview?.updated_at ?? "n/a"}
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
