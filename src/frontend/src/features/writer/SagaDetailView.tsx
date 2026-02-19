"use client";

import { useState } from "react";
import Link from "next/link";
import { useSaga, useSagaAdvance } from "./useWriterApi";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { SagaTreeView } from "./SagaTreeView";
import type { SagaDetail, SagaWorld } from "@/shared/api/writer";
import {
  Orbit,
  Activity,
  ChevronRight,
  Sparkles,
  FastForward,
  Globe,
  Clock,
  Zap,
} from "lucide-react";
import { cn } from "@/lib/utils";

function SagaWorldRow({ sw }: { sw: SagaWorld }) {
  const statusColor =
    sw.universe_status === "active"
      ? "text-success border-success/30 bg-success/10"
      : sw.universe_status === "collapsed"
        ? "text-error border-error/30 bg-error/10"
        : "text-muted-foreground border-border/30 bg-muted/10";

  return (
    <div className="glass-card hover:bg-secondary/5 transition-all group border border-border/40 p-3">
      <div className="flex items-center justify-between gap-4">
        {/* Left: Icon & Name */}
        <div className="flex items-center gap-3 min-w-[200px]">
          <div className={cn("h-8 w-8 rounded-lg flex items-center justify-center transition-colors",
            sw.universe_status === 'active' ? 'bg-primary/10 text-primary' : 'bg-muted/20 text-muted-foreground')}>
            <Globe className="h-4 w-4" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <p className="font-bold text-sm leading-none">{sw.world_name}</p>
              {sw.universe_id && (
                <span className="font-mono text-[9px] text-muted-foreground/70 uppercase tracking-wider">
                  {sw.universe_id.slice(0, 8)}
                </span>
              )}
            </div>
            <div className="flex items-center gap-2 mt-1 text-[10px] text-muted-foreground">
              <span className="flex items-center gap-1"><Clock className="h-3 w-3" /> Age: {sw.universe_age ?? 0}</span>
              <span>•</span>
              <span className="font-mono">Seq {sw.sequence}</span>
            </div>
          </div>
        </div>

        {/* Middle: Metrics */}
        <div className="flex items-center gap-6">
          <div className="flex flex-col items-center">
            <span className="text-[9px] uppercase tracking-wider text-muted-foreground font-semibold">Entropy</span>
            <span className={cn("text-xs font-mono font-bold", (sw.universe_entropy || 0) > 0.6 ? "text-error" : "text-foreground")}>
              {sw.universe_entropy?.toFixed(3) ?? '—'}
            </span>
          </div>
          <div className="flex flex-col items-center">
            <span className="text-[9px] uppercase tracking-wider text-muted-foreground font-semibold">Status</span>
            <Badge variant="outline" className={cn("text-[9px] h-4 px-1.5 uppercase border", statusColor)}>
              {sw.universe_status ?? 'unknown'}
            </Badge>
          </div>
        </div>

        {/* Right: Action */}
        <div className="flex items-center justify-end min-w-[100px]">
          {sw.world_id && (
            <Button asChild variant="ghost" size="sm" className="h-7 px-2 text-[10px] font-bold gap-1 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
              <Link href={`/world/${sw.world_id}`}>
                Inspect <ChevronRight className="h-3 w-3" />
              </Link>
            </Button>
          )}
        </div>
      </div>
    </div>
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
  const [tickCount, setTickCount] = useState(10);

  if (isLoading) return (
    <div className="flex items-center justify-center p-12 text-muted-foreground animate-pulse">
      <div className="flex flex-col items-center gap-2">
        <Orbit className="h-6 w-6 animate-spin text-primary/50" />
        <span className="text-xs font-medium uppercase tracking-widest">Scanning Timeline...</span>
      </div>
    </div>
  );

  if (error) return (
    <div className="glass-card border-error/20 p-6 text-error">
      <h3 className="font-bold flex items-center gap-2"><Activity className="h-4 w-4" /> Timeline Scan Failed</h3>
      <p className="text-xs mt-1 opacity-80">Cannot access Saga data. Connection interrupted.</p>
    </div>
  );

  if (!saga) return null;

  const sagaWorlds = (saga as SagaDetail).saga_worlds ?? [];
  const isComplete = saga.status === "completed" || saga.status === "COMPLETED";

  return (
    <div className="space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-500">
      {showCreatedMessage && (
        <div className="glass-card border-success/30 bg-success/5 p-3 flex items-center gap-3">
          <Sparkles className="h-4 w-4 text-success" />
          <p className="text-xs font-bold text-success">Saga initialized. V3 pipeline active.</p>
        </div>
      )}

      {/* Saga Header Compact */}
      <div className="glass-card p-5 relative overflow-hidden group">
        <div className="absolute top-0 right-0 p-4">
          {/* Status Badge */}
          <Badge variant="outline" className={cn(
            "font-mono text-[10px] uppercase tracking-widest px-2 py-0.5 border",
            saga.status === 'running' ? 'text-success border-success/30 bg-success/5' :
              isComplete ? 'text-primary border-primary/30 bg-primary/5' :
                'text-warning border-warning/30 bg-warning/5'
          )}>
            <span className="mr-1.5 inline-block h-1.5 w-1.5 rounded-full bg-current animate-pulse" />
            {saga.status}
          </Badge>
        </div>

        <div className="flex items-start gap-4">
          <div className="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary shadow-sm border border-primary/20">
            <Activity className="h-5 w-5" />
          </div>
          <div>
            <h2 className="text-xl font-bold tracking-tight text-foreground">{saga.name}</h2>
            <div className="flex items-center gap-3 mt-1 text-[10px] text-muted-foreground font-mono">
              <span className="uppercase tracking-widest">ID: {sagaId.slice(0, 8)}</span>
              {saga.current_universe_id && (
                <>
                  <span className="opacity-30">|</span>
                  <span className="uppercase tracking-widest text-primary font-bold">
                    Curr: {saga.current_universe_id.slice(0, 8)}
                  </span>
                </>
              )}
            </div>
          </div>
        </div>

        {/* Compact Metrics Grid */}
        <div className="grid grid-cols-3 gap-3 mt-5">
          <div className="p-2.5 rounded-lg bg-secondary/10 border border-border/50">
            <div className="flex items-center justify-between">
              <span className="text-[9px] uppercase tracking-wider text-muted-foreground font-semibold">Parallel Worlds</span>
              <Globe className="h-3 w-3 text-muted-foreground/50" />
            </div>
            <p className="text-lg font-bold mt-0.5">{sagaWorlds.length}</p>
          </div>

          <div className="p-2.5 rounded-lg bg-secondary/10 border border-border/50">
            <div className="flex items-center justify-between">
              <span className="text-[9px] uppercase tracking-wider text-muted-foreground font-semibold">Total Generated</span>
              <Activity className="h-3 w-3 text-muted-foreground/50" />
            </div>
            <p className="text-lg font-bold mt-0.5">{saga.world_count ?? sagaWorlds.length}</p>
          </div>

          <div className="p-2.5 rounded-lg bg-secondary/10 border border-border/50">
            <div className="flex items-center justify-between">
              <span className="text-[9px] uppercase tracking-wider text-muted-foreground font-semibold">Pipeline Ver</span>
              <Zap className="h-3 w-3 text-primary/70" />
            </div>
            <p className="text-lg font-bold mt-0.5 text-primary">V3.03</p>
          </div>
        </div>
      </div>

      {/* Advance Controls (Floating Bar) */}
      <div className="glass-panel p-2 rounded-xl flex items-center gap-2 border border-primary/20 bg-primary/5">
        <div className="flex items-center gap-2 px-3 border-r border-border/30">
          <FastForward className="h-4 w-4 text-primary" />
          <span className="text-[10px] font-bold uppercase tracking-widest text-primary">Control</span>
        </div>

        <div className="flex-1 flex items-center justify-center gap-2">
          <span className="text-[10px] uppercase font-semibold text-muted-foreground">Tick Batch:</span>
          <input
            type="number"
            min={1}
            max={100}
            value={tickCount}
            onChange={(e) => setTickCount(Math.max(1, parseInt(e.target.value) || 1))}
            className="h-7 w-12 rounded bg-background border border-border text-center text-xs font-mono font-bold focus:ring-1 focus:ring-primary"
          />
        </div>

        <Button
          size="sm"
          className="h-8 shadow-sm font-bold text-xs gap-1.5"
          disabled={advance.isPending || isComplete}
          onClick={() => advance.mutate(tickCount)}
        >
          {advance.isPending ? <Orbit className="h-3 w-3 animate-spin" /> : <FastForward className="h-3 w-3" />}
          {advance.isPending ? "Processing..." : "Run Simulation"}
        </Button>
      </div>

      {/* Timeline Tree */}
      <div className="glass-card p-0 overflow-hidden">
        <SagaTreeView sagaId={sagaId} />
      </div>

      {/* Saga Worlds List */}
      {sagaWorlds.length > 0 && (
        <div className="space-y-2 pt-2">
          <div className="flex items-center justify-between px-1">
            <h3 className="text-[10px] font-bold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
              <Globe className="h-3 w-3" />
              Active Worlds
            </h3>
            <Badge variant="secondary" className="text-[9px] h-4 px-1">{sagaWorlds.length}</Badge>
          </div>
          <div className="space-y-1.5">
            {sagaWorlds.map((sw) => (
              <SagaWorldRow key={sw.id} sw={sw} />
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
