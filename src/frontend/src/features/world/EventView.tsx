"use client";

import { useState } from "react";
import { useWorldEvents, useWorldEventsReplay } from "@/features/writer/useWriterApi";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Database,
  Search,
  RotateCcw,
  ChevronLeft,
  ChevronRight,
  Terminal,
  Activity
} from "lucide-react";
import { cn } from "@/lib/utils";

const PER_PAGE = 20;

export function EventView({ worldId }: { worldId: string }) {
  const [page, setPage] = useState(1);
  const [typeFilter, setTypeFilter] = useState("");
  const [replayTick, setReplayTick] = useState<number | "">("");

  const { data, isLoading, error } = useWorldEvents(worldId, {
    page,
    per_page: PER_PAGE,
    type: typeFilter || undefined,
  });
  const replay = useWorldEventsReplay(worldId);

  const events: any[] = data?.events ?? [];
  const meta: any = data?.meta;

  if (isLoading) return (
    <div className="flex items-center gap-2 text-muted-foreground p-6">
      <div className="h-4 w-4 animate-spin rounded-full border-2 border-muted border-t-primary" />
      <span>Siphoning Domain Events...</span>
    </div>
  );

  if (error) return (
    <div className="glass-card border-error/20 p-6 text-error">
      <h3 className="font-bold">Log Link Severed</h3>
      <p className="text-sm">Failed to retrieve event stream for World {worldId}.</p>
    </div>
  );

  return (
    <div className="space-y-6 animate-in fade-in duration-500">
      {/* Search & Filter Header */}
      <div className="glass-panel p-4 rounded-xl flex items-center justify-between gap-4">
        <div className="flex items-center gap-3 flex-1 max-w-sm relative">
          <Search className="absolute left-3 h-4 w-4 text-muted-foreground" />
          <input
            type="text"
            className="w-full bg-white/20 border-border/50 rounded-lg pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary/30"
            placeholder="Search Event Signature..."
            value={typeFilter}
            onChange={(e) => {
              setTypeFilter(e.target.value);
              setPage(1);
            }}
          />
        </div>
        <div className="flex items-center gap-2">
          <Badge variant="outline" className="text-[10px] font-mono border-primary/20 bg-primary/5 text-primary">
            {meta?.total ?? 0} TOTAL EVENTS
          </Badge>
          <div className="h-4 w-px bg-border/50 mx-2" />
          <div className="flex items-center gap-1">
            <div className="h-2 w-2 rounded-full bg-success animate-pulse" />
            <span className="text-[10px] font-bold text-success uppercase">Live Feed</span>
          </div>
        </div>
      </div>

      <div className="grid gap-6 lg:grid-cols-4">
        {/* Main Log Table */}
        <div className="lg:col-span-3">
          <div className="glass-card overflow-hidden">
            <div className="px-6 py-4 border-b border-border/50 flex items-center justify-between bg-white/40">
              <h3 className="font-bold text-sm uppercase tracking-wider flex items-center gap-2">
                <Terminal className="h-4 w-4 text-primary" />
                Domain Event Stream
              </h3>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="bg-muted/30 text-[10px] uppercase font-bold tracking-widest text-muted-foreground">
                  <tr>
                    <th className="px-6 py-3 font-semibold w-24">Tick</th>
                    <th className="px-6 py-3 font-semibold w-48">Signature</th>
                    <th className="px-6 py-3 font-semibold">Data Payload</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-border/30 font-mono">
                  {events.map((e: any) => (
                    <tr key={e.id} className="group hover:bg-primary/[0.02] transition-colors border-l-2 border-l-transparent hover:border-l-primary">
                      <td className="px-6 py-4 text-xs font-bold text-muted-foreground tabular-nums">
                        {e.tick}
                      </td>
                      <td className="px-6 py-4">
                        <Badge variant="secondary" className="text-[10px] bg-primary/5 text-primary border-primary/10">
                          {e.type}
                        </Badge>
                      </td>
                      <td className="px-6 py-4">
                        <div className="max-w-md truncate text-[10px] text-muted-foreground group-hover:text-foreground transition-colors" title={JSON.stringify(e.payload)}>
                          {JSON.stringify(e.payload ?? {})}
                        </div>
                      </td>
                    </tr>
                  ))}
                  {events.length === 0 && (
                    <tr>
                      <td colSpan={3} className="px-6 py-12 text-center text-muted-foreground opacity-50">
                        <Database className="h-12 w-12 mx-auto mb-2" />
                        <p className="text-xs uppercase font-bold tracking-widest">No matching signatures found</p>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            {/* Pagination */}
            {meta && meta.total > PER_PAGE && (
              <div className="px-6 py-4 border-t border-border/50 flex items-center justify-between bg-muted/10">
                <span className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">
                  Page {meta.current_page} of {Math.ceil(meta.total / meta.per_page)}
                </span>
                <div className="flex gap-2">
                  <Button
                    size="sm"
                    variant="ghost"
                    className="h-8 gap-1 font-bold text-[10px] uppercase tracking-widest"
                    disabled={page <= 1}
                    onClick={() => setPage((p) => p - 1)}
                  >
                    <ChevronLeft className="h-3 w-3" /> Prev
                  </Button>
                  <Button
                    size="sm"
                    variant="ghost"
                    className="h-8 gap-1 font-bold text-[10px] uppercase tracking-widest"
                    disabled={page >= Math.ceil(meta.total / meta.per_page)}
                    onClick={() => setPage((p) => p + 1)}
                  >
                    Next <ChevronRight className="h-3 w-3" />
                  </Button>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Replay Ops Panel */}
        <div className="lg:col-span-1 space-y-4">
          <div className="glass-panel p-6 rounded-xl">
            <div className="flex items-center gap-2 mb-6">
              <RotateCcw className="h-4 w-4 text-primary" />
              <h3 className="font-bold text-sm uppercase tracking-wider">Replay Operations</h3>
            </div>

            <div className="space-y-4">
              <div>
                <label className="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2 block">
                  Target Temporal Tick
                </label>
                <input
                  type="number"
                  className="w-full bg-white/20 border border-border/50 rounded-lg px-4 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-primary/30"
                  placeholder="e.g. 1024"
                  value={replayTick === "" ? "" : replayTick}
                  onChange={(e) => setReplayTick(e.target.value === "" ? "" : Number(e.target.value))}
                />
              </div>

              <Button
                className="w-full gap-2 font-bold shadow-lg h-10"
                disabled={replay.isPending || replayTick === ""}
                onClick={() => typeof replayTick === "number" && replay.mutate(replayTick)}
              >
                {replay.isPending ? <Activity className="h-4 w-4 animate-spin" /> : <RotateCcw className="h-4 w-4" />}
                REPLAY FROM TICK
              </Button>

              <p className="text-[10px] text-muted-foreground leading-relaxed italic text-center px-2">
                System will attempt to roll back the world state to the specified temporal signature and re-execute.
              </p>
            </div>
          </div>

          <div className="glass-card-accent p-6 flex flex-col items-center text-center">
            <Terminal className="h-8 w-8 text-primary mb-3" />
            <h4 className="font-bold text-sm mb-1 uppercase tracking-tight">Event Persistence</h4>
            <p className="text-xs text-muted-foreground mb-4">Events are cryogenically stored for 7 days. Long-term archiving found in Phase D.</p>
            <Button variant="outline" size="sm" className="w-full text-[10px] font-bold uppercase tracking-widest">
              Export Logs
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}
