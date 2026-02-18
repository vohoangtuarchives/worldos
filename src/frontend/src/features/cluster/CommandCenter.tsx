"use client";

import React from "react";
import { useClusterSnapshot, useClusterGovernor, useClusterSystem, useClusterEmergencyFreeze } from "./useClusterApi";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Activity,
    Cpu,
    Coins,
    Zap,
    ShieldAlert,
    Pause,
    Play,
    ArrowUpRight,
    ArrowDownRight,
    Globe,
    Settings,
    Terminal,
    Layers,
    BarChart3
} from "lucide-react";
import Link from "next/link";
import { cn } from "@/lib/utils";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";

export function CommandCenter() {
    const { data: snapshot } = useClusterSnapshot();
    const { data: governor } = useClusterGovernor();
    const { data: system } = useClusterSystem();
    const emergencyFreeze = useClusterEmergencyFreeze();

    const { worlds = [], clusterStats = { total: 0, running: 0 } } = snapshot ?? {};

    return (
        <div className="space-y-8 animate-in fade-in duration-700">
            {/* Top Level Strategic Metrics */}
            <div className="grid gap-4 md:grid-cols-3">
                {/* Health Card */}
                <div className="glass-card p-6 border-l-4 border-l-success">
                    <div className="flex items-center justify-between mb-4">
                        <div className="h-10 w-10 rounded-lg bg-success/10 flex items-center justify-center text-success shadow-inner">
                            <Activity className="h-6 w-6" />
                        </div>
                        <Badge variant="outline" className="text-success border-success/20 bg-success/5 animate-pulse">SYSTEM NOMINAL</Badge>
                    </div>
                    <p className="metric-label">Operational Capacity</p>
                    <div className="flex items-baseline gap-2">
                        <h2 className="text-4xl font-black metric-value tracking-tighter">{clusterStats.running}</h2>
                        <span className="text-muted-foreground text-xs uppercase font-bold">/ {clusterStats.total} active nodes</span>
                    </div>
                </div>

                {/* Performance Card */}
                <div className="glass-card p-6 border-l-4 border-l-primary">
                    <div className="flex items-center justify-between mb-4">
                        <div className="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary shadow-inner">
                            <Cpu className="h-6 w-6" />
                        </div>
                        <Badge variant="outline" className="text-primary border-primary/20 bg-primary/5">LOAD {(system?.cpuPercent ?? 42).toFixed(1)}%</Badge>
                    </div>
                    <p className="metric-label">Neural Throughput</p>
                    <div className="flex items-baseline gap-2">
                        <h2 className="text-4xl font-black metric-value tracking-tighter">
                            {system?.cpuPercent ?? 42}<span className="text-xl">%</span>
                        </h2>
                        <span className="text-muted-foreground text-xs uppercase font-bold">Aggregate CPU</span>
                    </div>
                </div>

                {/* Cost Card */}
                <div className="glass-card p-6 border-l-4 border-l-warning">
                    <div className="flex items-center justify-between mb-4">
                        <div className="h-10 w-10 rounded-lg bg-warning/10 flex items-center justify-center text-warning shadow-inner">
                            <Coins className="h-6 w-6" />
                        </div>
                        <Badge variant="outline" className="text-warning border-warning/20 bg-warning/5">EFFICIENCY 98.4%</Badge>
                    </div>
                    <p className="metric-label">Resource Consumption</p>
                    <div className="flex items-baseline gap-2">
                        <h2 className="text-4xl font-black metric-value tracking-tighter">
                            ${governor?.costBurnRate ?? 0.85}<span className="text-xl">/hr</span>
                        </h2>
                        <span className="text-muted-foreground text-xs uppercase font-bold">Burn rate (est)</span>
                    </div>
                </div>
            </div>

            {/* Main Operational Grid */}
            <div className="grid gap-6 lg:grid-cols-4">
                {/* Governor & Navigation Sidebar */}
                <div className="lg:col-span-1 space-y-4">
                    <div className="glass-panel p-6 border border-white/20">
                        <div className="flex items-center gap-2 mb-6">
                            <ShieldAlert className="h-5 w-5 text-primary" />
                            <h3 className="font-black text-xs uppercase tracking-[0.2em]">Governor Engine</h3>
                        </div>

                        <div className="space-y-6">
                            <div className="space-y-2">
                                <div className="flex justify-between items-center px-1">
                                    <span className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Global Pressure</span>
                                    <span className="text-xs font-mono font-black text-primary">{(governor?.pressureScore ?? 0.12).toFixed(2)}</span>
                                </div>
                                <div className="h-3 w-full bg-slate-200/50 rounded-full p-0.5 shadow-inner">
                                    <div
                                        className="h-full bg-gradient-to-r from-primary to-blue-400 rounded-full transition-all duration-1000 relative overflow-hidden"
                                        style={{ width: `${Math.min((governor?.pressureScore ?? 0.12) * 100, 100)}%` }}
                                    >
                                        <div className="absolute inset-0 bg-white/20 animate-pulse" />
                                    </div>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-2">
                                <div className="p-3 rounded-lg bg-slate-900/5 border border-white/20 text-center">
                                    <p className="text-[9px] text-muted-foreground uppercase font-black tracking-tighter mb-1">Throttle</p>
                                    <p className="text-xs font-black uppercase text-slate-800">{governor?.throttleLevel ?? 'Normal'}</p>
                                </div>
                                <div className="p-3 rounded-lg bg-slate-900/5 border border-white/20 text-center">
                                    <p className="text-[9px] text-muted-foreground uppercase font-black tracking-tighter mb-1">Mode</p>
                                    <p className="text-xs font-black uppercase text-success">Active</p>
                                </div>
                            </div>

                            <div className="pt-2 flex flex-col gap-2">
                                <Button
                                    variant="outline"
                                    className="w-full justify-start gap-3 h-10 font-bold border-white/40 hover:bg-white/40"
                                    asChild
                                >
                                    <Link href="/cluster/governor">
                                        <Settings className="h-4 w-4 text-primary" />
                                        Governor Settings
                                    </Link>
                                </Button>
                                <Button
                                    variant="outline"
                                    className="w-full justify-start gap-3 h-10 font-bold border-white/40 hover:bg-white/40"
                                    asChild
                                >
                                    <Link href="/cluster/events">
                                        <Terminal className="h-4 w-4 text-primary" />
                                        System Events
                                    </Link>
                                </Button>
                                <Button
                                    variant="destructive"
                                    className="w-full gap-2 font-black shadow-lg shadow-error/20 uppercase text-[11px] tracking-widest"
                                    onClick={() => emergencyFreeze.mutate()}
                                    disabled={emergencyFreeze.isPending}
                                >
                                    <Pause className="h-4 w-4" />
                                    Emergency Freeze
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div className="glass-card-accent p-6 flex flex-col items-center text-center group">
                        <div className="h-12 w-12 rounded-full bg-primary/20 flex items-center justify-center text-primary mb-4 group-hover:scale-110 transition-transform">
                            <Zap className="h-6 w-6" />
                        </div>
                        <h4 className="font-black text-xs uppercase tracking-widest mb-2">Neural Optimization</h4>
                        <p className="text-[10px] text-muted-foreground leading-relaxed mb-4">The Governor is currently rebalancing compute cycles across high-entropy worlds.</p>
                        <Button variant="outline" size="sm" className="w-full text-[10px] font-black uppercase tracking-[0.2em] h-8">
                            Execute Rebalance
                        </Button>
                    </div>
                </div>

                {/* High Density World Matrix */}
                <div className="lg:col-span-3 flex flex-col gap-4">
                    <div className="glass-panel overflow-hidden border border-white/20">
                        <div className="px-6 py-4 border-b border-white/20 flex items-center justify-between bg-white/20 backdrop-blur-md">
                            <div className="flex items-center gap-3">
                                <Layers className="h-4 w-4 text-primary" />
                                <h3 className="font-black text-[11px] uppercase tracking-[0.3em]">World Matrix Sensor</h3>
                            </div>
                            <div className="flex items-center gap-4">
                                <div className="flex items-center gap-4 text-[9px] uppercase font-black text-muted-foreground/60 tracking-tighter">
                                    <div className="flex items-center gap-1.5"><div className="h-1.5 w-1.5 rounded-full bg-success" /> Running</div>
                                    <div className="flex items-center gap-1.5"><div className="h-1.5 w-1.5 rounded-full bg-warning" /> High Risk</div>
                                    <div className="flex items-center gap-1.5"><div className="h-1.5 w-1.5 rounded-full bg-destructive" /> Critical</div>
                                </div>
                                <div className="h-4 w-px bg-white/40" />
                                <Button variant="ghost" size="icon" className="h-8 w-8 text-primary"><BarChart3 className="h-4 w-4" /></Button>
                            </div>
                        </div>

                        <div className="p-6">
                            <div className="grid grid-cols-10 gap-2 sm:grid-cols-12 md:grid-cols-15 lg:grid-cols-20 xl:grid-cols-25">
                                <TooltipProvider delayDuration={0}>
                                    {worlds.map((world, idx) => {
                                        const status = world.status || 'unknown';
                                        const stability = world.stability ?? 0.8;
                                        const entropy = world.entropy ?? 0.2;
                                        const isHighRisk = stability < 0.4 || entropy > 0.7;

                                        return (
                                            <Tooltip key={world.id}>
                                                <TooltipTrigger asChild>
                                                    <Link
                                                        href={`/world/${world.id}`}
                                                        className={cn(
                                                            "aspect-square rounded-[2px] cursor-pointer transition-all duration-300 hover:scale-150 hover:z-10 relative group border border-white/5 shadow-sm",
                                                            status === 'running' && !isHighRisk && "bg-success/60 hover:bg-success shadow-success/10",
                                                            status === 'running' && isHighRisk && "bg-warning/70 hover:bg-warning shadow-warning/20 animate-pulse",
                                                            status !== 'running' && "bg-slate-300/40 opacity-50 gray-scale hover:opacity-100 hover:gray-0"
                                                        )}
                                                    >
                                                        {status === 'running' && (
                                                            <div className="absolute inset-0 bg-white/10 animate-pulse opacity-0 group-hover:opacity-100" />
                                                        )}
                                                    </Link>
                                                </TooltipTrigger>
                                                <TooltipContent className="bg-slate-900 border-primary/20 text-white p-3 shadow-2xl backdrop-blur-xl">
                                                    <div className="space-y-1.5">
                                                        <p className="text-xs font-black uppercase tracking-wider">{world.name}</p>
                                                        <div className="flex gap-3 mt-1">
                                                            <div className="space-y-0.5">
                                                                <p className="text-[8px] text-muted-foreground uppercase font-bold tracking-widest text-[10px]">Stability</p>
                                                                <p className={cn("text-xs font-mono font-black", stability < 0.5 ? "text-error" : "text-success")}>
                                                                    {(stability * 10).toFixed(1)}
                                                                </p>
                                                            </div>
                                                            <div className="space-y-0.5">
                                                                <p className="text-[8px] text-muted-foreground uppercase font-bold tracking-widest text-[10px]">Entropy</p>
                                                                <p className="text-xs font-mono font-black text-orange-400">
                                                                    {entropy.toFixed(2)}
                                                                </p>
                                                            </div>
                                                            <div className="space-y-0.5">
                                                                <p className="text-[8px] text-muted-foreground uppercase font-bold tracking-widest text-[10px]">Tick</p>
                                                                <p className="text-xs font-mono font-black text-blue-400">
                                                                    {world.current_tick}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </TooltipContent>
                                            </Tooltip>
                                        );
                                    })}
                                    {/* Placeholder for scaling to 100+ */}
                                    {Array.from({ length: Math.max(0, 100 - worlds.length) }).map((_, i) => (
                                        <div key={i} className="aspect-square rounded-[2px] bg-slate-200/20 border border-white/5" />
                                    ))}
                                </TooltipProvider>
                            </div>
                        </div>

                        <div className="px-6 py-3 border-t border-white/20 bg-muted/20 flex justify-between items-center">
                            <p className="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60">
                                Showing {worlds.length} synchronized entities + {Math.max(0, 100 - worlds.length)} reserved slots
                            </p>
                            <Link href="/cluster/grid" className="text-[9px] font-black uppercase tracking-widest text-primary hover:underline flex items-center gap-1">
                                Expanded Visualization <ArrowUpRight className="h-2.5 w-2.5" />
                            </Link>
                        </div>
                    </div>

                    <div className="glass-card overflow-hidden h-[300px] border border-white/20">
                        <div className="px-6 py-4 border-b border-white/20 flex items-center justify-between bg-white/20">
                            <h3 className="font-black text-[11px] uppercase tracking-[0.3em] flex items-center gap-2">
                                <Activity className="h-4 w-4 text-primary" />
                                Priority World Logs
                            </h3>
                            <Button variant="ghost" size="sm" className="text-[9px] font-black uppercase tracking-widest h-7 gap-1.5" asChild>
                                <Link href="/cluster/events">
                                    Full Stream <Terminal className="h-3 w-3" />
                                </Link>
                            </Button>
                        </div>
                        <div className="p-4 overflow-y-auto h-[calc(300px-56px)] bg-slate-900/5 backdrop-blur-inner space-y-2 font-mono text-[10px]">
                            {worlds.slice(0, 8).map((w, i) => (
                                <div key={i} className="flex gap-3 leading-tight animate-in slide-in-from-left-2 duration-300" style={{ animationDelay: `${i * 100}ms` }}>
                                    <span className="text-muted-foreground font-bold shrink-0">{new Date().toLocaleTimeString([], { hour12: false })}</span>
                                    <span className="text-primary font-black shrink-0">[{w.name.slice(0, 12)}]</span>
                                    <span className="text-slate-700">Ticked to {w.current_tick}. Entropy stabilized at {w.entropy?.toFixed(2) ?? '0.00'}...</span>
                                </div>
                            ))}
                            <div className="flex gap-3 leading-tight text-warning italic">
                                <span className="text-muted-foreground font-bold shrink-0">{new Date().toLocaleTimeString([], { hour12: false })}</span>
                                <span className="font-black shrink-0">[SYSTEM]</span>
                                <span>Governor rebalancing task scheduled in 45s.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
