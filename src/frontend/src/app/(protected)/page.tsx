"use client";

import { Globe, BookOpen, Fingerprint, Zap, ShieldAlert, ArrowRight } from "lucide-react";
import Link from "next/link";

export default function Dashboard() {
    return (
        <div className="space-y-8 animate-in fade-in duration-700">
            <header>
                <h1 className="text-3xl font-bold tracking-tight text-foreground font-sans">
                    MISSION <span className="gradient-text">CONTROL</span>
                </h1>
                <p className="text-muted-foreground mt-2 font-mono text-sm max-w-2xl">
                    Overview of Universe Simulation Matrix. All active domains are responding nominally. Entropy levels are within acceptable thresholds.
                </p>
            </header>

            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                {/* Metric Card 1 */}
                <div className="glass-card glow-cyan p-6 space-y-4 relative overflow-hidden group">
                    <div className="absolute top-0 right-0 w-32 h-32 bg-domain-world/10 rounded-full blur-2xl -mr-10 -mt-10 group-hover:bg-domain-world/20 transition-all duration-500"></div>
                    <div className="flex items-center justify-between">
                        <h3 className="text-sm font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                            <Globe className="w-4 h-4 text-domain-world" /> Worlds
                        </h3>
                        <span className="w-2 h-2 rounded-full bg-domain-world animate-pulse"></span>
                    </div>
                    <div className="space-y-1">
                        <div className="text-4xl font-black font-mono tracking-tighter text-foreground">1,024</div>
                        <div className="text-sm text-domain-world font-mono">+12 simulated today</div>
                    </div>
                </div>

                {/* Metric Card 2 */}
                <div className="glass-card glow-amber p-6 space-y-4 relative overflow-hidden group">
                    <div className="absolute top-0 right-0 w-32 h-32 bg-domain-saga/10 rounded-full blur-2xl -mr-10 -mt-10 group-hover:bg-domain-saga/20 transition-all duration-500"></div>
                    <div className="flex items-center justify-between">
                        <h3 className="text-sm font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                            <BookOpen className="w-4 h-4 text-domain-saga" /> Sagas
                        </h3>
                        <span className="w-2 h-2 rounded-full bg-domain-saga animate-pulse"></span>
                    </div>
                    <div className="space-y-1">
                        <div className="text-4xl font-black font-mono tracking-tighter text-foreground">256</div>
                        <div className="text-sm text-domain-saga font-mono">3 arcs pending canon</div>
                    </div>
                </div>

                {/* Metric Card 3 */}
                <div className="glass-card glow-purple p-6 space-y-4 relative overflow-hidden group">
                    <div className="absolute top-0 right-0 w-32 h-32 bg-domain-narrative/10 rounded-full blur-2xl -mr-10 -mt-10 group-hover:bg-domain-narrative/20 transition-all duration-500"></div>
                    <div className="flex items-center justify-between">
                        <h3 className="text-sm font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                            <Fingerprint className="w-4 h-4 text-domain-narrative" /> Entities
                        </h3>
                    </div>
                    <div className="space-y-1">
                        <div className="text-4xl font-black font-mono tracking-tighter text-foreground">14.2M</div>
                        <div className="text-sm font-mono text-muted-foreground">Across all dimensions</div>
                    </div>
                </div>

                {/* Metric Card 4 */}
                <div className="glass-card glow-rose p-6 space-y-4 relative overflow-hidden group">
                    <div className="absolute top-0 right-0 w-32 h-32 bg-domain-evolution/10 rounded-full blur-2xl -mr-10 -mt-10 group-hover:bg-domain-evolution/20 transition-all duration-500"></div>
                    <div className="flex items-center justify-between">
                        <h3 className="text-sm font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                            <Zap className="w-4 h-4 text-domain-evolution" /> System Load
                        </h3>
                        <ShieldAlert className="w-4 h-4 text-domain-evolution" />
                    </div>
                    <div className="space-y-1">
                        <div className="text-4xl font-black font-mono tracking-tighter text-foreground">92%</div>
                        <div className="text-sm text-domain-evolution font-mono">Warning: High Entropy</div>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 glass-panel rounded-xl p-6 h-[400px] border border-white/5 relative overflow-hidden">
                    <h3 className="text-sm font-bold uppercase tracking-wider text-muted-foreground mb-4">Event Stream Waterfall</h3>
                    <div className="flex items-center justify-center h-[300px] border border-dashed border-white/10 rounded-lg bg-black/50">
                        <span className="text-muted-foreground font-mono text-sm">[ Event Stream visualization will appear here ]</span>
                    </div>
                </div>

                <div className="glass-panel rounded-xl p-6 h-[400px] border border-white/5 flex flex-col">
                    <h3 className="text-sm font-bold uppercase tracking-wider text-muted-foreground mb-4">Quick Actions</h3>
                    <div className="space-y-4 flex-1">
                        <button className="w-full text-left p-4 rounded-lg bg-black/40 hover:bg-domain-world/10 border border-white/5 hover:border-domain-world/30 transition-all flex items-center justify-between group">
                            <div>
                                <div className="font-bold text-sm text-white group-hover:text-domain-world transition-colors">Ignite Universe</div>
                                <div className="text-xs text-muted-foreground font-mono mt-1">Initialize Big Bang Sequence</div>
                            </div>
                            <ArrowRight className="w-4 h-4 text-muted-foreground group-hover:text-domain-world" />
                        </button>

                        <button className="w-full text-left p-4 rounded-lg bg-black/40 hover:bg-domain-saga/10 border border-white/5 hover:border-domain-saga/30 transition-all flex items-center justify-between group">
                            <div>
                                <div className="font-bold text-sm text-white group-hover:text-domain-saga transition-colors">View Simulator</div>
                                <div className="text-xs text-muted-foreground font-mono mt-1">Enter God Mode</div>
                            </div>
                            <ArrowRight className="w-4 h-4 text-muted-foreground group-hover:text-domain-saga" />
                        </button>
                    </div>
                </div>
            </div>

        </div>
    );
}
