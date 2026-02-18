"use client";

import React from "react";
import Link from "next/link";
import {
    Globe,
    Plus,
    Activity,
    History,
    Database,
    Zap,
    ChevronRight,
    Sparkles,
    Search,
    BookOpen,
    Cpu
} from "lucide-react";
import { useSagas, useSagaStats } from "./useWriterApi";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { CreateSagaButton } from "./CreateSagaButton";
import { cn } from "@/lib/utils";

export function NarrativeOrchestrator() {
    const { data: sagas } = useSagas();
    const { data: stats } = useSagaStats();

    return (
        <div className="space-y-8 animate-in fade-in duration-700">
            {/* Narrative Metrics */}
            <div className="grid gap-4 md:grid-cols-4">
                <div className="glass-card p-6">
                    <p className="metric-label">Active Sagas</p>
                    <div className="flex items-baseline gap-2 mt-1">
                        <h2 className="text-3xl font-bold metric-value text-primary">{stats?.data?.active_sagas ?? 0}</h2>
                        <span className="text-muted-foreground text-xs uppercase tracking-tighter">Running</span>
                    </div>
                </div>
                <div className="glass-card p-6">
                    <p className="metric-label">Total Gathers</p>
                    <div className="flex items-baseline gap-2 mt-1">
                        <h2 className="text-3xl font-bold metric-value text-success">{stats?.data?.total_worlds ?? 0}</h2>
                        <span className="text-muted-foreground text-xs uppercase tracking-tighter">Worlds Spawned</span>
                    </div>
                </div>
                <div className="glass-card p-6">
                    <p className="metric-label">Neural Output</p>
                    <div className="flex items-baseline gap-2 mt-1">
                        <h2 className="text-3xl font-bold metric-value text-orange-500">{stats?.data?.total_chapters ?? 0}</h2>
                        <span className="text-muted-foreground text-xs uppercase tracking-tighter">Chapters</span>
                    </div>
                </div>
                <div className="glass-card p-6">
                    <p className="metric-label">Convergence</p>
                    <div className="flex items-baseline gap-2 mt-1">
                        <h2 className="text-3xl font-bold metric-value text-blue-500">{(sagas?.length ?? 0) > 0 ? '92%' : '0%'}</h2>
                        <span className="text-muted-foreground text-xs uppercase tracking-tighter">Stability</span>
                    </div>
                </div>
            </div>

            <div className="grid gap-6 lg:grid-cols-3">
                {/* Saga Archive & Active List */}
                <div className="lg:col-span-2 space-y-6">
                    <div className="flex items-center justify-between px-2">
                        <h3 className="text-xs font-bold uppercase tracking-widest text-muted-foreground/70 flex items-center gap-2">
                            <History className="h-3.5 w-3.5" />
                            Temporal Sagas
                        </h3>
                        <div className="flex items-center gap-3">
                            <div className="h-8 w-48 relative">
                                <Search className="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-muted-foreground" />
                                <input
                                    type="text"
                                    placeholder="Query timeline..."
                                    className="w-full h-full pl-8 pr-3 text-xs rounded-md bg-muted/30 border border-border/50 outline-none"
                                />
                            </div>
                            <CreateSagaButton />
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        {sagas?.map((saga) => (
                            <Link key={saga.id} href={`/writer/sagas/${saga.id}`} className="group">
                                <div className="glass-card p-5 hover:border-primary/40 transition-all duration-300 relative overflow-hidden h-full flex flex-col justify-between">
                                    <div className="flex justify-between items-start mb-4">
                                        <div className="space-y-1">
                                            <h4 className="font-bold text-slate-900 group-hover:text-primary transition-colors">{saga.name}</h4>
                                            <div className="flex items-center gap-2">
                                                <Badge variant="outline" className={cn(
                                                    "text-[10px] h-4 uppercase tracking-tighter",
                                                    saga.status === 'running' ? "bg-success/10 text-success border-success/20" : "bg-muted text-muted-foreground"
                                                )}>
                                                    {saga.status}
                                                </Badge>
                                                <span className="text-[10px] text-muted-foreground font-mono">
                                                    {saga.id.slice(0, 8)}
                                                </span>
                                            </div>
                                        </div>
                                        <ChevronRight className="h-4 w-4 text-muted-foreground group-hover:translate-x-1 transition-transform" />
                                    </div>

                                    <div className="space-y-4">
                                        <div className="grid grid-cols-2 gap-4">
                                            <div className="space-y-1">
                                                <p className="text-[10px] text-muted-foreground uppercase tracking-widest">Parallel Worlds</p>
                                                <p className="text-sm font-bold font-mono">{saga.saga_worlds_count ?? 0} / {saga.world_count}</p>
                                            </div>
                                            <div className="space-y-1">
                                                <p className="text-[10px] text-muted-foreground uppercase tracking-widest">Active Universe</p>
                                                <p className="text-sm font-bold font-mono text-primary">
                                                    {saga.current_universe_id ? saga.current_universe_id.slice(0, 6).toUpperCase() : 'NONE'}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="h-1.5 w-full rounded-full bg-muted overflow-hidden">
                                            <div className={cn(
                                                "h-full bg-primary",
                                                saga.status === 'running' && "animate-pulse"
                                            )} style={{ width: `${(saga.saga_worlds_count ?? 0) / saga.world_count * 100}%` }} />
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        ))}
                        {(!sagas || sagas.length === 0) && (
                            <div className="sm:col-span-2 border border-dashed border-border/50 rounded-xl py-20 flex flex-col items-center justify-center text-muted-foreground gap-3">
                                <Globe className="h-10 w-10 opacity-20" />
                                <p className="text-sm italic">No active Sagas found in the temporal web.</p>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href="/writer/genesis" className="gap-2">
                                        <Sparkles className="h-3.5 w-3.5" />
                                        Seed New World
                                    </Link>
                                </Button>
                            </div>
                        )}
                    </div>
                </div>

                {/* System & Resource Layer */}
                <div className="space-y-6">
                    <div className="flex items-center justify-between px-2">
                        <h3 className="text-xs font-bold uppercase tracking-widest text-muted-foreground/70 font-mono">Layer Status</h3>
                    </div>

                    <div className="space-y-4">
                        <div className="glass-card-accent p-6 flex flex-col gap-4">
                            <div className="flex items-center gap-3">
                                <div className="h-10 w-10 rounded-lg bg-primary/20 flex items-center justify-center text-primary shadow-sm">
                                    <Database className="h-5 w-5" />
                                </div>
                                <div>
                                    <h4 className="text-sm font-bold">Chronicle Ledger</h4>
                                    <p className="text-[10px] text-muted-foreground uppercase">Persistence active</p>
                                </div>
                            </div>
                            <div className="text-2xl font-bold font-mono">$1.42 <span className="text-xs font-normal text-muted-foreground">USD / EST</span></div>
                        </div>

                        <div className="glass-card p-6 space-y-4">
                            <h4 className="text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                <Activity className="h-3.5 w-3.5 text-success" />
                                System Throughput
                            </h4>
                            <div className="space-y-3">
                                <div className="flex justify-between items-center text-[10px] font-mono">
                                    <span className="text-muted-foreground">Neural Cycles</span>
                                    <span className="text-success font-bold">0.82 Hz</span>
                                </div>
                                <div className="h-1 w-full bg-muted rounded-full overflow-hidden">
                                    <div className="h-full w-[45%] bg-success" />
                                </div>
                                <div className="flex justify-between items-center text-[10px] font-mono">
                                    <span className="text-muted-foreground">Network Latency</span>
                                    <span className="text-primary font-bold">124ms</span>
                                </div>
                                <div className="h-1 w-full bg-muted rounded-full overflow-hidden">
                                    <div className="h-full w-[15%] bg-primary" />
                                </div>
                            </div>
                        </div>

                        <div className="glass-card p-6">
                            <h4 className="text-xs font-bold uppercase tracking-widest flex items-center gap-2 mb-4">
                                <BookOpen className="h-3.5 w-3.5 text-primary" />
                                Quick Actions
                            </h4>
                            <div className="grid grid-cols-1 gap-2">
                                <Button variant="outline" size="sm" className="w-full justify-start gap-2 h-9 text-xs" asChild>
                                    <Link href="/writer/genesis">
                                        <Sparkles className="h-3.5 w-3.5 text-primary" />
                                        Genesis Seeding
                                    </Link>
                                </Button>
                                <Button variant="outline" size="sm" className="w-full justify-start gap-2 h-9 text-xs" asChild>
                                    <Link href="/cluster/ai">
                                        <Cpu className="h-3.5 w-3.5 text-primary" />
                                        AI Oversight
                                    </Link>
                                </Button>
                                <Button variant="ghost" size="sm" className="w-full justify-start gap-2 h-9 text-xs text-muted-foreground hover:text-primary">
                                    <Zap className="h-3.5 w-3.5" />
                                    Apply Global Batch Step
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
