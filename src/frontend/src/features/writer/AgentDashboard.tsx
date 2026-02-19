"use client";

import React, { useMemo, useState } from "react";
import {
  Bot,
  Zap,
  Brain,
  Terminal,
  Activity,
  Cpu,
  Search,
  CheckCircle2,
  XCircle,
  MessageSquare,
} from "lucide-react";
import { useAIMetrics, useAIGenerations, useAIAgents, useAIIntervene } from "./useWriterApi";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

type AgentRosterItem = {
  name: string;
  status: string;
  requests?: number;
  avg_duration_ms?: number;
};

type GenerationItem = {
  id: string;
  created_at: string;
  status: string;
  user_prompt?: string;
  response_content?: string;
  attempt_number?: number;
  world?: { name?: string };
};

export function AgentDashboard() {
  const { data: metrics } = useAIMetrics({ refetchInterval: 10000 });
  const { data: generations } = useAIGenerations({ refetchInterval: 5000 });
  const { data: agents } = useAIAgents({ refetchInterval: 10000 });
  const intervene = useAIIntervene();

  const [filter, setFilter] = useState("");
  const [instruction, setInstruction] = useState("");

  const roster = (agents?.roster ?? []) as AgentRosterItem[];
  const generationList = useMemo(() => (generations ?? []) as GenerationItem[], [generations]);

  const filteredGenerations = useMemo(() => {
    if (!filter.trim()) return generationList;
    const q = filter.toLowerCase();
    return generationList.filter((item) => {
      const content = `${item.world?.name ?? ""} ${item.user_prompt ?? ""} ${item.status}`.toLowerCase();
      return content.includes(q);
    });
  }, [generationList, filter]);

  return (
    <div className="space-y-8 animate-in fade-in duration-700">
      <div className="grid gap-4 md:grid-cols-4">
        <div className="glass-card p-6">
          <div className="mb-4 flex items-center justify-between">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <Bot className="h-6 w-6" />
            </div>
            <Badge variant="outline" className="border-primary/20 bg-primary/5 text-primary">ACTIVE</Badge>
          </div>
          <p className="metric-label">Success Rate</p>
          <div className="flex items-baseline gap-2">
            <h2 className="metric-value text-3xl font-bold">{(metrics?.success_rate ?? 0) * 100}%</h2>
            <span className="text-sm text-muted-foreground">Target {'>'} 95%</span>
          </div>
        </div>

        <div className="glass-card p-6">
          <div className="mb-4 flex items-center justify-between">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/10 text-orange-500">
              <Zap className="h-6 w-6" />
            </div>
          </div>
          <p className="metric-label">Burn Rate (Today)</p>
          <div className="flex items-baseline gap-2">
            <h2 className="metric-value text-3xl font-bold text-orange-500">${metrics?.estimated_cost_usd ?? 0}</h2>
            <span className="text-sm text-muted-foreground">USD</span>
          </div>
        </div>

        <div className="glass-card p-6">
          <div className="mb-4 flex items-center justify-between">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-success/10 text-success">
              <Brain className="h-6 w-6" />
            </div>
          </div>
          <p className="metric-label">Total Gathers</p>
          <div className="flex items-baseline gap-2">
            <h2 className="metric-value text-3xl font-bold text-success">{metrics?.generations_count ?? 0}</h2>
            <span className="text-sm text-muted-foreground">PROMPTS</span>
          </div>
        </div>

        <div className="glass-card p-6">
          <div className="mb-4 flex items-center justify-between">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10 text-blue-500">
              <Activity className="h-6 w-6" />
            </div>
          </div>
          <p className="metric-label">Total Tokens</p>
          <div className="flex items-baseline gap-2">
            <h2 className="metric-value text-3xl font-bold text-blue-500">{(metrics?.tokens.total ?? 0).toLocaleString()}</h2>
            <span className="text-sm text-muted-foreground">TOKENS</span>
          </div>
        </div>
      </div>

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="space-y-4 lg:col-span-1">
          <div className="flex items-center justify-between px-2">
            <h3 className="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Narrative Agents</h3>
            <span className="text-[10px] font-mono text-primary animate-pulse">LIVE POLLING</span>
          </div>

          <div className="space-y-3">
            {roster.map((agent) => (
              <div key={agent.name} className="glass-card p-4 transition-colors hover:border-primary/30">
                <div className="mb-3 flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <div className="flex h-8 w-8 items-center justify-center rounded bg-muted">
                      <Cpu className="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div>
                      <p className="text-sm font-bold">{agent.name}</p>
                      <p className="text-[10px] uppercase text-muted-foreground">{agent.requests ?? 0} requests</p>
                    </div>
                  </div>
                  <div
                    className={cn(
                      "h-2 w-2 rounded-full",
                      agent.status === "active" ? "bg-success shadow-[0_0_8px_rgba(34,197,94,0.5)]" : "bg-muted"
                    )}
                  />
                </div>
                <p className="text-[10px] text-muted-foreground">Avg latency: {agent.avg_duration_ms ?? 0} ms</p>
              </div>
            ))}
          </div>

          <div className="glass-card p-6">
            <h3 className="mb-4 flex items-center gap-2 text-sm font-bold">
              <Terminal className="h-4 w-4 text-primary" />
              Divine Intervention
            </h3>
            <div className="space-y-4">
              <textarea
                value={instruction}
                onChange={(e) => setInstruction(e.target.value)}
                placeholder="Broadcast instruction to all agents..."
                className="h-24 w-full resize-none rounded-md border border-border/50 bg-background/50 p-3 text-sm outline-none focus:ring-1 focus:ring-primary"
              />
              <Button
                size="sm"
                className="w-full gap-2"
                disabled={intervene.isPending || instruction.trim().length < 3}
                onClick={() => intervene.mutate({ worldId: "global", instruction: instruction.trim() })}
              >
                <Zap className="h-4 w-4" />
                {intervene.isPending ? "Transmitting..." : "Transmit Guidance"}
              </Button>
              {intervene.isSuccess && (
                <p className="text-[10px] text-success">Intervention recorded with id {(intervene.data as { data?: { request_log_id?: string } })?.data?.request_log_id ?? "n/a"}.</p>
              )}
            </div>
          </div>
        </div>

        <div className="flex flex-col gap-4 lg:col-span-2">
          <div className="flex items-center justify-between px-2">
            <h3 className="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Live Activity Feed</h3>
            <div className="h-8 w-48 relative">
              <Search className="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-muted-foreground" />
              <input
                type="text"
                value={filter}
                onChange={(e) => setFilter(e.target.value)}
                placeholder="Filter logs..."
                className="h-full w-full rounded-md border border-border/50 bg-muted/30 pl-8 pr-3 text-xs outline-none"
              />
            </div>
          </div>

          <div className="glass-card overflow-hidden">
            <div className="max-h-[600px] overflow-y-auto">
              <table className="w-full text-left text-xs">
                <thead className="sticky top-0 z-10 border-b border-border/50 bg-background/80 backdrop-blur-md">
                  <tr>
                    <th className="px-4 py-3 font-bold uppercase tracking-widest text-muted-foreground">Temporal</th>
                    <th className="px-4 py-3 font-bold uppercase tracking-widest text-muted-foreground">Origin</th>
                    <th className="px-4 py-3 font-bold uppercase tracking-widest text-muted-foreground">Intent / Context</th>
                    <th className="px-4 py-3 text-right font-bold uppercase tracking-widest text-muted-foreground">Throughput</th>
                    <th className="px-4 py-3 text-center font-bold uppercase tracking-widest text-muted-foreground">Outcome</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-border/50">
                  {filteredGenerations.map((gen) => {
                    const approxTokens = Math.ceil((gen.response_content?.length ?? 0) / 4);
                    const attempts = gen.attempt_number ?? 0;
                    return (
                      <tr key={gen.id} className="group transition-colors hover:bg-muted/30">
                        <td className="px-4 py-3 font-mono text-[10px] text-muted-foreground">{new Date(gen.created_at).toLocaleTimeString()}</td>
                        <td className="px-4 py-3"><span className="font-bold text-foreground">{gen.world?.name ?? "UNKNOWN"}</span></td>
                        <td className="px-4 py-3">
                          <div className="max-w-xs truncate italic text-muted-foreground">{(gen.user_prompt ?? "").slice(0, 50)}...</div>
                        </td>
                        <td className="px-4 py-3 text-right font-mono text-[10px]">{attempts} attempts / ~{approxTokens} tokens</td>
                        <td className="px-4 py-3">
                          <div className="flex justify-center">
                            {gen.status === "ACCEPTED" ? <CheckCircle2 className="h-4 w-4 text-success" /> : <XCircle className="h-4 w-4 text-destructive" />}
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
              {filteredGenerations.length === 0 && (
                <div className="flex flex-col items-center justify-center gap-3 py-20 text-muted-foreground">
                  <MessageSquare className="h-10 w-10 animate-pulse opacity-20" />
                  <p className="text-sm italic">No AI activity matched your filter.</p>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
