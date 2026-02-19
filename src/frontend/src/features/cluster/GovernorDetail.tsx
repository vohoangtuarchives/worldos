"use client";

import React from "react";
import {
    ShieldAlert,
    Settings2,
    TrendingUp,
    Activity,
    Zap,
    Pause,
    ChevronRight,
    Database,
    Search,
    ArrowUpRight,
    ArrowDownRight,
    Lock,
    RefreshCw,
    BrainCircuit,
    Check,
    X
} from "lucide-react";
import {
    useClusterGovernor,
    useClusterSystem,
    useClusterEmergencyFreeze
} from "./useClusterApi";
import {
    useStyleProposals,
    useApproveStyleProposal,
    useRejectStyleProposal,
    useWorlds
} from "@/features/writer/useWriterApi";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Slider } from "@/components/ui/slider";
import { cn } from "@/lib/utils";

export function GovernorDetail() {
    const { data: governor } = useClusterGovernor();
    const { data: system } = useClusterSystem();
    const emergencyFreeze = useClusterEmergencyFreeze();

    const { data: worldsData } = useWorlds();
    const activeWorldId = worldsData?.[0]?.id ?? null; // For demo, use first world

    const { data: proposalsRes, isLoading: proposalsLoading } = useStyleProposals(activeWorldId);
    const approveProposal = useApproveStyleProposal();
    const rejectProposal = useRejectStyleProposal();

    const [throttle, setThrottle] = React.useState(50);

    return (
        <div className="space-y-8 animate-in fade-in duration-1000">
            {/* Header / Stats */}
            <div className="grid gap-6 md:grid-cols-4">
                <div className="glass-card p-6 flex flex-col justify-between overflow-hidden relative group">
                    <div className="absolute -right-4 -top-4 text-primary opacity-5 group-hover:opacity-10 transition-opacity">
                        <Lock className="h-24 w-24" />
                    </div>
                    <p className="metric-label">Engine Status</p>
                    <div className="flex items-center gap-2 mt-1">
                        <div className="h-2 w-2 rounded-full bg-success animate-pulse" />
                        <h2 className="text-2xl font-black uppercase tracking-tight">PROTECTED</h2>
                    </div>
                </div>

                <div className="glass-card p-6">
                    <p className="metric-label">Pressure Index</p>
                    <div className="flex items-baseline gap-2 mt-1">
                        <h2 className="text-3xl font-black font-mono text-primary">{(governor?.pressureScore ?? 0.12).toFixed(2)}</h2>
                        <span className="text-xs text-muted-foreground uppercase font-bold">Aggregate</span>
                    </div>
                </div>

                <div className="glass-card p-6">
                    <p className="metric-label">Throttle Level</p>
                    <div className="flex items-baseline gap-2 mt-1">
                        <h2 className="text-3xl font-black uppercase tracking-tight text-slate-800">{governor?.throttleLevel ?? 'Normal'}</h2>
                        <span className="text-xs text-muted-foreground uppercase font-bold">Active Cap</span>
                    </div>
                </div>

                <div className="glass-card p-6">
                    <p className="metric-label">Burn Velocity</p>
                    <div className="flex items-baseline gap-2 mt-1">
                        <h2 className="text-3xl font-black font-mono text-warning">0.82 <span className="text-sm font-bold opacity-70">TKNs/s</span></h2>
                    </div>
                </div>
            </div>

            <div className="grid gap-8 lg:grid-cols-3">
                {/* Real-time Control Layer */}
                <div className="lg:col-span-2 space-y-6">
                    <div className="glass-panel p-8 border border-white/30 backdrop-blur-3xl relative overflow-hidden">
                        <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-blue-400" />

                        <div className="flex items-center justify-between mb-8">
                            <div className="flex items-center gap-3">
                                <Settings2 className="h-6 w-6 text-primary" />
                                <div>
                                    <h3 className="font-black text-sm uppercase tracking-widest text-slate-900 leading-none">Cluster Throttle Control</h3>
                                    <p className="text-[10px] text-muted-foreground mt-1 uppercase tracking-tighter">Adjust neural cycle frequency (Requires level 4 clearance)</p>
                                </div>
                            </div>
                            <Badge variant="outline" className="h-6 border-primary/30 text-primary font-bold px-3">LEVEL 4 CLEARANCE</Badge>
                        </div>

                        <div className="space-y-12 py-8 px-4">
                            <div className="relative">
                                <div className="flex justify-between items-end mb-6">
                                    <div className="space-y-1">
                                        <p className="text-4xl font-black font-mono text-slate-900 tracking-tighter">{(throttle / 50).toFixed(1)}x</p>
                                        <p className="text-[10px] text-muted-foreground uppercase font-black">Simulation Acceleration</p>
                                    </div>
                                    <div className="text-right space-y-1">
                                        <p className="text-xl font-black font-mono text-primary">{(throttle * 1.2).toFixed(0)} <span className="text-xs">Cycles/sec</span></p>
                                        <p className="text-[10px] text-muted-foreground uppercase font-black">Target Output</p>
                                    </div>
                                </div>
                                <Slider
                                    value={[throttle]}
                                    onValueChange={(val) => setThrottle(val[0])}
                                    max={100}
                                    step={1}
                                    className="cursor-pointer"
                                />
                                <div className="flex justify-between mt-4">
                                    <span className="text-[9px] font-black uppercase text-muted-foreground tracking-widest">Stasis (0x)</span>
                                    <span className="text-[9px] font-black uppercase text-primary tracking-widest font-mono">Nominal (1x)</span>
                                    <span className="text-[9px] font-black uppercase text-error tracking-widest">Overclock (2x)</span>
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <Button size="lg" className="h-16 font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-primary/20 transition-all hover:scale-[1.02]">
                                    <RefreshCw className="mr-3 h-5 w-5" />
                                    Sync Throttle State
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="lg"
                                    className="h-16 font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-error/20 transition-all hover:scale-[1.02]"
                                    onClick={() => emergencyFreeze.mutate()}
                                    disabled={emergencyFreeze.isPending}
                                >
                                    <Pause className="mr-3 h-5 w-5 fill-current" />
                                    Execute Master Pause
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div className="glass-card p-6">
                        <div className="flex items-center justify-between mb-6">
                            <h3 className="font-black text-xs uppercase tracking-widest flex items-center gap-2">
                                <TrendingUp className="h-4 w-4 text-success" />
                                Historical Pressure Scan
                            </h3>
                            <div className="flex gap-2">
                                <Badge variant="secondary" className="text-[9px] h-5 px-2">1H</Badge>
                                <Badge variant="outline" className="text-[9px] h-5 px-2">24H</Badge>
                            </div>
                        </div>
                        <div className="h-40 w-full flex items-end gap-1 px-2">
                            {Array.from({ length: 40 }).map((_, i) => (
                                <div
                                    key={i}
                                    className="flex-1 bg-primary/20 hover:bg-primary transition-colors rounded-t-[1px]"
                                    style={{ height: `${20 + Math.random() * 80}%` }}
                                />
                            ))}
                        </div>
                        <div className="flex justify-between mt-4 px-2">
                            <span className="text-[9px] font-bold text-muted-foreground uppercase">T-60MIN</span>
                            <span className="text-[9px] font-bold text-muted-foreground uppercase tracking-tighter">CURRENT_CYCLE</span>
                        </div>
                    </div>

                    {/* AI Advisor Proposals Section */}
                    <div className="glass-panel p-6 border-primary/10">
                        <div className="flex items-center justify-between mb-6">
                            <div className="flex items-center gap-2">
                                <BrainCircuit className="h-5 w-5 text-primary" />
                                <h3 className="font-black text-xs uppercase tracking-widest">AI Advisor Proposals</h3>
                            </div>
                            <Badge variant="secondary" className="text-[9px] font-bold">
                                {(proposalsRes?.data as any)?.data?.length ?? 0} PENDING
                            </Badge>
                        </div>

                        <div className="space-y-4">
                            {proposalsLoading ? (
                                <p className="text-xs text-muted-foreground animate-pulse text-center py-8 italic uppercase font-bold tracking-widest">Scanning Governance Layer...</p>
                            ) : ((proposalsRes?.data as any)?.data?.length ?? 0) === 0 ? (
                                <div className="p-8 border border-dashed border-border rounded-lg text-center">
                                    <p className="text-xs text-muted-foreground italic uppercase font-bold tracking-widest">No active style proposals from Advisor.</p>
                                </div>
                            ) : (
                                ((proposalsRes?.data as any)?.data ?? []).map((p: any) => (
                                    <div key={p.id} className="p-4 rounded-lg bg-white/40 border border-primary/10 space-y-3 relative group overflow-hidden">
                                        <div className="absolute top-0 left-0 w-1 h-full bg-primary/20 group-hover:bg-primary transition-colors" />
                                        <div className="flex justify-between items-start">
                                            <div>
                                                <p className="text-[10px] font-black text-primary uppercase mb-1">Physics Mutation Proposal</p>
                                                <h4 className="text-sm font-black text-slate-800 uppercase tracking-tight">Predicted GI Improvement: +{(p.predicted_improvement * 100).toFixed(1)}%</h4>
                                            </div>
                                            <Badge variant="outline" className="text-[9px] font-mono">{(p.created_at as string).split('T')[0]}</Badge>
                                        </div>
                                        <p className="text-xs text-slate-600 line-clamp-2 italic">"{p.reasoning}"</p>

                                        <div className="flex gap-2 mt-4 pt-4 border-t border-primary/5">
                                            <Button
                                                variant="default"
                                                size="sm"
                                                className="h-8 flex-1 font-bold text-[10px] uppercase gap-2 shadow-lg shadow-primary/10"
                                                onClick={() => approveProposal.mutate(p.id)}
                                                disabled={approveProposal.isPending}
                                            >
                                                <Check className="h-3 w-3" /> Execute Mutation
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                className="h-8 flex-1 font-bold text-[10px] uppercase gap-2"
                                                onClick={() => rejectProposal.mutate(p.id)}
                                                disabled={rejectProposal.isPending}
                                            >
                                                <X className="h-3 w-3" /> Dismiss
                                            </Button>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>

                {/* System Policy & Resource Column */}
                <div className="space-y-6">
                    <div className="glass-panel p-6 bg-slate-900 border-primary/20 text-white shadow-2xl">
                        <div className="flex items-center gap-3 mb-6">
                            <div className="h-10 w-10 rounded-lg bg-primary/20 flex items-center justify-center text-primary border border-primary/30">
                                <ShieldAlert className="h-6 w-6" />
                            </div>
                            <h3 className="font-black text-xs uppercase tracking-[0.2em]">Active Policy</h3>
                        </div>

                        <div className="space-y-4">
                            <div className="space-y-1">
                                <p className="text-[9px] text-muted-foreground font-black uppercase tracking-widest">Governing Strategy</p>
                                <p className="text-sm font-bold">Aggressive Entropy Mitigation</p>
                            </div>
                            <div className="space-y-1">
                                <p className="text-[9px] text-muted-foreground font-black uppercase tracking-widest">Auto-Scale Behavior</p>
                                <p className="text-sm font-bold">Disabled (Manual Oversight Only)</p>
                            </div>
                            <div className="p-4 rounded-lg bg-primary/5 border border-primary/10 mt-4">
                                <p className="text-[10px] text-primary/80 leading-relaxed font-medium italic">
                                    "System will priority-stabilize high-reputation worlds above grade S when global pressure exceeds 0.85"
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="glass-card p-6 space-y-6">
                        <div className="flex items-center gap-2">
                            <Database className="h-4 w-4 text-primary" />
                            <h3 className="font-black text-xs uppercase tracking-widest">Resource Allocation</h3>
                        </div>

                        <div className="space-y-4">
                            <div className="space-y-2">
                                <div className="flex justify-between text-[10px] font-black uppercase tracking-tighter text-muted-foreground">
                                    <span>Memory Buffer</span>
                                    <span className="text-slate-900">1.8GB / 4.0GB</span>
                                </div>
                                <div className="h-1.5 w-full bg-slate-200/50 rounded-full overflow-hidden">
                                    <div className="h-full bg-primary w-[45%]" />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <div className="flex justify-between text-[10px] font-black uppercase tracking-tighter text-muted-foreground">
                                    <span>Worker Threads</span>
                                    <span className="text-slate-900">12 / 16 active</span>
                                </div>
                                <div className="h-1.5 w-full bg-slate-200/50 rounded-full overflow-hidden">
                                    <div className="h-full bg-slate-800 w-[75%]" />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <div className="flex justify-between text-[10px] font-black uppercase tracking-tighter text-muted-foreground">
                                    <span>Signal Latency</span>
                                    <span className="text-success font-bold">12ms</span>
                                </div>
                                <div className="h-1.5 w-full bg-slate-200/50 rounded-full overflow-hidden">
                                    <div className="h-full bg-success w-[15%]" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="glass-card p-6 space-y-4">
                        <div className="flex items-center gap-2">
                            <Zap className="h-4 w-4 text-warning fill-current" />
                            <h3 className="font-black text-xs uppercase tracking-widest">Quick Toggles</h3>
                        </div>
                        <div className="space-y-2">
                            <div className="flex items-center justify-between p-3 rounded-md bg-muted/30 border border-border/50 hover:bg-muted/50 cursor-pointer transition-colors group">
                                <div className="flex items-center gap-3">
                                    <Activity className="h-4 w-4 text-muted-foreground group-hover:text-primary transition-colors" />
                                    <span className="text-[11px] font-bold uppercase tracking-tight">Verbose Telemetry</span>
                                </div>
                                <div className="h-4 w-8 bg-slate-200 rounded-full p-0.5">
                                    <div className="h-full w-3 bg-white rounded-full shadow-sm" />
                                </div>
                            </div>
                            <div className="flex items-center justify-between p-3 rounded-md bg-muted/30 border border-border/50 hover:bg-muted/50 cursor-pointer transition-colors group">
                                <div className="flex items-center gap-3">
                                    <RefreshCw className="h-4 w-4 text-muted-foreground group-hover:text-primary transition-colors" />
                                    <span className="text-[11px] font-bold uppercase tracking-tight">Auto-Correction</span>
                                </div>
                                <div className="h-4 w-8 bg-primary rounded-full p-0.5 flex justify-end">
                                    <div className="h-full w-3 bg-white rounded-full shadow-sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
