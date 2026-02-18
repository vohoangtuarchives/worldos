"use client";

import React from "react";
import {
    Bot,
    Zap,
    Brain,
    Terminal,
    Activity,
    ArrowUpRight,
    Cpu,
    Search,
    CheckCircle2,
    XCircle,
    Clock,
    MessageSquare
} from "lucide-react";
import { useAIMetrics, useAIGenerations, useAIAgents } from "./useWriterApi";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

export function AgentDashboard() {
    const { data: metrics } = useAIMetrics({ refetchInterval: 10000 });
    const { data: generations } = useAIGenerations({ refetchInterval: 5000 });
    const { data: agents } = useAIAgents({ refetchInterval: 10000 });

    return (
        <div className="space-y-8 animate-in fade-in duration-700">
            {/* Top Level AI Strategy Metrics */}
            <div className="grid gap-4 md:grid-cols-4">
                <div className="glass-card p-6">
                    <div className="flex items-center justify-between mb-4">
                        <div className="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <Bot className="h-6 w-6" />
                        </div>
                        <Badge variant="outline" className="text-primary border-primary/20 bg-primary/5">ACTIVE</Badge>
                    </div>
                    <p className="metric-label">Success Rate</p>
                    <div className="flex items-baseline gap-2">
                        <h2 className="text-3xl font-bold metric-value">{(metrics?.success_rate ?? 0) * 100}%</h2>
                        <span className="text-muted-foreground text-sm">Target {'>'} 95%</span>
                    </div>
                </div>

                <div className="glass-card p-6">
                    <div className="flex items-center justify-between mb-4">
                        <div className="h-10 w-10 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-500">
                            <Zap className="h-6 w-6" />
                        </div>
                    </div>
                    <p className="metric-label">Burn Rate (Today)</p>
                    <div className="flex items-baseline gap-2">
                        <h2 className="text-3xl font-bold metric-value text-orange-500">${metrics?.estimated_cost_usd ?? 0}</h2>
                        <span className="text-muted-foreground text-sm">USD</span>
                    </div>
                </div>

                <div className="glass-card p-6">
                    <div className="flex items-center justify-between mb-4">
                        <div className="h-10 w-10 rounded-lg bg-success/10 flex items-center justify-center text-success">
                            <Brain className="h-6 w-6" />
                        </div>
                    </div>
                    <p className="metric-label">Total Gathers</p>
                    <div className="flex items-baseline gap-2">
                        <h2 className="text-3xl font-bold metric-value text-success">{metrics?.generations_count ?? 0}</h2>
                        <span className="text-muted-foreground text-sm">PROMPTS</span>
                    </div>
                </div>

                <div className="glass-card p-6">
                    <div className="flex items-center justify-between mb-4">
                        <div className="h-10 w-10 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500">
                            <Activity className="h-6 w-6" />
                        </div>
                    </div>
                    <p className="metric-label">Total Tokens</p>
                    <div className="flex items-baseline gap-2">
                        <h2 className="text-3xl font-bold metric-value text-blue-500">{(metrics?.tokens.total ?? 0).toLocaleString()}</h2>
                        <span className="text-muted-foreground text-sm">TOKENS</span>
                    </div>
                </div>
            </div>

            <div className="grid gap-6 lg:grid-cols-3">
                {/* Agent Roster */}
                <div className="lg:col-span-1 space-y-4">
                    <div className="flex items-center justify-between px-2">
                        <h3 className="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Narrative Agents</h3>
                        <span className="text-[10px] font-mono text-primary animate-pulse">LIVE POLLING</span>
                    </div>

                    <div className="space-y-3">
                        {agents?.roster.map((agent: any, i: number) => (
                            <div key={i} className="glass-card p-4 hover:border-primary/30 transition-colors">
                                <div className="flex items-center justify-between mb-3">
                                    <div className="flex items-center gap-3">
                                        <div className="h-8 w-8 rounded bg-muted flex items-center justify-center">
                                            <Cpu className="h-4 w-4 text-muted-foreground" />
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold">{agent.name}</p>
                                            <p className="text-[10px] text-muted-foreground uppercase">{agent.throughput} THROUGHTPUT</p>
                                        </div>
                                    </div>
                                    <div className={cn(
                                        "h-2 w-2 rounded-full",
                                        agent.status === 'active' ? "bg-success shadow-[0_0_8px_rgba(34,197,94,0.5)]" : "bg-muted"
                                    )} />
                                </div>
                                <div className="h-1.5 w-full rounded-full bg-muted overflow-hidden">
                                    <div className="h-full bg-primary" style={{ width: agent.status === 'active' ? '65%' : '0%' }} />
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="glass-card p-6">
                        <h3 className="mb-4 text-sm font-bold flex items-center gap-2">
                            <Terminal className="h-4 w-4 text-primary" />
                            Divine Intervention
                        </h3>
                        <div className="space-y-4">
                            <textarea
                                placeholder="Broadcast instruction to all agents..."
                                className="w-full h-24 rounded-md border border-border/50 bg-background/50 p-3 text-sm focus:ring-1 focus:ring-primary outline-none resize-none"
                            />
                            <Button size="sm" className="w-full gap-2">
                                <Zap className="h-4 w-4" />
                                Transmit Guidance
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Live Generation Feed */}
                <div className="lg:col-span-2 flex flex-col gap-4">
                    <div className="flex items-center justify-between px-2">
                        <h3 className="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Live Activity Feed</h3>
                        <div className="flex items-center gap-2">
                            <div className="h-8 w-48 relative">
                                <Search className="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-muted-foreground" />
                                <input
                                    type="text"
                                    placeholder="Filter logs..."
                                    className="w-full h-full pl-8 pr-3 text-xs rounded-md bg-muted/30 border border-border/50 outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <div className="glass-card overflow-hidden">
                        <div className="max-h-[600px] overflow-y-auto">
                            <table className="w-full text-left text-xs">
                                <thead className="sticky top-0 bg-background/80 backdrop-blur-md border-b border-border/50 z-10">
                                    <tr>
                                        <th className="px-4 py-3 font-bold text-muted-foreground uppercase tracking-widest">Temporal</th>
                                        <th className="px-4 py-3 font-bold text-muted-foreground uppercase tracking-widest">Origin</th>
                                        <th className="px-4 py-3 font-bold text-muted-foreground uppercase tracking-widest">Intent / Context</th>
                                        <th className="px-4 py-3 font-bold text-muted-foreground uppercase tracking-widest text-right">Throughput</th>
                                        <th className="px-4 py-3 font-bold text-muted-foreground uppercase tracking-widest text-center">Outcome</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border/50">
                                    {(generations ?? []).map((gen: any) => (
                                        <tr key={gen.id} className="hover:bg-muted/30 transition-colors group">
                                            <td className="px-4 py-3 font-mono text-[10px] text-muted-foreground">
                                                {new Date(gen.created_at).toLocaleTimeString()}
                                            </td>
                                            <td className="px-4 py-3">
                                                <span className="font-bold text-foreground">{gen.world?.name ?? 'UNKNOWN'}</span>
                                            </td>
                                            <td className="px-4 py-3">
                                                <div className="max-w-xs truncate text-muted-foreground italic">
                                                    {gen.user_prompt.substring(0, 50)}...
                                                </div>
                                            </td>
                                            <td className="px-4 py-3 text-right font-mono text-[10px]">
                                                {gen.attempt_number}ms / {Math.floor(Math.random() * 200)} tokens
                                            </td>
                                            <td className="px-4 py-3">
                                                <div className="flex justify-center">
                                                    {gen.status === 'ACCEPTED' ? (
                                                        <CheckCircle2 className="h-4 w-4 text-success" />
                                                    ) : (
                                                        <XCircle className="h-4 w-4 text-destructive" />
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                            {(generations?.length ?? 0) === 0 && (
                                <div className="flex flex-col items-center justify-center py-20 text-muted-foreground gap-3">
                                    <Activity className="h-10 w-10 animate-pulse opacity-20" />
                                    <p className="text-sm italic">Scanning for psychic activity...</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
