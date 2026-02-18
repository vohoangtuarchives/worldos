"use client";

import { useState, useEffect } from "react";
import { useSearchParams } from "next/navigation";
import {
  useWorld,
  useWorldGodConsoleMetrics,
  useUniverseMetrics,
} from "@/features/writer/useWriterApi";
import { Badge } from "@/components/ui/badge";
import {
  Activity,
  Zap,
  Orbit,
  ChevronRight,
  BarChart3,
  Waves
} from "lucide-react";
import { cn } from "@/lib/utils";

const METRICS_POLL_MS = 4000;

export function EvolutionView({ worldId }: { worldId: string }) {
  const searchParams = useSearchParams();
  const universeFromUrl = searchParams.get("universe") ?? "";
  const [selectedUniverseId, setSelectedUniverseId] = useState<string>("");

  const { data: world } = useWorld(worldId);
  const runtimeInstances = world?.runtime_instances ?? [];

  useEffect(() => {
    if (universeFromUrl && runtimeInstances.some((u) => u.id === universeFromUrl)) {
      setSelectedUniverseId(universeFromUrl);
    } else if (runtimeInstances.length > 0 && !selectedUniverseId) {
      setSelectedUniverseId(runtimeInstances[0].id);
    }
  }, [universeFromUrl, runtimeInstances, selectedUniverseId]);

  const useUniverse = selectedUniverseId.length > 0;
  const { data: universeData, isLoading: universeLoading } = useUniverseMetrics(
    useUniverse ? selectedUniverseId : null,
    { refetchInterval: METRICS_POLL_MS }
  );
  const { data: worldData, isLoading: worldLoading } = useWorldGodConsoleMetrics(
    useUniverse ? null : worldId,
    { refetchInterval: METRICS_POLL_MS }
  );

  const data: any = useUniverse ? universeData : worldData;
  const isLoading = useUniverse ? universeLoading : worldLoading;

  const tick = data?.tick ?? 0;
  const phase = data?.phase ?? "unknown";
  const vector = data?.state_vector ?? {};
  const entropy = data?.entropy ?? vector?.entropy;
  const stability = data?.stability_index ?? vector?.stability_index;

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      {/* Scope Selector */}
      <div className="glass-panel p-4 rounded-xl flex items-center justify-between">
        <div className="flex items-center gap-3">
          <Orbit className="h-5 w-5 text-primary" />
          <span className="text-xs font-bold uppercase tracking-tight">Monitoring Scope</span>
        </div>
        <select
          className="bg-transparent border-none text-sm font-bold text-primary focus:ring-0 cursor-pointer"
          value={selectedUniverseId}
          onChange={(e) => setSelectedUniverseId(e.target.value)}
        >
          <option value="">— World Kernel (Legacy) —</option>
          {runtimeInstances.map((u) => (
            <option key={u.id} value={u.id}>
              {u.name} (Age {u.age})
            </option>
          ))}
        </select>
      </div>

      <div className="grid gap-8 lg:grid-cols-3">
        {/* Core Live Metrics */}
        <div className="lg:col-span-1 space-y-6">
          <div className="glass-card p-6">
            <div className="flex items-center justify-between mb-6">
              <span className="metric-label">Evolution State</span>
              <div className="flex items-center gap-2">
                <div className="h-2 w-2 rounded-full bg-success animate-pulse" />
                <span className="text-[10px] font-bold text-success uppercase">Live Pulse</span>
              </div>
            </div>

            <div className="space-y-6">
              <div>
                <p className="metric-label mb-1">Current Tick/Cycle</p>
                <p className="text-4xl font-bold metric-value tracking-tighter">{tick}</p>
              </div>

              <div className="grid grid-cols-2 gap-4 pt-4 border-t border-border/30">
                <div>
                  <span className="metric-label">Entropy</span>
                  <p className="text-xl font-bold metric-value">{(Number(entropy) || 0).toFixed(3)}</p>
                </div>
                <div>
                  <span className="metric-label">Stability</span>
                  <p className="text-xl font-bold metric-value text-success">{(Number(stability) || 0).toFixed(3)}</p>
                </div>
              </div>

              <div className="pt-4 border-t border-border/30">
                <span className="metric-label">Current Phase</span>
                <Badge variant="secondary" className="mt-2 w-full justify-center py-1 font-mono uppercase text-[10px] tracking-widest border-primary/20 bg-primary/5 text-primary">
                  {phase}
                </Badge>
              </div>
            </div>
          </div>

          <div className="glass-card-accent p-6">
            <div className="flex items-center gap-2 mb-4">
              <Waves className="h-4 w-4 text-primary" />
              <span className="text-[10px] font-bold uppercase tracking-wider">Evolution Dynamics</span>
            </div>
            <p className="text-xs text-muted-foreground leading-relaxed">
              The evolution loop is executing at {METRICS_POLL_MS / 1000}s intervals.
              Adaptive mutation rate is currently active.
            </p>
          </div>
        </div>

        {/* State Vector Visualization */}
        <div className="lg:col-span-2">
          <div className="glass-card flex flex-col h-full overflow-hidden">
            <div className="px-6 py-4 border-b border-border/50 flex items-center justify-between bg-white/40">
              <h3 className="font-bold text-sm uppercase tracking-wider flex items-center gap-2">
                <BarChart3 className="h-4 w-4 text-primary" />
                State Vector Analysis
              </h3>
            </div>
            <div className="flex-1 p-6">
              {Object.keys(vector).length === 0 ? (
                <div className="h-full flex flex-col items-center justify-center text-muted-foreground opacity-50 space-y-2">
                  <Activity className="h-12 w-12" />
                  <p className="text-xs uppercase tracking-widest font-bold">Waiting for Vector Stream...</p>
                </div>
              ) : (
                <div className="grid gap-3">
                  {Object.entries(vector).map(([k, v]) => (
                    <div key={k} className="flex items-center justify-between p-3 rounded-lg bg-muted/20 border border-border/20 group hover:border-primary/30 transition-all">
                      <div className="flex items-center gap-3">
                        <div className="h-2 w-2 rounded-full bg-primary/20 group-hover:bg-primary transition-all" />
                        <span className="text-xs font-bold text-muted-foreground uppercase tracking-tight">{k}</span>
                      </div>
                      <div className="flex items-center gap-4">
                        <span className="font-mono text-xs font-bold tabular-nums">
                          {typeof v === "number" ? v.toFixed(4) : String(v)}
                        </span>
                        <div className="h-1.5 w-24 bg-muted rounded-full overflow-hidden">
                          <div
                            className="h-full bg-primary/60"
                            style={{ width: `${Math.min(typeof v === 'number' ? v * 100 : 0, 100)}%` }}
                          />
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
