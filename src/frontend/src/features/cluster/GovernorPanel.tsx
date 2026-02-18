"use client";

import { useClusterGovernor, useClusterEmergencyFreeze } from "./useClusterApi";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

export function GovernorPanel() {
  const { data: governor, isLoading, error } = useClusterGovernor();
  const emergencyFreeze = useClusterEmergencyFreeze();

  if (isLoading) {
    return (
      <Card>
        <CardHeader className="pb-2">
          <CardTitle className="text-base">Governor</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">Loading…</p>
        </CardContent>
      </Card>
    );
  }

  if (error) {
    return (
      <Card>
        <CardHeader className="pb-2">
          <CardTitle className="text-base">Governor</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-destructive">Failed to load governor state.</p>
        </CardContent>
      </Card>
    );
  }

  const pressureScore = governor?.pressureScore ?? 0;
  const throttleLevel = governor?.throttleLevel ?? "normal";
  const emergencyMode = governor?.emergencyMode ?? false;
  const costBurnRate = governor?.costBurnRate;

  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-base">Governor</CardTitle>
        <p className="text-xs text-muted-foreground">
          Pressure and throttle. Stub until Governor service is wired.
        </p>
      </CardHeader>
      <CardContent className="space-y-3">
        <dl className="grid gap-1 text-sm">
          <dt className="text-muted-foreground">Pressure score</dt>
          <dd className="font-mono tabular-nums">{pressureScore.toFixed(2)}</dd>
          <dt className="text-muted-foreground">Throttle level</dt>
          <dd className="font-mono">{throttleLevel}</dd>
          <dt className="text-muted-foreground">Emergency mode</dt>
          <dd className="font-mono">{emergencyMode ? "Yes" : "No"}</dd>
          {costBurnRate != null && (
            <>
              <dt className="text-muted-foreground">Cost burn rate</dt>
              <dd className="font-mono tabular-nums">{costBurnRate}</dd>
            </>
          )}
        </dl>
        <Button
          variant="destructive"
          size="sm"
          disabled={emergencyFreeze.isPending}
          onClick={() => emergencyFreeze.mutate()}
        >
          {emergencyFreeze.isPending ? "…" : "Emergency freeze"}
        </Button>
      </CardContent>
    </Card>
  );
}
