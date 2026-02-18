"use client";

import Link from "next/link";
import { useWorld, useWorldAction } from "./useWriterApi";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Activity,
  Orbit,
  Settings,
  Pause,
  Play,
  StepForward,
  Undo2,
  PlusCircle,
  Zap
} from "lucide-react";
import { cn } from "@/lib/utils";

export function WorldHubView({ worldId, refetchInterval }: { worldId: string; refetchInterval?: number }) {
  const { data: world, isLoading, error } = useWorld(worldId, { refetchInterval });
  const freeze = useWorldAction("freeze");
  const resume = useWorldAction("resume");
  const step = useWorldAction("step");
  const rollback = useWorldAction("rollback");
  const createInstance = useWorldAction("createInstance");

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

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
      {/* World Entity Header & Basic Stats */}
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
            <span className="metric-label">Avg Entropy</span>
            <p className="text-2xl font-bold metric-value">0.42</p>
          </div>
          <div className="p-4 rounded-lg bg-muted/30 border border-border/50">
            <span className="metric-label">Stability</span>
            <p className="text-2xl font-bold metric-value text-success">84%</p>
          </div>
          <div className="p-4 rounded-lg bg-muted/30 border border-border/50">
            <span className="metric-label">Generations</span>
            <p className="text-2xl font-bold metric-value">12</p>
          </div>
          <div className="p-4 rounded-lg bg-muted/30 border border-border/50">
            <span className="metric-label">Risk Index</span>
            <p className="text-2xl font-bold metric-value text-warning">0.08</p>
          </div>
        </div>
      </div>

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

      {/* Universes List (Runtime Instances) */}
      {world.runtime_instances && world.runtime_instances.length > 0 && (
        <div className="space-y-4">
          <div className="flex items-center justify-between px-2">
            <h3 className="font-bold text-sm uppercase tracking-wider flex items-center gap-2">
              <Orbit className="h-4 w-4 text-primary" />
              Runtime Universes
            </h3>
            <span className="text-[10px] font-bold text-muted-foreground uppercase">{world.runtime_instances.length} ACTIVE</span>
          </div>

          <div className="grid gap-4">
            {world.runtime_instances.map((u) => (
              <div key={u.id} className="glass-card group flex items-center justify-between p-4 hover:border-primary/30 transition-all">
                <div className="flex items-center gap-4">
                  <div className="h-10 w-10 rounded-lg bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
                    <Activity className="h-5 w-5" />
                  </div>
                  <div>
                    <p className="font-bold text-foreground text-sm">{u.name}</p>
                    <div className="flex items-center gap-2 mt-0.5 font-mono text-[9px] text-muted-foreground uppercase tracking-wider">
                      <span>Age: {u.age ?? 0}</span>
                      <span>•</span>
                      <span className={u.status === 'active' ? 'text-success' : ''}>{u.status}</span>
                    </div>
                  </div>
                </div>

                <div className="flex items-center gap-8 px-8">
                  <div className="flex flex-col items-center">
                    <span className="metric-label">Entropy</span>
                    <span className="text-xs font-mono font-bold">{u.entropy?.toFixed(3) ?? '0.000'}</span>
                  </div>
                  <div className="flex flex-col items-center">
                    <span className="metric-label">Stability</span>
                    <span className="text-xs font-mono font-bold text-success">{u.stability_index?.toFixed(3) ?? '0.000'}</span>
                  </div>
                </div>

                <Button asChild variant="ghost" size="sm" className="gap-2 font-bold group-hover:bg-primary/5">
                  <Link href={`/world/${worldId}?universe=${u.id}`}>
                    Inspect <ArrowUpRight className="h-3 w-3" />
                  </Link>
                </Button>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

function ArrowUpRight({ className }: { className?: string }) {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
      className={className}
    >
      <path d="M7 7h10v10" /><path d="M7 17 17 7" />
    </svg>
  );
}
