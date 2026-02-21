"use client";

import { Network, Database } from "lucide-react";

export default function MarketplaceHubPage() {
    return (
        <div className="p-4 md:p-8 space-y-6">
            <header className="flex flex-col md:flex-row shadow-glow justify-between items-start md:items-end pb-6 border-b border-purple-500/20">
                <div>
                    <div className="flex items-center gap-3 mb-2">
                        <Network className="w-8 h-8 text-purple-400 shadow-[0_0_15px_theme(colors.purple.400)]" />
                        <h1 className="text-3xl md:text-4xl font-bold tracking-tight bg-gradient-to-r from-purple-400 to-purple-600 bg-clip-text text-transparent uppercase">
                            Multiverse Marketplace
                        </h1>
                    </div>
                    <p className="text-muted-foreground font-mono text-sm uppercase tracking-widest pl-1">
                        Inter-Civilization Asset Exchange
                    </p>
                </div>
            </header>

            <main className="min-h-[60vh] flex flex-col items-center justify-center p-8 border border-purple-500/10 bg-black/20 rounded-2xl relative overflow-hidden">
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,theme(colors.purple.500/0.05)_0%,transparent_50%)] pointer-events-none" />
                <Database className="w-16 h-16 text-purple-500/50 mb-4 animate-pulse drop-shadow-[0_0_10px_theme(colors.purple.500)]" />
                <h2 className="text-xl font-mono tracking-widest text-foreground uppercase mb-2">Exchange Network Offline</h2>
                <p className="text-sm text-muted-foreground max-w-sm text-center">
                    The Marketplace is awaiting initialization. Assets from collapsed timelines will be available for trade here soon.
                </p>
            </main>
        </div>
    );
}
