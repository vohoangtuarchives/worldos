"use client";

import { useState } from "react";
import { useWorldEvents, useWorldEventsReplay } from "@/features/writer/useWriterApi";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

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

  const events = data?.events ?? [];
  const meta = data?.meta;

  if (isLoading) {
    return (
      <div className="flex items-center gap-2 p-6 text-muted-foreground">
        <div className="h-5 w-5 animate-spin rounded-full border-2 border-muted border-t-primary" />
        <span>Loading events…</span>
      </div>
    );
  }

  if (error) {
    return (
      <div className="p-6">
        <p className="text-destructive">Failed to load events.</p>
      </div>
    );
  }

  return (
    <div className="space-y-6 p-6">
      <Card>
        <CardHeader>
          <CardTitle>Events</CardTitle>
          <p className="text-sm text-muted-foreground">
            Domain event log. Filter by type; replay from tick (stub).
          </p>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex flex-wrap items-center gap-2">
            <label className="text-sm text-muted-foreground">Type</label>
            <input
              type="text"
              className="w-32 rounded border border-input bg-background px-2 py-1 text-sm"
              placeholder="Filter type"
              value={typeFilter}
              onChange={(e) => {
                setTypeFilter(e.target.value);
                setPage(1);
              }}
            />
          </div>
          {events.length === 0 ? (
            <p className="text-sm text-muted-foreground">No events.</p>
          ) : (
            <>
              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="border-b border-border">
                      <th className="px-2 py-1 text-left font-medium">Tick</th>
                      <th className="px-2 py-1 text-left font-medium">Type</th>
                      <th className="px-2 py-1 text-left font-medium">Payload</th>
                    </tr>
                  </thead>
                  <tbody>
                    {events.map((e) => (
                      <tr key={e.id} className="border-b border-border/50">
                        <td className="px-2 py-1 font-mono tabular-nums">{e.tick}</td>
                        <td className="px-2 py-1">{e.type}</td>
                        <td className="max-w-xs truncate px-2 py-1 font-mono text-xs text-muted-foreground">
                          {JSON.stringify(e.payload ?? {})}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              {meta && meta.total > PER_PAGE && (
                <div className="flex items-center gap-2">
                  <Button
                    size="sm"
                    variant="outline"
                    disabled={page <= 1}
                    onClick={() => setPage((p) => p - 1)}
                  >
                    Previous
                  </Button>
                  <span className="text-sm text-muted-foreground">
                    Page {meta.current_page} of {Math.ceil(meta.total / meta.per_page)}
                  </span>
                  <Button
                    size="sm"
                    variant="outline"
                    disabled={page >= Math.ceil(meta.total / meta.per_page)}
                    onClick={() => setPage((p) => p + 1)}
                  >
                    Next
                  </Button>
                </div>
              )}
            </>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Replay</CardTitle>
          <p className="text-sm text-muted-foreground">
            Replay from tick (stub). Wire to domain when supported.
          </p>
        </CardHeader>
        <CardContent className="flex flex-wrap items-center gap-2">
          <input
            type="number"
            className="w-24 rounded border border-input bg-background px-2 py-1 text-sm"
            placeholder="From tick"
            value={replayTick === "" ? "" : replayTick}
            onChange={(e) => setReplayTick(e.target.value === "" ? "" : Number(e.target.value))}
          />
          <Button
            size="sm"
            variant="outline"
            disabled={replay.isPending || replayTick === ""}
            onClick={() => typeof replayTick === "number" && replay.mutate(replayTick)}
          >
            {replay.isPending ? "…" : "Replay from tick"}
          </Button>
        </CardContent>
      </Card>
    </div>
  );
}
