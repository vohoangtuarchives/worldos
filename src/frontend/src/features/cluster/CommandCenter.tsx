"use client";

import React, { useMemo } from "react";
import Link from "next/link";
import {
  AlertTriangle,
  Pause,
  Server,
  ShieldAlert,
  Activity,
  Gauge,
  Cpu,
  Clock3,
  ArrowUpRight,
} from "lucide-react";
import {
  useClusterSnapshot,
  useClusterGovernor,
  useClusterSystem,
  useClusterEmergencyFreeze,
} from "./useClusterApi";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

type RuntimeMetrics = {
  avgEntropy: number | null;
  avgStability: number | null;
  totalTick: number;
};

function formatMetric(value: number | null, digits = 2) {
  return value == null ? "—" : value.toFixed(digits);
}

export function CommandCenter() {
  const { data: snapshot } = useClusterSnapshot();
  const { data: governor } = useClusterGovernor();
  const { data: system } = useClusterSystem();
  const emergencyFreeze = useClusterEmergencyFreeze();

  const { worlds = [], clusterStats = { total: 0, running: 0 } } = snapshot ?? {};

  const runtimeMetrics = useMemo<RuntimeMetrics>(() => {
    const withEntropy = worlds.filter((w) => typeof w.entropy === "number");
    const withStability = worlds.filter((w) => typeof w.stability === "number");

    const avgEntropy =
      withEntropy.length > 0
        ? withEntropy.reduce((acc, w) => acc + (w.entropy ?? 0), 0) / withEntropy.length
        : null;

    const avgStability =
      withStability.length > 0
        ? withStability.reduce((acc, w) => acc + (w.stability ?? 0), 0) / withStability.length
        : null;

    const totalTick = worlds.reduce((acc, w) => acc + (w.current_tick ?? 0), 0);

    return { avgEntropy, avgStability, totalTick };
  }, [worlds]);

  const topRiskWorlds = useMemo(() => {
    return [...worlds]
      .sort((a, b) => (b.entropy ?? -1) - (a.entropy ?? -1))
      .slice(0, 8);
  }, [worlds]);

  return (
    <div className="space-y-6 animate-in fade-in duration-500">
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div className="glass-card p-5 border-l-4 border-l-primary">
          <div className="mb-3 flex items-center justify-between">
            <Server className="h-5 w-5 text-primary" />
            <Badge variant="outline">Runtime</Badge>
          </div>
          <p className="metric-label">Running Worlds</p>
          <h2 className="metric-value text-3xl font-black tracking-tight">
            {clusterStats.running}
            <span className="ml-1 text-base text-muted-foreground">/ {clusterStats.total}</span>
          </h2>
        </div>

        <div className="glass-card p-5 border-l-4 border-l-warning">
          <div className="mb-3 flex items-center justify-between">
            <Gauge className="h-5 w-5 text-warning" />
            <Badge variant="outline">Snapshot</Badge>
          </div>
          <p className="metric-label">Average Entropy</p>
          <h2 className="metric-value text-3xl font-black tracking-tight">{formatMetric(runtimeMetrics.avgEntropy)}</h2>
        </div>

        <div className="glass-card p-5 border-l-4 border-l-success">
          <div className="mb-3 flex items-center justify-between">
            <Activity className="h-5 w-5 text-success" />
            <Badge variant="outline">Snapshot</Badge>
          </div>
          <p className="metric-label">Average Stability</p>
          <h2 className="metric-value text-3xl font-black tracking-tight">{formatMetric(runtimeMetrics.avgStability)}</h2>
        </div>

        <div className="glass-card p-5 border-l-4 border-l-slate-400">
          <div className="mb-3 flex items-center justify-between">
            <Clock3 className="h-5 w-5 text-slate-500" />
            <Badge variant="outline">Aggregate</Badge>
          </div>
          <p className="metric-label">Total Tick</p>
          <h2 className="metric-value text-3xl font-black tracking-tight">{runtimeMetrics.totalTick}</h2>
        </div>
      </div>

      <div className="grid gap-6 xl:grid-cols-3">
        <div className="space-y-4 xl:col-span-1">
          <div className="glass-panel p-5 border border-white/20">
            <div className="mb-4 flex items-center gap-2">
              <ShieldAlert className="h-4 w-4 text-primary" />
              <h3 className="text-xs font-black uppercase tracking-[0.2em]">Governor contract</h3>
            </div>
            <dl className="space-y-2 text-sm">
              <div className="flex items-center justify-between">
                <dt className="text-muted-foreground">Pressure score</dt>
                <dd className="font-mono">{(governor?.pressureScore ?? 0).toFixed(2)}</dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-muted-foreground">Throttle level</dt>
                <dd className="font-mono">{governor?.throttleLevel ?? "normal"}</dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-muted-foreground">Emergency mode</dt>
                <dd className="font-mono">{governor?.emergencyMode ? "yes" : "no"}</dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-muted-foreground">Cost burn rate</dt>
                <dd className="font-mono">{governor?.costBurnRate == null ? "stub" : governor.costBurnRate}</dd>
              </div>
            </dl>
            <p className="mt-3 text-xs text-muted-foreground">
              Governor API hiện trả dữ liệu stub theo backend V3 hiện tại.
            </p>
            <Button
              variant="destructive"
              className="mt-4 w-full gap-2 text-xs font-black uppercase tracking-wider"
              onClick={() => emergencyFreeze.mutate()}
              disabled={emergencyFreeze.isPending}
            >
              <Pause className="h-4 w-4" />
              {emergencyFreeze.isPending ? "Sending request..." : "Emergency freeze (request)"}
            </Button>
          </div>

          <div className="glass-panel p-5 border border-white/20">
            <div className="mb-4 flex items-center gap-2">
              <Cpu className="h-4 w-4 text-primary" />
              <h3 className="text-xs font-black uppercase tracking-[0.2em]">System telemetry</h3>
            </div>
            <dl className="space-y-2 text-sm">
              <div className="flex items-center justify-between">
                <dt className="text-muted-foreground">CPU %</dt>
                <dd className="font-mono">{system?.cpuPercent == null ? "stub" : system.cpuPercent}</dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-muted-foreground">Memory %</dt>
                <dd className="font-mono">{system?.memoryPercent == null ? "stub" : system.memoryPercent}</dd>
              </div>
              <div className="flex items-center justify-between">
                <dt className="text-muted-foreground">Queue length</dt>
                <dd className="font-mono">{system?.queueLength == null ? "stub" : system.queueLength}</dd>
              </div>
            </dl>
          </div>
        </div>

        <div className="glass-panel overflow-hidden border border-white/20 xl:col-span-2">
          <div className="flex items-center justify-between border-b border-white/20 px-5 py-4">
            <h3 className="text-xs font-black uppercase tracking-[0.25em]">World matrix (runtime)</h3>
            <Button variant="ghost" size="sm" className="text-[10px] font-bold uppercase" asChild>
              <Link href="/cluster/events">
                Event stream <ArrowUpRight className="ml-1 h-3 w-3" />
              </Link>
            </Button>
          </div>

          <div className="p-5">
            <div className="mb-5 grid grid-cols-10 gap-2 sm:grid-cols-12 md:grid-cols-16 lg:grid-cols-20">
              {worlds.map((world) => {
                const stability = world.stability ?? 0;
                const entropy = world.entropy ?? 0;
                const critical = stability < 0.4 || entropy > 0.75;
                const warn = stability < 0.55 || entropy > 0.6;

                return (
                  <Link
                    key={world.id}
                    href={`/world/${world.id}`}
                    title={`${world.name} • tick ${world.current_tick} • entropy ${formatMetric(world.entropy)} • stability ${formatMetric(world.stability)}`}
                    className={cn(
                      "aspect-square rounded-sm border border-white/10 transition hover:scale-125",
                      critical && "bg-destructive/70",
                      !critical && warn && "bg-warning/70",
                      !critical && !warn && "bg-success/60"
                    )}
                  />
                );
              })}
            </div>

            <div className="rounded-lg border border-border/50 bg-white/40 p-3">
              <div className="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest">
                <AlertTriangle className="h-3.5 w-3.5 text-warning" />
                Top worlds by entropy
              </div>
              <div className="space-y-1.5 text-xs">
                {topRiskWorlds.map((w) => (
                  <div key={w.id} className="grid grid-cols-[1fr_auto_auto] gap-3">
                    <Link href={`/world/${w.id}`} className="truncate font-semibold hover:underline">
                      {w.name}
                    </Link>
                    <span className="font-mono text-muted-foreground">tick {w.current_tick}</span>
                    <span className="font-mono">E {formatMetric(w.entropy)}</span>
                  </div>
                ))}
                {topRiskWorlds.length === 0 && <p className="text-muted-foreground">No runtime world data.</p>}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
