"use client";

import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { BookOpenText, Target, Beaker, Clock } from "lucide-react";

export default function SagasHubPage() {
    return (
        <div className="p-4 md:p-8 space-y-6">
            <header className="flex flex-col md:flex-row shadow-glow justify-between items-start md:items-end pb-6 border-b border-accent/20">
                <div>
                    <div className="flex items-center gap-3 mb-2">
                        <BookOpenText className="w-8 h-8 text-accent shadow-glow" />
                        <h1 className="text-3xl md:text-4xl font-bold tracking-tight bg-gradient-to-r from-accent to-orange-400 bg-clip-text text-transparent uppercase">
                            Sagas & Epics
                        </h1>
                    </div>
                    <p className="text-muted-foreground font-mono text-sm uppercase tracking-widest pl-1">
                        Historical Narratives and timelines
                    </p>
                </div>
            </header>

            <main className="min-h-[60vh] flex flex-col items-center justify-center p-8 border border-white/5 bg-black/20 rounded-2xl glass-card-accent">
                <Clock className="w-16 h-16 text-accent/50 mb-4 animate-pulse" />
                <h2 className="text-xl font-mono tracking-widest text-foreground uppercase mb-2">Chronicle Database Syncing...</h2>
                <p className="text-sm text-muted-foreground max-w-sm text-center">
                    The Sagas Hub is currently compiling multi-universe narrative events. Tactical data grids will be available shortly.
                </p>
            </main>
        </div>
    );
}
