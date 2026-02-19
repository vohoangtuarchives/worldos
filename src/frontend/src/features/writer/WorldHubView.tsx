"use client";

import { useState } from "react";
import Link from "next/link";
import { useWorld, useWorldAction, useWorldEmergency } from "./useWriterApi";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Orbit,
  Pause,
  Play,
  StepForward,
  Undo2,
  PlusCircle,
  Zap,
  Flame,
  ShieldOff,
  Skull,
  ToggleLeft,
  ToggleRight,
  AlertTriangle,
} from "lucide-react";
import { cn } from "@/lib/utils";
import { WorldHeroesCard } from "./WorldHeroesCard";

export function WorldHubView({ worldId, refetchInterval }: { worldId: string; refetchInterval?: number }) {
  const { data: world, isLoading, error } = useWorld(worldId, { refetchInterval });
  const freeze = useWorldAction("freeze");
  const resume = useWorldAction("resume");
  const step = useWorldAction("step");
  const rollback = useWorldAction("rollback");
  const createInstance = useWorldAction("createInstance");
  const emergency = useWorldEmergency();

  const [shockMagnitude, setShockMagnitude] = useState(0.15);
  const [rigidityReduction, setRigidityReduction] = useState(0.1);
  const [emergentDisabled, setEmergentDisabled] = useState(false);
  const [collapseConfirm, setCollapseConfirm] = useState(false);

  if (isLoading) return (
    <div className="flex items-center gap-2 text-muted-foreground animate-pulse">
      <div className="h-4 w-4 rounded-full bg-muted" />
      <span>Loading World Essence...</span>
    </div>
  );

  if (error) return (
    <div className="glass-card border-error/20 p-6 text-error">
      <h3 className="font-bold">Extraction Failed</h3>
      <p className="text-sm">Cannot connect to World {worldId} reactor.</p>
    </div>
  );

  if (!world) return null;

  // V3: Pull live metrics from the first active universe
  const activeUniverse = world.runtime_instances?.find(u => u.status === 'active' || !u.is_archived) ?? world.runtime_instances?.[0];
  const liveEntropy = activeUniverse?.entropy ?? 0;
  const liveStability = activeUniverse?.stability_index ?? 0;
  const liveAge = activeUniverse?.age ?? 0;
  const runtimeInstances = world.runtime_instances ?? [];
  const universeCount = runtimeInstances.length;
  const activeCount = runtimeInstances.filter((u) => u.status === "active").length;
  const collapsedCount = runtimeInstances.filter((u) => u.status === "collapsed").length;
  const archivedCount = runtimeInstances.filter((u) => u.is_archived).length;
  const trackedEntropies = runtimeInstances.map((u) => u.entropy).filter((v): v is number => typeof v === "number");
  const trackedStability = runtimeInstances.map((u) => u.stability_index).filter((v): v is number => typeof v === "number");
  const avgEntropy = trackedEntropies.length > 0 ? trackedEntropies.reduce((sum, item) => sum + item, 0) / trackedEntropies.length : 0;
  const avgStability = trackedStability.length > 0 ? trackedStability.reduce((sum, item) => sum + item, 0) / trackedStability.length : 0;

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
      {/* World Entity Header & Live Metrics */}
      <div className="glass-card p-6 overflow-hidden relative">
        <div className="absolute top-0 right-0 p-4">
          <Badge variant="outline" className={cn(
            "font-mono text-[10px] uppercase tracking-widest",
            world.status === 'running' ? 'text-success border-success/20 bg-success/5' : 'text-warning border-warning/20 bg-warning/5'
          )}>
            {world.status ?? "offline"}
          </Badge>
        </div>

        <div className="flex items-start gap-4 mb-8">
          <div className="h-14 w-14 rounded-xl bg-primary/10 flex items-center justify-center text-primary shadow-inner">
            <Orbit className="h-8 w-8" />
          </div>
          <div>
            <h2 className="text-3xl font-bold tracking-tight text-foreground">{world.name}</h2>
            <div className="flex items-center gap-4 mt-1">
              <span className="text-xs uppercase tracking-widest text-muted-foreground font-bold">World Entity • ID: {worldId}</span>
              <span className="text-xs text-muted-foreground font-mono">TICK: {world.current_tick ?? 0}</span>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div className="p-4 rounded-lg bg-muted/30 border border-border/50">
            <span className="metric-label">Entropy</span>
            <p className={cn("text-2xl font-bold metric-value", liveEntropy > 0.7 ? "text-error" : liveEntropy > 0.4 ? "text-warning" : "text-foreground")}>
              {liveEntropy.toFixed(3)}
            </p>
          </div>
          <div className="p-4 rounded-lg bg-muted/30 border border-border/50">
            <span className="metric-label">Stability</span>
            <p className={cn("text-2xl font-bold metric-value", liveStability > 0.7 ? "text-success" : liveStability > 0.4 ? "text-warning" : "text-error")}>
              {(liveStability * 100).toFixed(1)}%
            </p>
          </div>
          <div className="p-4 rounded-lg bg-muted/30 border border-border/50">
            <span className="metric-label">Universe Age</span>
            <p className="text-2xl font-bold metric-value">{liveAge}</p>
          </div>
          <div className="p-4 rounded-lg bg-muted/30 border border-border/50">
            <span className="metric-label">Instances</span>
            <p className="text-2xl font-bold metric-value text-primary">{universeCount}</p>
          </div>
        </div>
      </div>

      <div className="glass-card p-6">
        <div className="flex flex-wrap items-center justify-between gap-3 mb-4">
          <div>
            <h3 className="font-bold text-sm uppercase tracking-wider">Universe Tracking Board</h3>
            <p className="text-xs text-muted-foreground">Theo dõi realtime để thấy rõ runtime V3 đang chạy ra sao, không còn cảm giác &quot;đen hộp&quot;.</p>
          </div>
          <Badge variant="outline" className="text-[10px] uppercase">{activeCount} active • {collapsedCount} collapsed</Badge>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
          <div className="rounded-lg border border-border/50 p-3 bg-muted/20">
            <p className="metric-label">Avg Entropy</p>
            <p className={cn("text-xl font-bold", avgEntropy > 0.65 ? "text-error" : "text-foreground")}>{avgEntropy.toFixed(3)}</p>
          </div>
          <div className="rounded-lg border border-border/50 p-3 bg-muted/20">
            <p className="metric-label">Avg Stability</p>
            <p className={cn("text-xl font-bold", avgStability < 0.45 ? "text-error" : "text-success")}>{(avgStability * 100).toFixed(1)}%</p>
          </div>
          <div className="rounded-lg border border-border/50 p-3 bg-muted/20">
            <p className="metric-label">Archived</p>
            <p className="text-xl font-bold text-muted-foreground">{archivedCount}</p>
          </div>
          <div className="rounded-lg border border-border/50 p-3 bg-muted/20">
            <p className="metric-label">Tick</p>
            <p className="text-xl font-bold">{world.current_tick ?? 0}</p>
          </div>
        </div>

        <div className="overflow-x-auto rounded-xl border border-border/40">
          <table className="w-full text-xs">
            <thead className="bg-muted/30">
              <tr className="text-left uppercase tracking-wider text-muted-foreground">
                <th className="px-3 py-2">Universe</th>
                <th className="px-3 py-2">Status</th>
                <th className="px-3 py-2">Age</th>
                <th className="px-3 py-2">Entropy</th>
                <th className="px-3 py-2">Stability</th>
                <th className="px-3 py-2 text-right">Inspect</th>
              </tr>
            </thead>
            <tbody>
              {runtimeInstances.length === 0 ? (
                <tr>
                  <td colSpan={6} className="px-3 py-4 text-center text-muted-foreground">Chưa có runtime universe nào để theo dõi.</td>
                </tr>
              ) : runtimeInstances.map((u) => (
                <tr key={u.id} className="border-t border-border/40">
                  <td className="px-3 py-2 font-medium">{u.name}</td>
                  <td className="px-3 py-2">
                    <Badge variant="outline" className={cn("text-[10px]", u.status === "active" ? "text-success border-success/30" : u.status === "collapsed" ? "text-error border-error/30" : "")}>{u.status ?? "unknown"}</Badge>
                  </td>
                  <td className="px-3 py-2 font-mono">{u.age ?? 0}</td>
                  <td className="px-3 py-2 font-mono">{u.entropy?.toFixed(3) ?? "—"}</td>
                  <td className="px-3 py-2 font-mono">{u.stability_index?.toFixed(3) ?? "—"}</td>
                  <td className="px-3 py-2 text-right">
                    <Button asChild variant="ghost" size="sm" className="h-7 text-[11px]">
                      <Link href={`/world/${worldId}?universe=${u.id}`}>Open</Link>
                    </Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Active Heroes */}
      <WorldHeroesCard worldId={worldId} />

      {/* Intervention Console */}
      <div className="glass-panel p-6 rounded-2xl">
        <div className="flex items-center gap-2 mb-6">
          <Zap className="h-5 w-5 text-primary" />
          <h3 className="font-bold text-sm uppercase tracking-wider">Intervention Console</h3>
        </div>
        <div className="flex flex-wrap gap-3">
          <Button
            className="gap-2 font-bold shadow-md h-10 px-6"
            disabled={freeze.isPending}
            onClick={() => freeze.mutate(worldId)}
          >
            <Pause className="h-4 w-4" /> Freeze
          </Button>
          <Button
            variant="outline"
            className="gap-2 font-bold bg-white/50 h-10 px-6"
            disabled={resume.isPending}
            onClick={() => resume.mutate(worldId)}
          >
            <Play className="h-4 w-4" /> Resume
          </Button>
          <div className="h-10 w-px bg-border/50 mx-2" />
          <Button
            variant="outline"
            className="gap-2 font-bold bg-white/50"
            disabled={step.isPending}
            onClick={() => step.mutate(worldId)}
          >
            <StepForward className="h-4 w-4" /> Step Tick
          </Button>
          <Button
            variant="outline"
            className="gap-2 font-bold bg-white/50"
            disabled={rollback.isPending}
            onClick={() => rollback.mutate(worldId)}
          >
            <Undo2 className="h-4 w-4" /> Rollback
          </Button>
          <Button
            variant="secondary"
            className="gap-2 font-bold ml-auto"
            disabled={createInstance.isPending}
            onClick={() => createInstance.mutate(worldId)}
          >
            <PlusCircle className="h-4 w-4" /> New Instance
          </Button>
        </div>
      </div>

      {/* V3 Emergency Console */}
      <div className="glass-card-accent p-6 space-y-6">
        <div className="flex items-center gap-2">
          <AlertTriangle className="h-5 w-5 text-warning" />
          <h3 className="font-bold text-sm uppercase tracking-wider">Emergency Interventions</h3>
          <Badge variant="outline" className="text-[9px] ml-auto text-warning border-warning/30 bg-warning/5">V3 UNIVERSE-LEVEL</Badge>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Entropy Shock */}
          <div className="p-4 rounded-xl bg-white/30 border border-orange-200/50 space-y-3">
            <div className="flex items-center gap-2">
              <Flame className="h-4 w-4 text-orange-500" />
              <span className="text-xs font-bold uppercase tracking-wider">Entropy Shock</span>
            </div>
            <p className="text-[10px] text-muted-foreground">Spike entropy để destabilize locked systems. Tăng entropy, giảm order.</p>
            <div className="flex items-center gap-3">
              <label className="text-[10px] font-bold text-muted-foreground whitespace-nowrap">Magnitude</label>
              <input
                type="range"
                min={0.05}
                max={0.3}
                step={0.01}
                value={shockMagnitude}
                onChange={(e) => setShockMagnitude(parseFloat(e.target.value))}
                className="flex-1 accent-orange-500"
              />
              <span className="font-mono text-xs font-bold w-10 text-right">{shockMagnitude.toFixed(2)}</span>
            </div>
            <Button
              size="sm"
              variant="outline"
              className="w-full gap-2 font-bold text-orange-600 border-orange-300 hover:bg-orange-50"
              disabled={emergency.isPending}
              onClick={() => emergency.mutate({ worldId, action: "entropy-shock", params: { magnitude: shockMagnitude } })}
            >
              <Flame className="h-3.5 w-3.5" />
              {emergency.isPending ? "Injecting..." : "Inject Entropy Shock"}
            </Button>
          </div>

          {/* Reduce Rigidity */}
          <div className="p-4 rounded-xl bg-white/30 border border-blue-200/50 space-y-3">
            <div className="flex items-center gap-2">
              <ShieldOff className="h-4 w-4 text-blue-500" />
              <span className="text-xs font-bold uppercase tracking-wider">Reduce Rigidity</span>
            </div>
            <p className="text-[10px] text-muted-foreground">Giảm rigidity, tăng flexibility. Nhẹ entropy increase + strain decrease.</p>
            <div className="flex items-center gap-3">
              <label className="text-[10px] font-bold text-muted-foreground whitespace-nowrap">Reduction</label>
              <input
                type="range"
                min={0.05}
                max={0.2}
                step={0.01}
                value={rigidityReduction}
                onChange={(e) => setRigidityReduction(parseFloat(e.target.value))}
                className="flex-1 accent-blue-500"
              />
              <span className="font-mono text-xs font-bold w-10 text-right">{rigidityReduction.toFixed(2)}</span>
            </div>
            <Button
              size="sm"
              variant="outline"
              className="w-full gap-2 font-bold text-blue-600 border-blue-300 hover:bg-blue-50"
              disabled={emergency.isPending}
              onClick={() => emergency.mutate({ worldId, action: "reduce-rigidity", params: { reduction: rigidityReduction } })}
            >
              <ShieldOff className="h-3.5 w-3.5" />
              {emergency.isPending ? "Reducing..." : "Reduce Rigidity"}
            </Button>
          </div>

          {/* Force Collapse */}
          <div className="p-4 rounded-xl bg-white/30 border border-red-200/50 space-y-3">
            <div className="flex items-center gap-2">
              <Skull className="h-4 w-4 text-red-500" />
              <span className="text-xs font-bold uppercase tracking-wider">Force Collapse</span>
            </div>
            <p className="text-[10px] text-muted-foreground">Trigger CTI threshold breach — spike entropy, drain order/stability. Universe → COLLAPSED.</p>
            {collapseConfirm ? (
              <div className="flex gap-2">
                <Button
                  size="sm"
                  variant="destructive"
                  className="flex-1 gap-2 font-bold"
                  disabled={emergency.isPending}
                  onClick={() => {
                    emergency.mutate({ worldId, action: "force-collapse" });
                    setCollapseConfirm(false);
                  }}
                >
                  <Skull className="h-3.5 w-3.5" /> Confirm Collapse
                </Button>
                <Button size="sm" variant="ghost" className="font-bold" onClick={() => setCollapseConfirm(false)}>
                  Cancel
                </Button>
              </div>
            ) : (
              <Button
                size="sm"
                variant="outline"
                className="w-full gap-2 font-bold text-red-600 border-red-300 hover:bg-red-50"
                onClick={() => setCollapseConfirm(true)}
              >
                <Skull className="h-3.5 w-3.5" /> Force Collapse
              </Button>
            )}
          </div>

          {/* Toggle Emergent */}
          <div className="p-4 rounded-xl bg-white/30 border border-purple-200/50 space-y-3">
            <div className="flex items-center gap-2">
              {emergentDisabled ? <ToggleLeft className="h-4 w-4 text-purple-400" /> : <ToggleRight className="h-4 w-4 text-purple-500" />}
              <span className="text-xs font-bold uppercase tracking-wider">Emergent Archetypes</span>
            </div>
            <p className="text-[10px] text-muted-foreground">Block hoặc enable tạo archetype mới. Khi disabled, hệ thống không spawn archetype mới.</p>
            <Button
              size="sm"
              variant="outline"
              className={cn(
                "w-full gap-2 font-bold",
                emergentDisabled
                  ? "text-purple-400 border-purple-200"
                  : "text-purple-600 border-purple-300 hover:bg-purple-50"
              )}
              disabled={emergency.isPending}
              onClick={() => {
                const newState = !emergentDisabled;
                emergency.mutate({ worldId, action: "toggle-emergent", params: { disabled: newState } });
                setEmergentDisabled(newState);
              }}
            >
              {emergentDisabled ? <ToggleLeft className="h-3.5 w-3.5" /> : <ToggleRight className="h-3.5 w-3.5" />}
              {emergentDisabled ? "Đang Disabled — Click để Enable" : "Đang Enabled — Click để Disable"}
            </Button>
          </div>
        </div>

        {/* Emergency status */}
        {emergency.isSuccess && (
          <div className="p-3 rounded-lg bg-success/10 border border-success/20 text-sm text-success font-medium animate-in fade-in duration-300">
            ✓ {emergency.data?.message}
          </div>
        )}
        {emergency.isError && (
          <div className="p-3 rounded-lg bg-error/10 border border-error/20 text-sm text-error font-medium animate-in fade-in duration-300">
            ✕ {(emergency.error as Error)?.message ?? "Emergency action failed."}
          </div>
        )}
      </div>

    </div>
  );
}
