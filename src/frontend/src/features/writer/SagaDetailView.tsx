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
} from "lucide-react";
import { cn } from "@/lib/utils";

function SagaWorldRow({ sw }: { sw: SagaWorld }) {
  const statusColor =
    sw.universe_status === "active"
      ? "text-success"
      : sw.universe_status === "collapsed"
        ? "text-error"
        : "text-muted-foreground";

  return (
    <div className="glass-card p-4 group hover:border-primary/30 transition-all">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="h-9 w-9 rounded-lg bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
            <Globe className="h-4 w-4" />
          </div>
          <div>
            <p className="font-bold text-sm">{sw.world_name}</p>
            <div className="flex items-center gap-2 mt-0.5 font-mono text-[9px] text-muted-foreground uppercase tracking-wider">
              <span>Seq {sw.sequence}</span>
              {sw.universe_id && (
                <>
                  <span>•</span>
                  <span title={sw.universe_id}>{sw.universe_id.slice(0, 8)}</span>
                </>
              )}
              {sw.universe_age != null && (
                <>
                  <span>•</span>
                  <span>Age: {sw.universe_age}</span>
                </>
              )}
            </div>
          </div>
        </div>

        <div className="flex items-center gap-3">
          {sw.universe_status && (
            <Badge variant="outline" className={cn("text-[9px] h-5 uppercase", statusColor)}>
              {sw.universe_status}
            </Badge>
          )}
          {sw.universe_entropy != null && (
            <div className="flex flex-col items-center">
              <span className="text-[8px] text-muted-foreground uppercase">Entropy</span>
              <span className="text-[10px] font-mono font-bold">{sw.universe_entropy.toFixed(3)}</span>
            </div>
          )}
          {sw.world_id && (
            <Button asChild variant="ghost" size="sm" className="h-7 px-2 text-[10px] font-bold gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
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
    <div className="flex items-center gap-2 text-muted-foreground animate-pulse p-8">
      <Orbit className="h-5 w-5 animate-spin" />
      <span className="text-sm italic">Scanning Saga timeline...</span>
    </div>
  );

  if (error) return (
    <div className="glass-card border-error/20 p-6 text-error">
      <h3 className="font-bold">Timeline Scan Failed</h3>
      <p className="text-sm mt-1">Cannot access Saga data. Check your connection.</p>
    </div>
  );

  if (!saga) return null;

  const sagaWorlds = (saga as SagaDetail).saga_worlds ?? [];
  const isComplete = saga.status === "completed" || saga.status === "COMPLETED";

  return (
    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
      {showCreatedMessage && (
        <div className="glass-card border-success/30 bg-success/5 p-4 flex items-center gap-3">
          <Sparkles className="h-5 w-5 text-success" />
          <p className="text-sm font-medium text-success">Saga đã được tạo và đang chạy (10 ticks) qua V3 pipeline.</p>
        </div>
      )}

      {/* Saga Header Card */}
      <div className="glass-card p-6 overflow-hidden relative">
        <div className="absolute top-0 right-0 p-4">
          <Badge variant="outline" className={cn(
            "font-mono text-[10px] uppercase tracking-widest",
            saga.status === 'running' ? 'text-success border-success/20 bg-success/5' :
              isComplete ? 'text-primary border-primary/20 bg-primary/5' :
                'text-warning border-warning/20 bg-warning/5'
          )}>
            {saga.status}
          </Badge>
        </div>

        <div className="flex items-start gap-4 mb-6">
          <div className="h-12 w-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary shadow-inner">
            <Activity className="h-6 w-6" />
          </div>
          <div>
            <h2 className="text-2xl font-bold tracking-tight text-foreground">{saga.name}</h2>
            <div className="flex items-center gap-3 mt-1">
              <span className="text-[10px] uppercase tracking-widest text-muted-foreground font-bold font-mono">
                {sagaId.slice(0, 8)}
              </span>
              {saga.current_universe_id && (
                <>
                  <span className="text-muted-foreground">•</span>
                  <span className="text-[10px] uppercase tracking-widest text-primary font-bold font-mono" title={saga.current_universe_id}>
                    Universe: {saga.current_universe_id.slice(0, 8)}
                  </span>
                </>
              )}
            </div>
          </div>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div className="p-3 rounded-lg bg-muted/30 border border-border/50">
            <span className="metric-label">Parallel Worlds</span>
            <p className="text-xl font-bold metric-value">{sagaWorlds.length}</p>
          </div>
          <div className="p-3 rounded-lg bg-muted/30 border border-border/50">
            <span className="metric-label">Total Worlds</span>
            <p className="text-xl font-bold metric-value">{saga.world_count ?? sagaWorlds.length}</p>
          </div>
          <div className="p-3 rounded-lg bg-muted/30 border border-border/50">
            <span className="metric-label">Pipeline</span>
            <p className="text-xl font-bold metric-value text-primary">V3</p>
          </div>
        </div>
      </div>

      {/* Saga Worlds */}
      {sagaWorlds.length > 0 && (
        <div className="space-y-3">
          <div className="flex items-center justify-between px-2">
            <h3 className="text-xs font-bold uppercase tracking-widest text-muted-foreground/70 flex items-center gap-2">
              <Globe className="h-3.5 w-3.5" />
              Saga Worlds
            </h3>
            <Badge variant="secondary" className="text-[10px]">{sagaWorlds.length} WORLDS</Badge>
          </div>
          <div className="space-y-2">
            {sagaWorlds.map((sw) => (
              <SagaWorldRow key={sw.id} sw={sw} />
            ))}
          </div>
        </div>
      )}

      {/* Timeline Tree */}
      <SagaTreeView sagaId={sagaId} />

      {/* Advance Controls */}
      <div className="glass-panel p-4 rounded-2xl flex items-center gap-4">
        <div className="flex items-center gap-2">
          <FastForward className="h-4 w-4 text-primary" />
          <span className="text-xs font-bold uppercase tracking-wider">Advance</span>
        </div>
        <div className="flex items-center gap-2">
          <input
            type="number"
            min={1}
            max={100}
            value={tickCount}
            onChange={(e) => setTickCount(Math.max(1, parseInt(e.target.value) || 1))}
            className="h-9 w-20 rounded-md bg-muted/30 border border-border/50 px-3 text-sm font-mono text-center outline-none focus:ring-2 focus:ring-primary/30"
          />
          <span className="text-[10px] text-muted-foreground uppercase">ticks</span>
        </div>
        <Button
          className="gap-2 font-bold ml-auto px-8 shadow-md"
          disabled={advance.isPending || isComplete}
          onClick={() => advance.mutate(tickCount)}
        >
          <FastForward className="h-4 w-4" />
          {advance.isPending ? "Advancing..." : `Advance ${tickCount} Ticks`}
        </Button>
      </div>
    </div>
  );
}
