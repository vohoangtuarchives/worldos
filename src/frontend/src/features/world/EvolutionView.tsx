"use client";

import { useState, useMemo } from "react";
import { useSearchParams } from "next/navigation";
import { useQuery } from "@tanstack/react-query";
import {
  useWorld,
  useWorldGodConsoleMetrics,
  useUniverseMetrics,
} from "@/features/writer/useWriterApi";
import { writerApi } from "@/shared/api/writer";
import { Badge } from "@/components/ui/badge";
import {
  Activity,
  Orbit,
  BarChart3,
  Waves,
  Route,
  Boxes,
  DatabaseBackup,
  AlertTriangle,
  CheckCircle2,
  Zap,
} from "lucide-react";
import { useWorldStream } from "./hooks/useWorldStream";
import { RealtimeVectorAnalysis } from "./components/RealtimeVectorAnalysis";
import { LiveChronicleNode } from "./components/LiveChronicleNode";
import { useSimulationStore } from "./stores/useSimulationStore";

const METRICS_POLL_MS = 4000;

const COLLAPSED_STATUSES = ["collapsed", "dissipated", "destroyed", "terminated", "void"];

export function EvolutionView({
  worldId,
  forceUniverseId,
}: {
  worldId: string;
  forceUniverseId?: string;
}) {
  const searchParams = useSearchParams();
  const universeFromUrl = searchParams.get("universe") ?? "";
  const [manualSelectedUniverseId, setManualSelectedUniverseId] = useState<string>("");

  const { data: world } = useWorld(worldId);
  const runtimeInstances = useMemo(() => world?.runtime_instances ?? [], [world?.runtime_instances]);

  const selectedUniverseId = useMemo(() => {
    if (forceUniverseId) {
      return forceUniverseId;
    }
    if (manualSelectedUniverseId && runtimeInstances.some((u) => u.id === manualSelectedUniverseId)) {
      return manualSelectedUniverseId;
    }
    if (universeFromUrl && runtimeInstances.some((u) => u.id === universeFromUrl)) {
      return universeFromUrl;
    }
    return runtimeInstances[0]?.id ?? "";
  }, [manualSelectedUniverseId, runtimeInstances, universeFromUrl]);

  const useUniverse = selectedUniverseId.length > 0;
  const { data: universeData, isLoading: universeLoading } = useUniverseMetrics(
    useUniverse ? selectedUniverseId : null,
    { refetchInterval: METRICS_POLL_MS }
  );
  const { data: worldData, isLoading: worldLoading } = useWorldGodConsoleMetrics(
    useUniverse ? null : worldId,
    { refetchInterval: METRICS_POLL_MS }
  );

  const { data: materialsData } = useQuery({
    queryKey: ["writer", "worlds", worldId, "materials", "instances"],
    queryFn: () => writerApi.materials.worldInstances(worldId),
    enabled: !!worldId,
    refetchInterval: METRICS_POLL_MS,
  });

  const { data: timelineData } = useQuery({
    queryKey: ["writer", "worlds", worldId, "materials", "timeline"],
    queryFn: () => writerApi.materials.timeline(worldId),
    enabled: !!worldId,
    refetchInterval: METRICS_POLL_MS,
  });

  // HYPER-REALTIME STREAM HOOK
  useWorldStream(selectedUniverseId || worldId);
  const rtYear = useSimulationStore(s => s.year);
  const rtPhase = useSimulationStore(s => s.phase);
  const rtEntropy = useSimulationStore(s => s.currentEntropy);
  const rtStability = useSimulationStore(s => s.currentStability);
  const rtVector = useSimulationStore(s => s.currentVector);

  const data = (useUniverse ? universeData : worldData) as
    | { tick?: number; phase?: string; state_vector?: Record<string, number>; entropy?: number | null; stability_index?: number | null }
    | undefined;
  const isLoading = useUniverse ? universeLoading : worldLoading;

  const tick = data?.tick ?? 0;
  const phase = data?.phase ?? "unknown";
  const vector = data?.state_vector ?? {};
  const entropy = data?.entropy ?? vector?.entropy;
  const stability = data?.stability_index ?? vector?.stability_index;

  const collapsedUniverses = useMemo(
    () => runtimeInstances.filter((u) => COLLAPSED_STATUSES.includes(String(u.status ?? "").toLowerCase())),
    [runtimeInstances]
  );

  const materialEvents = timelineData?.events ?? [];
  const materialLifecycle = materialsData?.lifecycle ?? { active: 0, dormant: 0, retired: 0 };

  const KEYS = [
    'ce', 'sc', 'tech', 'stab', 'pros', 'mp', 'ie',
    'legit', 'ec', 'ineq', 'sust', 'myst', 'legacy',
    'exp', 'info', 'mob', 'curv'
  ];

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      {!forceUniverseId && (
        <div className="glass-panel p-4 rounded-xl flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Orbit className="h-5 w-5 text-primary" />
            <span className="text-xs font-bold uppercase tracking-tight">Monitoring Scope</span>
          </div>
          <select
            className="bg-transparent border-none text-sm font-bold text-primary focus:ring-0 cursor-pointer"
            value={selectedUniverseId}
            onChange={(e) => setManualSelectedUniverseId(e.target.value)}
          >
            <option value="">— World Kernel (Legacy) —</option>
            {runtimeInstances.map((u) => (
              <option key={u.id} value={u.id}>
                {u.name} (Age {u.age})
              </option>
            ))}
          </select>
        </div>
      )}

      <div className="grid gap-6 lg:grid-cols-4">
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
                <p className="text-4xl font-bold metric-value tracking-tighter">
                  {rtYear || tick}
                </p>
              </div>

              <div className="grid grid-cols-2 gap-4 pt-4 border-t border-border/30">
                <div>
                  <span className="metric-label">Entropy</span>
                  <p className="text-xl font-bold metric-value">
                    {(rtEntropy || Number(entropy) || 0).toFixed(4)}
                  </p>
                </div>
                <div>
                  <span className="metric-label">Stability</span>
                  <p className="text-xl font-bold metric-value text-success">
                    {(rtStability || Number(stability) || 0).toFixed(4)}
                  </p>
                </div>
              </div>

              <div className="pt-4 border-t border-border/30">
                <span className="metric-label">Current Phase</span>
                <Badge variant="secondary" className="mt-2 w-full justify-center py-1 font-mono uppercase text-[10px] tracking-widest border-primary/20 bg-primary/5 text-primary">
                  {rtPhase !== 'PRIMORDIAL' ? rtPhase : phase}
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
              Auto-collection continuously records materials across universes.
            </p>
          </div>
        </div>

        <div className="lg:col-span-2">
          <div className="glass-card flex flex-col h-full overflow-hidden">
            <div className="px-6 py-4 border-b border-border/50 flex items-center justify-between bg-white/40">
              <h3 className="font-bold text-sm uppercase tracking-wider flex items-center gap-2">
                <Zap className="h-4 w-4 text-sky-500" />
                Hyper-Realtime Vector Analysis
              </h3>
            </div>
            <div className="flex-1 p-6 flex flex-col">
              <RealtimeVectorAnalysis />

              <div className="mt-4 grid grid-cols-2 md:grid-cols-4 gap-2">
                {KEYS.map((k, i) => (
                  <div key={k} className="flex flex-col p-2 bg-muted/20 border border-border/20 rounded-md">
                    <span className="text-[9px] text-muted-foreground uppercase font-bold">{k}</span>
                    <span className="text-xs font-mono font-bold">{(rtVector[i] || 0).toFixed(4)}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>

        <div className="lg:col-span-1 h-full min-h-[400px]">
          <LiveChronicleNode />
        </div>
      </div>

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="lg:col-span-2 glass-card p-6 space-y-4">
          <h3 className="text-sm font-bold uppercase tracking-wider flex items-center gap-2">
            <Route className="h-4 w-4 text-primary" /> Vận hành tiến hoá & vận chuyển universe
          </h3>
          <div className="grid sm:grid-cols-2 gap-3 text-xs">
            {[
              "1. Auto evolution loop theo tick realtime",
              "2. Chuyển trạng thái universe qua các phase sống",
              "3. Thu thập material ngay khi material kích hoạt/mutation",
              "4. Đồng bộ về world material ledger",
            ].map((item) => (
              <div key={item} className="rounded-lg border border-border/40 bg-muted/20 p-3 font-medium">
                {item}
              </div>
            ))}
          </div>

          <div className="space-y-2">
            {runtimeInstances.length === 0 ? (
              <p className="text-xs text-muted-foreground">Chưa có universe runtime.</p>
            ) : (
              runtimeInstances.map((u) => {
                const status = String(u.status ?? "active").toLowerCase();
                const isCollapsed = COLLAPSED_STATUSES.includes(status);
                return (
                  <div key={u.id} className="flex items-center justify-between rounded-lg border border-border/40 px-3 py-2">
                    <div>
                      <p className="text-sm font-semibold">{u.name}</p>
                      <p className="text-[11px] text-muted-foreground">Age {u.age} · ID {u.id.slice(0, 8)}…</p>
                    </div>
                    <Badge variant={isCollapsed ? "destructive" : "secondary"}>{status}</Badge>
                  </div>
                );
              })
            )}
          </div>
        </div>

        <div className="glass-card p-6 space-y-4">
          <h3 className="text-sm font-bold uppercase tracking-wider flex items-center gap-2">
            <Boxes className="h-4 w-4 text-primary" /> Material Harvest
          </h3>
          <div className="grid grid-cols-2 gap-2 text-xs">
            <div className="rounded-lg bg-muted/20 p-3 border border-border/30">
              <p className="text-muted-foreground uppercase text-[10px]">Active</p>
              <p className="text-xl font-bold">{materialLifecycle.active ?? 0}</p>
            </div>
            <div className="rounded-lg bg-muted/20 p-3 border border-border/30">
              <p className="text-muted-foreground uppercase text-[10px]">Retired</p>
              <p className="text-xl font-bold">{materialLifecycle.retired ?? 0}</p>
            </div>
          </div>
          <div className="text-xs text-muted-foreground rounded-lg border border-border/30 p-3 bg-muted/10">
            Total material records: <span className="font-bold text-foreground">{materialsData?.total ?? 0}</span>
          </div>
          <div className="space-y-2">
            {materialEvents.slice(0, 4).map((ev, idx) => (
              <div key={`${ev.type}-${idx}`} className="text-xs rounded-md border border-border/30 p-2">
                <p className="font-medium">{ev.description}</p>
                <p className="text-[10px] text-muted-foreground">Epoch {ev.epoch} · {ev.type}</p>
              </div>
            ))}
            {materialEvents.length === 0 && (
              <p className="text-xs text-muted-foreground">Chưa có material event.</p>
            )}
          </div>
        </div>
      </div>

      <div className="glass-card p-6 space-y-3 border-primary/20">
        <h3 className="text-sm font-bold uppercase tracking-wider flex items-center gap-2">
          <DatabaseBackup className="h-4 w-4 text-primary" /> Persistent Material Ledger
        </h3>
        {collapsedUniverses.length > 0 ? (
          <div className="rounded-lg border border-amber-500/30 bg-amber-50/40 p-3 text-xs">
            <p className="font-semibold flex items-center gap-2"><AlertTriangle className="h-4 w-4" /> {collapsedUniverses.length} universe đã tiêu tan.</p>
            <p className="mt-1 text-muted-foreground">
              Material vẫn được giữ trong ledger trung tâm. Retired/legacy records hiện tại: <strong>{materialLifecycle.retired ?? 0}</strong>.
            </p>
          </div>
        ) : (
          <div className="rounded-lg border border-emerald-500/30 bg-emerald-50/40 p-3 text-xs flex items-center gap-2">
            <CheckCircle2 className="h-4 w-4 text-emerald-600" />
            Tất cả universe đang ổn định. Material ledger vẫn ghi nhận xuyên suốt tiến hoá.
          </div>
        )}
      </div>
    </div>
  );
}
