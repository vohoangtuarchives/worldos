"use client";

import { Swords, Activity } from "lucide-react";

export default function HeroesHubPage() {
    return (
        <div className="p-4 md:p-8 space-y-6">
            <header className="flex flex-col md:flex-row shadow-glow justify-between items-start md:items-end pb-6 border-b border-red-500/20">
                <div>
                    <div className="flex items-center gap-3 mb-2">
                        <Swords className="w-8 h-8 text-red-400 shadow-[0_0_15px_theme(colors.red.400)]" />
                        <h1 className="text-3xl md:text-4xl font-bold tracking-tight bg-gradient-to-r from-red-400 to-red-600 bg-clip-text text-transparent uppercase">
                            Heroes Engine
                        </h1>
                    </div>
                    <p className="text-muted-foreground font-mono text-sm uppercase tracking-widest pl-1">
                        Extreme Outliers & Legacy Tracking
                    </p>
                </div>
            </header>

            <main className="min-h-[60vh] flex flex-col items-center justify-center p-8 border border-red-500/10 bg-black/20 rounded-2xl relative overflow-hidden">
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,theme(colors.red.500/0.05)_0%,transparent_50%)] pointer-events-none" />
                <Activity className="w-16 h-16 text-red-500/50 mb-4 animate-pulse" />
                <h2 className="text-xl font-mono tracking-widest text-foreground uppercase mb-2">Scanning Multiverse...</h2>
                <p className="text-sm text-muted-foreground max-w-sm text-center">
                    No active anomaly detected. The Heroes Engine requires an extreme socio-economic pressure event to spawn an outlier.
                </p>
            </main>
        </div>
    );
}
